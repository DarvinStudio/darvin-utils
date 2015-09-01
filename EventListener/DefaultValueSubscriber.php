<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Darvin\Utils\DefaultValue\DefaultValueException;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Default value event subscriber
 */
class DefaultValueSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist' => 'setDefaultValues',
            'preUpdate'  => 'setDefaultValues',
        );
    }

    /**
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $metadataFactory  Metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args Event arguments
     *
     * @throws \Darvin\Utils\DefaultValue\DefaultValueException
     */
    public function setDefaultValues(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->metadataFactory->getMetadata($args->getEntityManager()->getClassMetadata($entityClass));
        $defaultValuesMap = $meta['default_values'];
        $this->filterDefaultValuesMap($defaultValuesMap, $entity, $entityClass);

        if (empty($defaultValuesMap)) {
            return;
        }

        $sourcePropertyValues = $this->getSourcePropertyValues(
            array_unique(array_values($defaultValuesMap)),
            $entity,
            $entityClass
        );

        foreach ($defaultValuesMap as $targetProperty => $sourcePropertyPath) {
            if (empty($sourcePropertyValues[$sourcePropertyPath])) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($entity, $targetProperty)) {
                throw new DefaultValueException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $targetProperty));
            }

            $this->propertyAccessor->setValue($entity, $targetProperty, $sourcePropertyValues[$sourcePropertyPath]);
        }
    }

    /**
     * @param array  $defaultValuesMap Default values map to filter
     * @param object $entity           Entity
     * @param string $entityClass      Entity class
     *
     * @throws \Darvin\Utils\DefaultValue\DefaultValueException
     */
    private function filterDefaultValuesMap(array &$defaultValuesMap, $entity, $entityClass)
    {
        foreach ($defaultValuesMap as $targetProperty => $sourcePropertyPath) {
            if (!$this->propertyAccessor->isReadable($entity, $targetProperty)) {
                throw new DefaultValueException(sprintf('Property "%s::$%s" is not readable.', $entityClass, $targetProperty));
            }

            $value = $this->propertyAccessor->getValue($entity, $targetProperty);

            if (!empty($value)) {
                unset($defaultValuesMap[$targetProperty]);
            }
        }
    }

    /**
     * @param array  $sourcePropertyPaths Source property paths
     * @param object $entity              Entity
     * @param string $entityClass         Entity class
     *
     * @return array
     * @throws \Darvin\Utils\DefaultValue\DefaultValueException
     */
    private function getSourcePropertyValues(array $sourcePropertyPaths, $entity, $entityClass)
    {
        $sourcePropertyValues = array();

        foreach ($sourcePropertyPaths as $sourcePropertyPath) {
            if (!$this->propertyAccessor->isReadable($entity, $sourcePropertyPath)) {
                throw new DefaultValueException(
                    sprintf('Property "%s::$%s" is not readable.', $entityClass, $sourcePropertyPath)
                );
            }

            $sourcePropertyValues[$sourcePropertyPath] = $this->propertyAccessor->getValue($entity, $sourcePropertyPath);
        }

        return $sourcePropertyValues;
    }
}
