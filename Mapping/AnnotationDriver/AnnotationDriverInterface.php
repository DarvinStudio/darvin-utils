<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\AnnotationDriver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Annotation driver
 */
interface AnnotationDriverInterface
{
    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $doctrineMeta Doctrine metadata
     * @param array                                              $meta         Metadata
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta);
}
