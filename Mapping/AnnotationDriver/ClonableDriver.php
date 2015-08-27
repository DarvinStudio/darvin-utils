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

use Darvin\Utils\Mapping\Annotation\Clonable;

/**
 * Clonable annotation driver
 */
class ClonableDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(\ReflectionClass $reflectionClass, array &$meta)
    {
        if (null === $this->reader->getClassAnnotation($reflectionClass, Clonable::ANNOTATION)) {
            return;
        }

        $meta['clonable'] = array();
    }
}
