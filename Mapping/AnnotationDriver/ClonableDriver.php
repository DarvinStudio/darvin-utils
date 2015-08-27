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

use Darvin\Utils\Mapping\Annotation\Clonable\Clonable;
use Darvin\Utils\Mapping\Annotation\Clonable\Copy;
use Darvin\Utils\Mapping\Annotation\Clonable\Skip;

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
        /** @var \Darvin\Utils\Mapping\Annotation\Clonable\Clonable $clonableAnnotation */
        $clonableAnnotation = $this->reader->getClassAnnotation($reflectionClass, Clonable::ANNOTATION);

        if (empty($clonableAnnotation)) {
            return;
        }

        $meta['clonable'] = array(
            'properties' => $this->getPropertiesToCopy($reflectionClass, $clonableAnnotation->copyingPolicy),
        );
    }

    /**
     * @param \ReflectionClass $reflectionClass Reflection class
     * @param string           $copyingPolicy   Copying policy
     *
     * @return array
     */
    private function getPropertiesToCopy(\ReflectionClass $reflectionClass, $copyingPolicy)
    {
        $properties = array();

        switch ($copyingPolicy) {
            case Clonable::COPYING_POLICY_ALL:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null === $this->reader->getPropertyAnnotation($reflectionProperty, Skip::ANNOTATION)) {
                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
            case Clonable::COPYING_POLICY_NONE:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null !== $this->reader->getPropertyAnnotation($reflectionProperty, Copy::ANNOTATION)) {
                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
        }

        return $properties;
    }
}
