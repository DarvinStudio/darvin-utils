<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\CustomObject;

use Darvin\Utils\Mapping\Annotation\CustomObject;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Custom entity loader
 */
class CustomEntityLoader implements CustomObjectLoaderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var array
     */
    private $customObjectMeta;

    /**
     * @param \Doctrine\ORM\EntityManager                                 $em                      Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $extendedMetadataFactory Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor        Property accessor
     */
    public function __construct(
        EntityManager $em,
        MetadataFactoryInterface $extendedMetadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->em = $em;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->customObjectMeta = [];
    }

    /**
     * @param mixed    $entityOrEntities     Entity or array of entities
     * @param callable $queryBuilderCallback Callback to process query builder
     *
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    public function loadCustomObjects($entityOrEntities, callable $queryBuilderCallback = null)
    {
        if ($entityOrEntities instanceof Collection) {
            $entityOrEntities = $entityOrEntities->toArray();
        }

        $this->load(is_array($entityOrEntities) ? $entityOrEntities : [$entityOrEntities], $queryBuilderCallback);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return bool
     */
    public function customObjectsLoadable($entityClass)
    {
        return $this->hasCustomObjectMeta($entityClass);
    }

    /**
     * @param array    $entities             Entities
     * @param callable $queryBuilderCallback Callback to process query builder
     *
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function load(array $entities, callable $queryBuilderCallback = null)
    {
        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);

            if (!$this->customObjectsLoadable($entityClass)) {
                $message = sprintf(
                    'Class "%s" must be annotated with "%s" annotation in order to load custom objects.',
                    $entityClass,
                    CustomObject::class
                );

                throw new CustomObjectException($message);
            }
        }

        $customEntitiesMap = $this->buildCustomEntitiesMap($entities);

        $queriesMap = $this->buildQueriesMap($customEntitiesMap);

        $customEntities = $this->fetchCustomEntities($queriesMap, $queryBuilderCallback);

        $this->setCustomEntities($entities, $customEntities, $customEntitiesMap);
    }

    /**
     * @param array $entities          Entities
     * @param array $customEntities    Custom entities
     * @param array $customEntitiesMap Custom entities map
     */
    private function setCustomEntities(array $entities, array $customEntities, array $customEntitiesMap)
    {
        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);
            $objectHash = spl_object_hash($entity);

            if (!isset($customEntitiesMap[$entityClass][$objectHash])) {
                continue;
            }
            foreach ($customEntitiesMap[$entityClass][$objectHash] as $targetProperty => $targetPropertyMap) {
                $customEntityClass = $targetPropertyMap['class'];
                $initProperty = $targetPropertyMap['initProperty'];
                $initPropertyValue = $targetPropertyMap['initPropertyValue'];

                if (!isset($customEntities[$customEntityClass][$initProperty][$initPropertyValue])) {
                    continue;
                }

                $this->setPropertyValue(
                    $entity,
                    $targetProperty,
                    $customEntities[$customEntityClass][$initProperty][$initPropertyValue]
                );
            }
        }
    }

    /**
     * @param array    $queriesMap           Queries map
     * @param callable $queryBuilderCallback Callback to process query builder
     *
     * @return array
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function fetchCustomEntities(array $queriesMap, callable $queryBuilderCallback = null)
    {
        foreach ($queriesMap as $customEntityClass => &$initProperties) {
            $customEntityDoctrineMeta = $this->extendedMetadataFactory->getDoctrineMetadata($customEntityClass);

            foreach ($initProperties as $initProperty => &$initPropertyValues) {
                if (!$customEntityDoctrineMeta->hasField($initProperty)
                    && !$customEntityDoctrineMeta->hasAssociation($initProperty)
                ) {
                    throw new CustomObjectException(
                        sprintf('Property "%s::$%s" is not mapped field or association.', $customEntityClass, $initProperty)
                    );
                }

                $customEntityRepository = $this->em->getRepository($customEntityClass);

                if (1 === count($initPropertyValues)) {
                    $initPropertyValue = reset($initPropertyValues);
                    $qb = $customEntityRepository->createQueryBuilder('o')
                        ->andWhere(sprintf('o.%s = :%1$s', $initProperty))
                        ->setParameter($initProperty, $initPropertyValue);

                    if (!empty($queryBuilderCallback)) {
                        $queryBuilderCallback($qb);
                    }
                    try {
                        $customEntity = $qb->getQuery()->getOneOrNullResult();
                    } catch (NonUniqueResultException $ex) {
                        throw new CustomObjectException(
                            sprintf(
                                'Unable to fetch custom entity "%s" by %s "%s": entity is not unique.',
                                $customEntityRepository->getClassName(),
                                $initProperty,
                                $initPropertyValue
                            )
                        );
                    }
                    if (!empty($customEntity)) {
                        $initPropertyValues[$initPropertyValue] = $customEntity;

                        continue;
                    }

                    unset($initPropertyValues[$initPropertyValue]);

                    continue;
                }

                $qb = $customEntityRepository->createQueryBuilder('o');
                $qb->where($qb->expr()->in('o.'.$initProperty, $initPropertyValues));

                if (!empty($queryBuilderCallback)) {
                    $queryBuilderCallback($qb);
                }

                $customEntities = $qb->getQuery()->getResult();

                /** @var callable $getPropertyValueCallback */
                $getPropertyValueCallback = [$this, 'getPropertyValue'];

                $customEntities = array_combine(array_map(function ($customEntity) use ($getPropertyValueCallback, $initProperty) {
                    return $getPropertyValueCallback($customEntity, $initProperty);
                }, $customEntities), $customEntities);

                foreach ($initPropertyValues as &$initPropertyValue) {
                    if (isset($customEntities[$initPropertyValue])) {
                        $initPropertyValue = $customEntities[$initPropertyValue];

                        continue;
                    }

                    unset($initPropertyValues[$initPropertyValue]);
                }

                unset($initPropertyValue);
            }

            unset($initPropertyValues);
        }

        unset($initProperties);

        return $queriesMap;
    }

    /**
     * @param array $customEntitiesMap Custom entities map
     *
     * @return array
     */
    private function buildQueriesMap(array $customEntitiesMap)
    {
        $map = [];

        foreach ($customEntitiesMap as $entitiesMap) {
            foreach ($entitiesMap as $entityMap) {
                foreach ($entityMap as $targetPropertyMap) {
                    $customEntityClass = $targetPropertyMap['class'];
                    $initProperty = $targetPropertyMap['initProperty'];
                    $initPropertyValue = $targetPropertyMap['initPropertyValue'];

                    if (!isset($map[$customEntityClass])) {
                        $map[$customEntityClass] = [];
                    }
                    if (!isset($map[$customEntityClass][$initProperty])) {
                        $map[$customEntityClass][$initProperty] = [];
                    }
                    if (!in_array($initPropertyValue, $map[$customEntityClass][$initProperty])) {
                        $map[$customEntityClass][$initProperty][$initPropertyValue] = $initPropertyValue;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @param array $entities Entities
     *
     * @return array
     */
    private function buildCustomEntitiesMap(array $entities)
    {
        $map = [];

        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);
            $objectHash = spl_object_hash($entity);

            foreach ($this->getCustomObjectMeta($entityClass) as $targetProperty => $params) {
                $initPropertyValue = $this->getPropertyValue($entity, $params['initPropertyValuePath']);

                if (empty($initPropertyValue)) {
                    continue;
                }
                if (!isset($map[$entityClass])) {
                    $map[$entityClass] = [];
                }
                if (!isset($map[$entityClass][$objectHash])) {
                    $map[$entityClass][$objectHash] = [];
                }

                $map[$entityClass][$objectHash][$targetProperty] = [
                    'class' => !empty($params['class'])
                        ? $params['class']
                        : $this->getPropertyValue($entity, $params['classPropertyPath'])
                    ,
                    'initProperty' => !empty($params['initProperty'])
                        ? $params['initProperty']
                        : $this->extendedMetadataFactory->getIdentifier($entityClass)
                    ,
                    'initPropertyValue' => $initPropertyValue,
                ];
            }
        }

        return $map;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return bool
     */
    private function hasCustomObjectMeta($entityClass)
    {
        $customObjectMeta = $this->getCustomObjectMeta($entityClass);

        return !empty($customObjectMeta);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     */
    private function getCustomObjectMeta($entityClass)
    {
        if (!array_key_exists($entityClass, $this->customObjectMeta)) {
            $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

            $this->customObjectMeta[$entityClass] = isset($meta['customObjects']) && !empty($meta['customObjects'])
                ? $meta['customObjects']
                : null;
        }

        return $this->customObjectMeta[$entityClass];
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     * @param mixed  $value        Value
     *
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function setPropertyValue($entity, $propertyPath, $value)
    {
        if (!$this->propertyAccessor->isWritable($entity, $propertyPath)) {
            throw new CustomObjectException(
                sprintf('Property "%s::$%s" is not writable.', ClassUtils::getClass($entity), $propertyPath)
            );
        }

        $this->propertyAccessor->setValue($entity, $propertyPath, $value);
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     *
     * @return mixed
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function getPropertyValue($entity, $propertyPath)
    {
        if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
            throw new CustomObjectException(
                sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $propertyPath)
            );
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }
}
