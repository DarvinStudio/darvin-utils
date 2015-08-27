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
    public function getMetadata($class)
    {
        if (isset($this->loadedMeta[$class])) {
            return $this->loadedMeta[$class];
        }

        $meta = array();

        $reflectionClass = $this->om->getClassMetadata($class)->getReflectionClass();

        foreach ($this->annotationDrivers as $annotationDriver) {
            $annotationDriver->readMetadata($reflectionClass, $meta);
        }

        $this->loadedMeta[$class] = $meta;

        return $meta;
    }
}
