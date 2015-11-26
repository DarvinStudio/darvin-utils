<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Sluggable;

use Darvin\Utils\Doctrine\ORM\EntityManagerProviderInterface;
use Darvin\Utils\Event\Events;
use Darvin\Utils\Event\SlugsUpdateEvent;
use Darvin\Utils\Mapping\Annotation\Slug;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Sluggable entity manager
 */
class SluggableEntityManager implements SluggableManagerInterface
{
    /**
     * @var \Darvin\Utils\Doctrine\ORM\EntityManagerProviderInterface
     */
    private $entityManagerProvider;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

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
     * @param \Darvin\Utils\Doctrine\ORM\EntityManagerProviderInterface   $entityManagerProvider Entity manager provider
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher       Event dispatcher
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $metadataFactory       Metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor      Property accessor
     */
    public function __construct(
        EntityManagerProviderInterface $entityManagerProvider,
        EventDispatcherInterface $eventDispatcher,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->entityManagerProvider = $entityManagerProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->checkedIfSluggableClasses = array();
        $this->slugHandlers = array();
        $this->slugsMetadata = array();
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
    public function generateSlugs($entity, $dispatchUpdateEvent = false)
    {
        $em = $this->entityManagerProvider->getEntityManager();

        $entityClass = ClassUtils::getClass($entity);

        $slugsChangeSet = array();

        foreach ($this->getSlugsMetadata($entityClass) as $slugProperty => $params) {
            $sourcePropertyPaths = $params['sourcePropertyPaths'];

            $oldSlug = $this->getPropertyValue($entity, $slugProperty);

            $slugParts = $this->getSlugParts($entity, $slugProperty, $sourcePropertyPaths);

            $newSlug = $originalNewSlug = implode($params['separator'], $slugParts);
            $slugSuffix = $slugParts[count($slugParts) - 1];

            if ($newSlug === $oldSlug) {
                continue;
            }

            foreach ($this->slugHandlers as $slugHandler) {
                $slugHandler->handle($newSlug, $slugSuffix, $em);
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
            $this->eventDispatcher->dispatch(Events::POST_SLUGS_UPDATE, new SlugsUpdateEvent($slugsChangeSet, $em));
        }

        return true;
    }

    /**
     * @param object $entity              Entity
     * @param string $slugProperty        Slug property
     * @param array  $sourcePropertyPaths Source property paths
     *
     * @return array
     * @throws \Darvin\Utils\Sluggable\SluggableException
     */
    private function getSlugParts($entity, $slugProperty, array $sourcePropertyPaths)
    {
        $slugParts = array();

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
            try {
                $doctrineMeta = $this->entityManagerProvider->getEntityManager()->getClassMetadata($entityClass);
            } catch (MappingException $ex) {
                throw new SluggableException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
            }

            $meta = $this->metadataFactory->getMetadata($doctrineMeta);

            if (!isset($meta['slugs']) || empty($meta['slugs'])) {
                $message = sprintf(
                    'At least one property of class "%s" must be annotated with "%s" annotation in order to generate slug.',
                    $entityClass,
                    Slug::ANNOTATION
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
}
