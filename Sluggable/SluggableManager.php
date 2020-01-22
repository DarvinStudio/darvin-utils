<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Sluggable manager
 */
class SluggableManager implements SluggableManagerInterface
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
    public function addSlugHandler(SlugHandlerInterface $slugHandler): void
    {
        $this->slugHandlers[] = $slugHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function isSluggable($entityOrClass): bool
    {
        $class = is_object($entityOrClass) ? ClassUtils::getClass($entityOrClass) : $entityOrClass;

        if (!isset($this->checkedIfSluggableClasses[$class])) {
            $this->checkedIfSluggableClasses[$class] = true;

            try {
                $this->getSlugsMetadata($class);
            } catch (\InvalidArgumentException $ex) {
                $this->checkedIfSluggableClasses[$class] = false;
            }
        }

        return $this->checkedIfSluggableClasses[$class];
    }

    /**
     * {@inheritDoc}
     */
    public function generateSlugs(object $entity, bool $triggerUpdateEvent = false, ?string $prefix = null): bool
    {
        $em             = $this->getEntityManager();
        $entityClass    = ClassUtils::getClass($entity);
        $slugsChangeSet = [];

        foreach ($this->getSlugsMetadata($entityClass) as $slugProperty => $params) {
            $sourcePropertyPaths = $params['sourcePropertyPaths'];

            $oldSlug = $this->propertyAccessor->getValue($entity, $slugProperty);

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
                $this->propertyAccessor->setValue($entity, $suffixPropertyPath, $slugSuffix);
            }

            $this->propertyAccessor->setValue($entity, $slugProperty, $newSlug);
        }
        if (empty($slugsChangeSet)) {
            return false;
        }
        if ($triggerUpdateEvent) {
            $this->eventDispatcher->dispatch(new SlugsUpdateEvent($slugsChangeSet, $em), SluggableEvents::SLUGS_UPDATED);
        }

        return true;
    }

    /**
     * @param object      $entity              Entity
     * @param string      $slugProperty        Slug property
     * @param array       $sourcePropertyPaths Source property paths
     * @param string|null $prefix              Slug prefix
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getSlugParts(object $entity, string $slugProperty, array $sourcePropertyPaths, ?string $prefix): array
    {
        $slugParts = [];
        $prefix    = (string)$prefix;

        if ('' !== $prefix) {
            $slugParts[] = $prefix;
        }
        foreach ($sourcePropertyPaths as $propertyPath) {
            if (false !== strpos($propertyPath, '.')) {
                $related = $this->propertyAccessor->getValue($entity, preg_replace('/\..*/', '', $propertyPath));

                if (null === $related) {
                    continue;
                }
            }

            $slugPart = (string)$this->propertyAccessor->getValue($entity, $propertyPath);

            if ('' !== $slugPart) {
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

            throw new \InvalidArgumentException($message);
        }

        return $slugParts;
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getSlugsMetadata(string $entityClass): array
    {
        if (!isset($this->slugsMetadata[$entityClass])) {
            $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

            if (!isset($meta['slugs']) || empty($meta['slugs'])) {
                $message = sprintf(
                    'At least one property of class "%s" must be annotated with "%s" annotation in order to generate slug.',
                    $entityClass,
                    Slug::class
                );

                throw new \InvalidArgumentException($message);
            }

            $this->slugsMetadata[$entityClass] = $meta['slugs'];
        }

        return $this->slugsMetadata[$entityClass];
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManagerProvider->getService();
    }
}
