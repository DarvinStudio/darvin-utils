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
interface MetadataFactoryInterface
{
    /**
     * @param \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface $driver Annotation driver
     */
    public function addAnnotationDriver(AnnotationDriverInterface $driver);

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $doctrineMeta Doctrine metadata
     *
     * @return array
     */
    public function getMetadata(ClassMetadata $doctrineMeta);
}
