<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping;

use Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface;
use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;

/**
 * Metadata factory
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $objectManagerProvider;

    /**
     * @var \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface[]
     */
    private $annotationDrivers;

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $om;

    /**
     * @var \Doctrine\Persistence\Mapping\ClassMetadata[]
     */
    private $doctrineMeta;

    /**
     * @var array[]
     */
    private $extendedMeta;

    /**
     * @var string[]
     */
    private $identifiers;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $objectManagerProvider Object manager provider
     */
    public function __construct(ServiceProviderInterface $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;

        $this->om = null;
        $this->annotationDrivers = $this->doctrineMeta = $this->extendedMeta = $this->identifiers = [];
    }

    /**
     * @param \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface $annotationDriver Annotation driver
     */
    public function addAnnotationDriver(AnnotationDriverInterface $annotationDriver): void
    {
        $this->annotationDrivers[] = $annotationDriver;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier($objectOrClass)
    {
        $doctrineMeta = $this->getDoctrineMetadata($objectOrClass);

        if (!isset($this->identifiers[$doctrineMeta->getName()])) {
            $identifiers = $doctrineMeta->getIdentifier();
            $count = count($identifiers);

            if ($count > 1) {
                $message = sprintf(
                    'Only objects with single identifier are supported, class "%s" has %d identifiers.',
                    $doctrineMeta->getName(),
                    $count
                );

                throw new MappingException($message);
            }

            $this->identifiers[$doctrineMeta->getName()] = reset($identifiers);
        }

        return $this->identifiers[$doctrineMeta->getName()];
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedMetadata($objectOrClass): array
    {
        $doctrineMeta = $this->getDoctrineMetadata($objectOrClass);

        if (!isset($this->extendedMeta[$doctrineMeta->getName()])) {
            $meta = [];
            $om = $this->getObjectManager();

            foreach (array_reverse(class_parents($doctrineMeta->getName())) as $parent) {
                try {
                    $this->getDoctrineMetadata($parent);
                } catch (MappingException $ex) {
                    continue;
                }

                $this->readExtendedMetadata($om->getClassMetadata($parent), $meta);
                $this->extendedMeta[$parent] = $meta;
            }

            $this->readExtendedMetadata($doctrineMeta, $meta);
            $this->extendedMeta[$doctrineMeta->getName()] = $meta;
        }

        return $this->extendedMeta[$doctrineMeta->getName()];
    }

    /**
     * {@inheritDoc}
     */
    public function getDoctrineMetadata($objectOrClass): ClassMetadata
    {
        $class = $this->getClass($objectOrClass);

        if (!isset($this->doctrineMeta[$class])) {
            try {
                $this->doctrineMeta[$class] = $this->getObjectManager()->getClassMetadata($class);
            } catch (\Doctrine\Persistence\Mapping\MappingException $ex) {
                throw new MappingException(
                    sprintf('Unable to get Doctrine metadata for class "%s": %s.', $class, lcfirst($ex->getMessage()))
                );
            }
        }

        return $this->doctrineMeta[$class];
    }

    /**
     * @param \Doctrine\Persistence\Mapping\ClassMetadata $doctrineMeta Doctrine metadata
     * @param array                                       $extendedMeta Extended metadata
     */
    private function readExtendedMetadata(ClassMetadata $doctrineMeta, array &$extendedMeta): void
    {
        foreach ($this->annotationDrivers as $annotationDriver) {
            $annotationDriver->readMetadata($doctrineMeta, $extendedMeta);
        }
    }

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return string
     */
    private function getClass($objectOrClass): string
    {
        return is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;
    }

    /**
     * @return \Doctrine\Persistence\ObjectManager
     */
    private function getObjectManager(): ObjectManager
    {
        if (null === $this->om) {
            $this->om = $this->objectManagerProvider->getService();
        }

        return $this->om;
    }
}
