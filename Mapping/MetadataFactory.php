<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping;

use Darvin\Utils\Doctrine\ObjectManagerProviderInterface;
use Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Metadata factory
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Darvin\Utils\Doctrine\ObjectManagerProviderInterface
     */
    private $objectManagerProvider;

    /**
     * @var \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface[]
     */
    private $annotationDrivers;

    /**
     * @var array
     */
    private $loadedMeta;

    /**
     * @param \Darvin\Utils\Doctrine\ObjectManagerProviderInterface $objectManagerProvider Object manager provider
     */
    public function __construct(ObjectManagerProviderInterface $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->annotationDrivers = array();
        $this->loadedMeta = array();
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
    public function getMetadata(ClassMetadata $doctrineMeta)
    {
        if (!isset($this->loadedMeta[$doctrineMeta->getName()])) {
            $meta = array();
            $om = $this->objectManagerProvider->getObjectManager();

            foreach (array_reverse(class_parents($doctrineMeta->getName())) as $parent) {
                if ($om->getMetadataFactory()->hasMetadataFor($parent)) {
                    $this->readMetadata($om->getClassMetadata($parent), $meta);
                    $this->loadedMeta[$parent] = $meta;
                }
            }

            $this->readMetadata($doctrineMeta, $meta);
            $this->loadedMeta[$doctrineMeta->getName()] = $meta;
        }

        return $this->loadedMeta[$doctrineMeta->getName()];
    }

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $doctrineMeta Doctrine metadata
     * @param array                                              $meta         Metadata
     */
    private function readMetadata(ClassMetadata $doctrineMeta, &$meta)
    {
        foreach ($this->annotationDrivers as $annotationDriver) {
            $annotationDriver->readMetadata($doctrineMeta, $meta);
        }
    }
}
