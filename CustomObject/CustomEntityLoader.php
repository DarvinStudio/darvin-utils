<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\CustomObject;

use Darvin\Utils\Mapping\Annotation\CustomObject;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var array
     */
    private $processedHashes;

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
        $this->customObjectMeta = $this->processedHashes = [];
    }

    /**
     * {@inheritDoc}
     */
    public function loadCustomObjects($entityOrEntities, ?callable $queryBuilderCallback = null): void
    {
        if ($entityOrEntities instanceof Collection) {
            $entityOrEntities = $entityOrEntities->toArray();
        }

        $this->load(is_array($entityOrEntities) ? $entityOrEntities : [$entityOrEntities], $queryBuilderCallback);
    }

    /**
     * {@inheritDoc}
     */
    public function customObjectsLoadable(string $entityClass): bool
    {
        return $this->hasCustomObjectMeta($entityClass);
    }

    /**
     * @param array         $entities             Entities
     * @param callable|null $queryBuilderCallback Callback to process query builder
     *
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function load(array $entities, ?callable $queryBuilderCallback): void
    {
        foreach ($entities as $key => $entity) {
            $hash = spl_object_hash($entity);

            if (null !== $queryBuilderCallback) {
                /** @var callable|object $queryBuilderCallback */
                $hash .= spl_object_hash($queryBuilderCallback);
            }
            if (isset($this->processedHashes[$hash])) {
                unset($entities[$key]);

                continue;
            }

            $this->processedHashes[$hash] = true;

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
    private function setCustomEntities(array $entities, array $customEntities, array $customEntitiesMap): void
    {
        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);
            $objectHash  = spl_object_hash($entity);

            if (!isset($customEntitiesMap[$entityClass][$objectHash])) {
                continue;
            }
            foreach ($customEntitiesMap[$entityClass][$objectHash] as $targetProperty => $targetPropertyMap) {
                $customEntityClass = $targetPropertyMap['class'];
                $initProperty = $targetPropertyMap['initProperty'];
                $initPropertyValue = $targetPropertyMap['initPropertyValue'];

                if (!isset($customEntities[$customEntityClass][$initProperty][json_encode($initPropertyValue)])) {
                    continue;
                }

                $value = $customEntities[$customEntityClass][$initProperty][json_encode($initPropertyValue)];

                if (is_array($value)) {
                    $value = new ArrayCollection($value);
                }

                $this->setPropertyValue($entity, $targetProperty, $value);
            }
        }
    }

    /**
     * @param array         $queriesMap           Queries map
     * @param callable|null $queryBuilderCallback Callback to process query builder
     *
     * @return array
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function fetchCustomEntities(array $queriesMap, ?callable $queryBuilderCallback): array
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

                    if (!is_array($initPropertyValue)) {
                        $qb = $customEntityRepository->createQueryBuilder('o')
                            ->andWhere(sprintf('o.%s = :%1$s', $initProperty))
                            ->setParameter($initProperty, $initPropertyValue);

                        if (null !== $queryBuilderCallback) {
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
                        if (null !== $customEntity) {
                            $initPropertyValues[json_encode($initPropertyValue)] = $customEntity;
                        } else {
                            unset($initPropertyValues[json_encode($initPropertyValue)]);
                        }

                        continue;
                    }
                }

                $flatValues = [];

                foreach ($initPropertyValues as $initPropertyValue) {
                    if (is_array($initPropertyValue)) {
                        $flatValues = array_merge($flatValues, $initPropertyValue);
                    } else {
                        $flatValues[] = $initPropertyValue;
                    }
                }

                $qb = $customEntityRepository->createQueryBuilder('o');
                $qb->where($qb->expr()->in('o.'.$initProperty, $flatValues));

                if (null !== $queryBuilderCallback) {
                    $queryBuilderCallback($qb);
                }

                $customEntities = $qb->getQuery()->getResult();

                /** @var callable $getPropertyValueCallback */
                $getPropertyValueCallback = [$this, 'getPropertyValue'];

                $customEntities = array_combine(array_map(function ($customEntity) use ($getPropertyValueCallback, $initProperty) {
                    return $getPropertyValueCallback($customEntity, $initProperty);
                }, $customEntities), $customEntities);

                foreach ($initPropertyValues as &$initPropertyValue) {
                    if (is_array($initPropertyValue)) {
                        foreach ($initPropertyValue as $key => $value) {
                            if (isset($customEntities[$value])) {
                                $initPropertyValue[$key] = $customEntities[$value];
                            } else {
                                unset($initPropertyValue[$key]);
                            }
                        }
                    } elseif (isset($customEntities[$initPropertyValue])) {
                        $initPropertyValue = $customEntities[$initPropertyValue];
                    } else {
                        unset($initPropertyValues[json_encode($initPropertyValue)]);
                    }
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
    private function buildQueriesMap(array $customEntitiesMap): array
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
                        $map[$customEntityClass][$initProperty][json_encode($initPropertyValue)] = $initPropertyValue;
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
    private function buildCustomEntitiesMap(array $entities): array
    {
        $map = [];

        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);
            $objectHash  = spl_object_hash($entity);

            foreach ($this->getCustomObjectMeta($entityClass) as $targetProperty => $params) {
                $initPropertyValue = $this->getPropertyValue($entity, $params['initPropertyValuePath']);

                if (is_array($initPropertyValue)) {
                    if (empty($initPropertyValue)) {
                        continue;
                    }
                } else {
                    $initPropertyValue = (string)$initPropertyValue;

                    if ('' === $initPropertyValue) {
                        continue;
                    }
                }
                if (!isset($map[$entityClass])) {
                    $map[$entityClass] = [];
                }
                if (!isset($map[$entityClass][$objectHash])) {
                    $map[$entityClass][$objectHash] = [];
                }

                $map[$entityClass][$objectHash][$targetProperty] = [
                    'initPropertyValue' => $initPropertyValue,
                    'initProperty'      => null !== $params['initProperty']
                        ? $params['initProperty']
                        : $this->extendedMetadataFactory->getIdentifier($entityClass)
                    ,
                    'class' => null !== $params['class']
                        ? $params['class']
                        : $this->getPropertyValue($entity, $params['classPropertyPath'])
                    ,
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
    private function hasCustomObjectMeta(string $entityClass): bool
    {
        return null !== $this->getCustomObjectMeta($entityClass);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array|null
     */
    private function getCustomObjectMeta(string $entityClass): ?array
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
    private function setPropertyValue($entity, string $propertyPath, $value): void
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
    private function getPropertyValue($entity, string $propertyPath)
    {
        if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
            throw new CustomObjectException(
                sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $propertyPath)
            );
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }
}
