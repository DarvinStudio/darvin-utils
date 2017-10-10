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

use Darvin\Utils\Mapping\Annotation\CustomObject;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Custom object annotation driver
 */
class CustomObjectDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        if (!isset($meta['customObjects'])) {
            $meta['customObjects'] = [];
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $customObjectAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, CustomObject::class);

            if (!$customObjectAnnotation instanceof CustomObject) {
                continue;
            }

            $this->validateAnnotation($customObjectAnnotation, $doctrineMeta->getName(), $reflectionProperty->getName());

            $meta['customObjects'][$reflectionProperty->getName()] = get_object_vars($customObjectAnnotation);
        }
    }

    /**
     * @param \Darvin\Utils\Mapping\Annotation\CustomObject $annotation  Custom object annotation
     * @param string                                        $objectClass Object class
     * @param string                                        $property    Annotated property
     *
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function validateAnnotation(CustomObject $annotation, $objectClass, $property)
    {
        if (empty($annotation->class) && empty($annotation->classPropertyPath)) {
            throw $this->createPropertyAnnotationInvalidException(
                CustomObject::class,
                $objectClass,
                $property,
                'class or class property path must be provided'
            );
        }
        if (!empty($annotation->class) && !empty($annotation->classPropertyPath)) {
            throw $this->createPropertyAnnotationInvalidException(
                CustomObject::class,
                $objectClass,
                $property,
                'class or class property path must be provided but not both of them'
            );
        }
        if (!empty($annotation->class) && !(class_exists($annotation->class) || interface_exists($annotation->class))) {
            throw $this->createPropertyAnnotationInvalidException(
                CustomObject::class,
                $objectClass,
                $property,
                sprintf('class or interface "%s" does not exist', $annotation->class)
            );
        }
        if (empty($annotation->initPropertyValuePath)) {
            throw $this->createPropertyAnnotationInvalidException(
                CustomObject::class,
                $objectClass,
                $property,
                'init property value path must be provided'
            );
        }
    }
}
