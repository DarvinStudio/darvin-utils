<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Sluggable;

use Darvin\Utils\Event\SluggableEvents;
use Darvin\Utils\Event\SlugsUpdateEvent;
use Darvin\Utils\Mapping\Annotation\Slug;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Sluggable entity manager
 */
class SluggableEntityManager implements SluggableManagerInterface
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $entityManagerProvider;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

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
    private $checkedIfSluggableClasses;

    /**
     * @var \Darvin\Utils\Sluggable\SlugHandlerInterface[]
     */
    private $slugHandlers;

    /**
     * @var array
     */
    private $slugsMetadata;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface              $entityManagerProvider   Entity manager provider
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher         Event dispatcher
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $extendedMetadataFactory Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor        Property accessor
     */
    public function __construct(
        ServiceProviderInterface $entityManagerProvider,
        EventDispatcherInterface $eventDispatcher,
        MetadataFactoryInterface $extendedMetadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->entityManagerProvider = $entityManagerProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;

        $this->checkedIfSluggableClasses = $this->slugHandlers = $this->slugsMetadata = [];
    }

    /**
     * @param \Darvin\Utils\Sluggable\SlugHandlerInterface $slugHandler Slug handler
     */
    public function addSlugHandler(SlugHandlerInterface $slugHandler)
    {
        $this->slugHandlers[] = $slugHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function isSluggable($entityOrClass)
    {
        $class = is_object($entityOrClass) ? ClassUtils::getClass($entityOrClass) : $entityOrClass;

        if (!isset($this->checkedIfSluggableClasses[$class])) {
            $this->checkedIfSluggableClasses[$class] = true;

            try {
                $this->getSlugsMetadata($class);
            } catch (SluggableException $ex) {
                $this->checkedIfSluggableClasses[$class] = false;
            }
        }

        return $this->checkedIfSluggableClasses[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function generateSlugs($entity, $dispatchUpdateEvent = false, $prefix = null)
    {
        $em             = $this->getEntityManager();
        $entityClass    = ClassUtils::getClass($entity);
        $slugsChangeSet = [];

        foreach ($this->getSlugsMetadata($entityClass) as $slugProperty => $params) {
            $sourcePropertyPaths = $params['sourcePropertyPaths'];

            $oldSlug = $this->getPropertyValue($entity, $slugProperty);

            $slugParts = $this->getSlugParts($entity, $slugProperty, $sourcePropertyPaths, $prefix);

            $newSlug = $originalNewSlug = implode($params['separator'], $slugParts);
            $slugSuffix = $slugParts[count($slugParts) - 1];

            if ($newSlug === $oldSlug) {
                continue;
            }
            foreach ($this->slugHandlers as $slugHandler) {
                $slugHandler->handle($newSlug, $slugSuffix, $entity, $em);
            }

            $slugsChangeSet[$oldSlug] = $newSlug;

            if ($newSlug !== $originalNewSlug) {
                $suffixPropertyPath = $sourcePropertyPaths[count($sourcePropertyPaths) - 1];
                $this->setPropertyValue($entity, $suffixPropertyPath, $slugSuffix);
            }

            $this->setPropertyValue($entity, $slugProperty, $newSlug);
        }
        if (empty($slugsChangeSet)) {
            return false;
        }
        if ($dispatchUpdateEvent) {
            $this->eventDispatcher->dispatch(SluggableEvents::SLUGS_UPDATED, new SlugsUpdateEvent($slugsChangeSet, $em));
        }

        return true;
    }

    /**
     * @param object $entity              Entity
     * @param string $slugProperty        Slug property
     * @param array  $sourcePropertyPaths Source property paths
     * @param string $prefix              Slug prefix
     *
     * @return array
     * @throws \Darvin\Utils\Sluggable\SluggableException
     */
    private function getSlugParts($entity, $slugProperty, array $sourcePropertyPaths, $prefix = null)
    {
        $slugParts = [];

        if (!empty($prefix)) {
            $slugParts[] = $prefix;
        }
        foreach ($sourcePropertyPaths as $propertyPath) {
            if (false !== strpos($propertyPath, '.')) {
                $related = $this->getPropertyValue($entity, preg_replace('/\..*/', '', $propertyPath));

                if (empty($related)) {
                    continue;
                }
            }

            $slugPart = $this->getPropertyValue($entity, $propertyPath);

            if (!empty($slugPart)) {
                $slugParts[] = $slugPart;
            }
        }
        if (empty($slugParts)) {
            $message = sprintf(
                'Unable to generate slug "%s::$%s": unable to get any non empty slug parts using property paths "%s".',
                ClassUtils::getClass($entity),
                $slugProperty,
                implode('", "', $sourcePropertyPaths)
            );

            throw new SluggableException($message);
        }

        return $slugParts;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     * @throws \Darvin\Utils\Sluggable\SluggableException
     */
    private function getSlugsMetadata($entityClass)
    {
        if (!isset($this->slugsMetadata[$entityClass])) {
            $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

            if (!isset($meta['slugs']) || empty($meta['slugs'])) {
                $message = sprintf(
                    'At least one property of class "%s" must be annotated with "%s" annotation in order to generate slug.',
                    $entityClass,
                    Slug::class
                );

                throw new SluggableException($message);
            }

            $this->slugsMetadata[$entityClass] = $meta['slugs'];
        }

        return $this->slugsMetadata[$entityClass];
    }

    /**
     * @param object $entity       Entity
     * @param string $propertyPath Property path
     * @param mixed  $value        Value
     *
     * @throws \Darvin\Utils\Sluggable\SluggableException
     */
    private function setPropertyValue($entity, $propertyPath, $value)
    {
        if (!$this->propertyAccessor->isWritable($entity, $propertyPath)) {
            throw new SluggableException(
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
     * @throws \Darvin\Utils\Sluggable\SluggableException
     */
    private function getPropertyValue($entity, $propertyPath)
    {
        if (!$this->propertyAccessor->isReadable($entity, $propertyPath)) {
            throw new SluggableException(
                sprintf('Property "%s::$%s" is not readable.', ClassUtils::getClass($entity), $propertyPath)
            );
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->entityManagerProvider->getService();
    }
}
