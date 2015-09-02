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
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Default value event subscriber
 */
class DefaultValueSubscriber extends AbstractOnFlushListener implements EventSubscriber
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
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $metadataFactory  Metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        parent::onFlush($args);

        $setDefaultValuesCallback = array($this, 'setDefaultValues');

        $this
            ->onInsert($setDefaultValuesCallback)
            ->onUpdate($setDefaultValuesCallback);
    }

    /**
     * @param object $entity Entity
     *
     * @throws \Darvin\Utils\DefaultValue\DefaultValueException
     */
    protected function setDefaultValues($entity)
    {
        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->metadataFactory->getMetadata($this->em->getClassMetadata($entityClass));
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

        $recomputeChangeSet = false;

        foreach ($defaultValuesMap as $targetProperty => $sourcePropertyPath) {
            if (null === $sourcePropertyValues[$sourcePropertyPath]) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($entity, $targetProperty)) {
                throw new DefaultValueException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $targetProperty));
            }

            $this->propertyAccessor->setValue($entity, $targetProperty, $sourcePropertyValues[$sourcePropertyPath]);

            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $this->recomputeChangeSet($entity);
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

            if (null !== $value) {
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
