<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Set default values event subscriber
 */
class SetDefaultValuesSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $extendedMetadataFactory Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor        Property accessor
     */
    public function __construct(MetadataFactoryInterface $extendedMetadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach (array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()) as $entity) {
            $this->setDefaultValues($em, $entity);
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em     Entity manager
     * @param object                               $entity Entity
     *
     * @throws \RuntimeException
     */
    private function setDefaultValues(EntityManagerInterface $em, object $entity): void
    {
        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

        if (!isset($meta['defaultValues']) || empty($meta['defaultValues'])) {
            return;
        }

        $defaultValuesMap = $meta['defaultValues'];
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
                throw new \RuntimeException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $targetProperty));
            }

            $this->propertyAccessor->setValue($entity, $targetProperty, $sourcePropertyValues[$sourcePropertyPath]);

            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata($entityClass), $entity);
        }
    }

    /**
     * @param array  $defaultValuesMap Default values map to filter
     * @param object $entity           Entity
     * @param string $entityClass      Entity class
     *
     * @throws \RuntimeException
     */
    private function filterDefaultValuesMap(array &$defaultValuesMap, object $entity, string $entityClass): void
    {
        foreach ($defaultValuesMap as $targetProperty => $sourcePropertyPath) {
            if (!$this->propertyAccessor->isReadable($entity, $targetProperty)) {
                throw $this->createPropertyNotReadableException($entityClass, $targetProperty);
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
     * @throws \RuntimeException
     */
    private function getSourcePropertyValues(array $sourcePropertyPaths, object $entity, string $entityClass): array
    {
        $sourcePropertyValues = [];

        foreach ($sourcePropertyPaths as $sourcePropertyPath) {
            if (!$this->propertyAccessor->isReadable($entity, $sourcePropertyPath)) {
                throw $this->createPropertyNotReadableException($entityClass, $sourcePropertyPath);
            }

            $sourcePropertyValues[$sourcePropertyPath] = $this->propertyAccessor->getValue($entity, $sourcePropertyPath);
        }

        return $sourcePropertyValues;
    }

    /**
     * @param string $entityClass Entity class
     * @param string $property    Property
     *
     * @return \RuntimeException
     */
    private function createPropertyNotReadableException(string $entityClass, string $property): \RuntimeException
    {
        return new \RuntimeException(sprintf('Property "%s::$%s" is not readable.', $entityClass, $property));
    }
}
