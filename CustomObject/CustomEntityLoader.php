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
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
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
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadataInfo[]
     */
    private $doctrineMeta;

    /**
     * @var array
     */
    private $customObjectMeta;

    /**
     * @param \Doctrine\ORM\EntityManager                                 $em               Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $metadataFactory  Metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(
        EntityManager $em,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->em = $em;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->doctrineMeta = array();
        $this->customObjectMeta = array();
    }

    /**
     * {@inheritdoc}
     */
    public function loadForObject($object)
    {
        $this->loadForObjects(array($object));
    }

    /**
     * {@inheritdoc}
     */
    public function loadForObjects(array $objects)
    {
        if (empty($objects)) {
            return;
        }

        $customObjectsMap = $this->buildCustomObjectsMap($objects);

        $queriesData = $this->getQueriesData($customObjectsMap);

        $queryResults = $this->getQueryResults($queriesData);

        $this->setCustomEntities($objects, $queryResults, $customObjectsMap);
    }

    /**
     * @param array $entities         Entities
     * @param array $queryResults     Query results
     * @param array $customObjectsMap Custom objects map
     */
    private function setCustomEntities(array $entities, array $queryResults, array $customObjectsMap)
    {
        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);

            foreach ($customObjectsMap[$entityClass] as $targetProperty => $params) {
                $customObjectClass = $params['custom_object_class'];
                $initByPropertyName = $params['init_by_property_name'];
                $initByPropertyValue = $params['init_by_property_value'];
                $initByPropertyValuePath = $params['init_by_property_value_path'];

                if (!isset($queryResults[$customObjectClass][$initByPropertyName][$initByPropertyValuePath][$initByPropertyValue])
                    || empty($queryResults[$customObjectClass][$initByPropertyName][$initByPropertyValuePath][$initByPropertyValue])) {
                    continue;
                }

                $this->setPropertyValue(
                    $entity,
                    $targetProperty,
                    $queryResults[$customObjectClass][$initByPropertyName][$initByPropertyValuePath][$initByPropertyValue]
                );
            }
        }
    }

    /**
     * @param array $queriesData Queries data
     *
     * @return array
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function getQueryResults(array $queriesData)
    {
        foreach ($queriesData as $customEntityClass => &$initByProperties) {
            $customEntityDoctrineMeta = $this->getDoctrineMeta($customEntityClass);

            foreach ($initByProperties as $initByPropertyName => &$initByPropertyValues) {
                if (!$customEntityDoctrineMeta->hasField($initByPropertyName)
                    && !$customEntityDoctrineMeta->hasAssociation($initByPropertyName)
                ) {
                    throw new CustomObjectException(
                        sprintf('Property "%s::$%s" is not mapped field or association.', $customEntityClass, $initByPropertyName)
                    );
                }

                $customEntityRepository = $this->em->getRepository($customEntityClass);

                if (1 === count($initByPropertyValues)) {
                    $initByPropertyValue = reset($initByPropertyValues);

                    $customEntity = $customEntityRepository->findOneBy(array(
                        $initByPropertyName => $initByPropertyValue,
                    ));

                    if (empty($customEntity)) {
                        continue;
                    }

                    $targetProperties = array_keys($initByPropertyValues);

                    $initByPropertyValues[$targetProperties[0]] = array(
                        $initByPropertyValue => $customEntity,
                    );

                    continue;
                }

                $qb = $customEntityRepository->createQueryBuilder('o');
                $customEntities = $qb
                    ->andWhere($qb->expr()->in('o.'.$initByPropertyName, $initByPropertyValues))
                    ->getQuery()
                    ->getResult();

                if (empty($customEntities)) {
                    continue;
                }

                /** @var callable $getPropertyValueCallback */
                $getPropertyValueCallback = array($this, 'getPropertyValue');

                $customEntities = array_combine(array_map(function ($customEntity) use ($getPropertyValueCallback, $initByPropertyName) {
                    return $getPropertyValueCallback($customEntity, $initByPropertyName);
                }, $customEntities), $customEntities);

                foreach ($initByPropertyValues as &$initByPropertyValue) {
                    if (isset($customEntities[$initByPropertyValue])) {
                        $initByPropertyValue = array(
                            $initByPropertyValue => $customEntities[$initByPropertyValue],
                        );
                    }
                }

                unset($initByPropertyValue);
            }

            unset($initByPropertyValues);
        }

        unset($initByProperties);

        return $queriesData;
    }

    /**
     * @param array $customObjectsMap Custom objects map
     *
     * @return array
     */
    private function getQueriesData(array $customObjectsMap)
    {
        $data = array();

        foreach ($customObjectsMap as $entityParams) {
            foreach ($entityParams as $targetPropertyParams) {
                $customObjectClass = $targetPropertyParams['custom_object_class'];
                $initByPropertyName = $targetPropertyParams['init_by_property_name'];

                if (!isset($data[$customObjectClass])) {
                    $data[$customObjectClass] = array();
                }
                if (!isset($data[$customObjectClass][$initByPropertyName])) {
                    $data[$customObjectClass][$initByPropertyName] = array();
                }

                $data[$customObjectClass][$initByPropertyName][$targetPropertyParams['init_by_property_value_path']] =
                    $targetPropertyParams['init_by_property_value'];
            }
        }

        return $data;
    }

    /**
     * @param array $entities Entities
     *
     * @return array
     */
    private function buildCustomObjectsMap(array $entities)
    {
        $map = array();

        foreach ($entities as $entity) {
            $entityClass = ClassUtils::getClass($entity);

            foreach ($this->getCustomObjectMeta($entityClass) as $targetProperty => $params) {
                $initByPropertyValue = $this->getPropertyValue($entity, $params['initByPropertyValuePath']);

                if (empty($initByPropertyValue)) {
                    continue;
                }
                if (!isset($map[$entityClass])) {
                    $map[$entityClass] = array();
                }

                $map[$entityClass][$targetProperty] = array(
                    'custom_object_class' => !empty($params['class'])
                        ? $params['class']
                        : $this->getPropertyValue($entity, $params['classPropertyPath'])
                    ,
                    'init_by_property_name'       => $params['initByPropertyName'],
                    'init_by_property_value'      => $initByPropertyValue,
                    'init_by_property_value_path' => $params['initByPropertyValuePath'],
                );
            }
        }

        return $map;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function getCustomObjectMeta($entityClass)
    {
        if (!isset($this->customObjectMeta[$entityClass])) {
            $meta = $this->metadataFactory->getMetadata($this->getDoctrineMeta($entityClass));

            if (!isset($meta['customObjects']) || empty($meta['customObjects'])) {
                $message = sprintf(
                    'Class "%s" must be annotated with "%s" annotation in order to load custom objects.',
                    $entityClass,
                    CustomObject::ANNOTATION
                );

                throw new CustomObjectException($message);
            }

            $this->customObjectMeta[$entityClass] = $meta['customObjects'];
        }

        return $this->customObjectMeta[$entityClass];
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    private function getDoctrineMeta($entityClass)
    {
        if (!isset($this->doctrineMeta[$entityClass])) {
            try {
                $this->doctrineMeta[$entityClass] = $this->em->getClassMetadata($entityClass);
            } catch (MappingException $ex) {
                throw new CustomObjectException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
            }
        }

        return $this->doctrineMeta[$entityClass];
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
