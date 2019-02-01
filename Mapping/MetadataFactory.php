<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping;

use Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface;
use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
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
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata[]
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
     * {@inheritdoc}
     */
    public function addAnnotationDriver(AnnotationDriverInterface $annotationDriver)
    {
        $this->annotationDrivers[] = $annotationDriver;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getExtendedMetadata($objectOrClass)
    {
        $doctrineMeta = $this->getDoctrineMetadata($objectOrClass);

        if (!isset($this->extendedMeta[$doctrineMeta->getName()])) {
            $meta = [];
            $om = $this->getObjectManager();

            foreach (array_reverse(class_parents($doctrineMeta->getName())) as $parent) {
                if ($om->getMetadataFactory()->hasMetadataFor($parent)) {
                    $this->readExtendedMetadata($om->getClassMetadata($parent), $meta);
                    $this->extendedMeta[$parent] = $meta;
                }
            }

            $this->readExtendedMetadata($doctrineMeta, $meta);
            $this->extendedMeta[$doctrineMeta->getName()] = $meta;
        }

        return $this->extendedMeta[$doctrineMeta->getName()];
    }

    /**
     * {@inheritdoc}
     */
    public function getDoctrineMetadata($objectOrClass)
    {
        $class = $this->getClass($objectOrClass);

        if (!isset($this->doctrineMeta[$class])) {
            try {
                $this->doctrineMeta[$class] = $this->getObjectManager()->getClassMetadata($class);
            } catch (\Doctrine\Common\Persistence\Mapping\MappingException $ex) {
                throw new MappingException(
                    sprintf('Unable to get Doctrine metadata for class "%s": %s.', $class, lcfirst($ex->getMessage()))
                );
            }
        }

        return $this->doctrineMeta[$class];
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $doctrineMeta Doctrine metadata
     * @param mixed[]                                            $extendedMeta Extended metadata
     */
    private function readExtendedMetadata(ClassMetadata $doctrineMeta, &$extendedMeta)
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
    private function getClass($objectOrClass)
    {
        return is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getObjectManager()
    {
        if (empty($this->om)) {
            $this->om = $this->objectManagerProvider->getService();
        }

        return $this->om;
    }
}
