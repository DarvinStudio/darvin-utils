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

use Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;

/**
 * Metadata factory
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;

    /**
     * @var \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface[]
     */
    private $annotationDrivers;

    /**
     * @var array
     */
    private $loadedMeta;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $om Object manager
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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
    public function getMetadataByObject($object)
    {
        return $this->getMetadata(ClassUtils::getClass($object));
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($class)
    {
        if (isset($this->loadedMeta[$class])) {
            return $this->loadedMeta[$class];
        }

        $meta = array();

        $doctrineMeta = $this->om->getClassMetadata($class);

        foreach ($this->annotationDrivers as $annotationDriver) {
            $annotationDriver->readMetadata($doctrineMeta, $meta);
        }

        $this->loadedMeta[$class] = $meta;

        return $meta;
    }
}
