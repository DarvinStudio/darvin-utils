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
use Darvin\Utils\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Clonable annotation driver
 */
class ClonableDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        $reflectionClass = $doctrineMeta->getReflectionClass();

        $clonableAnnotation = $this->reader->getClassAnnotation($reflectionClass, Clonable::ANNOTATION);

        if (!$clonableAnnotation instanceof Clonable) {
            return;
        }

        $idProperties = $doctrineMeta->getIdentifier();

        $meta['clonable'] = array(
            'properties' => $this->getPropertiesToCopy($reflectionClass, $clonableAnnotation->copyingPolicy, $idProperties),
        );
    }

    /**
     * @param \ReflectionClass $reflectionClass Reflection class
     * @param string           $copyingPolicy   Copying policy
     * @param array            $idProperties    Identifier properties
     *
     * @return array
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function getPropertiesToCopy(\ReflectionClass $reflectionClass, $copyingPolicy, array $idProperties)
    {
        $properties = array();

        switch ($copyingPolicy) {
            case Clonable::COPYING_POLICY_ALL:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null === $this->reader->getPropertyAnnotation($reflectionProperty, Skip::ANNOTATION)
                        && !in_array($reflectionProperty->getName(), $idProperties)
                    ) {
                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
            case Clonable::COPYING_POLICY_NONE:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null !== $this->reader->getPropertyAnnotation($reflectionProperty, Copy::ANNOTATION)) {
                        if (in_array($reflectionProperty->getName(), $idProperties)) {
                            $message = sprintf(
                                'Property "%s::$%s" is identifier and it\'s value must not be copied during cloning.',
                                $reflectionClass->getName(),
                                $reflectionProperty->getName()
                            );

                            throw new MappingException($message);
                        }

                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
        }

        return $properties;
    }
}
