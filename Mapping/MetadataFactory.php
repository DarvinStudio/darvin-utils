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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Metadata factory
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface[]
     */
    private $annotationDrivers;

    /**
     * @var array
     */
    private $loadedMeta;

    /**
     * Constructor
     */
    public function __construct()
    {
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

            foreach ($this->annotationDrivers as $annotationDriver) {
                $annotationDriver->readMetadata($doctrineMeta, $meta);
            }

            $this->loadedMeta[$doctrineMeta->getName()] = $meta;
        }

        return $this->loadedMeta[$doctrineMeta->getName()];
    }
}
