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

use Darvin\Utils\Mapping\Annotation\NewObjectFlag;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * New object flag annotation driver
 */
class NewObjectFlagDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        if (!isset($meta['newObjectFlag'])) {
            $meta['newObjectFlag'] = null;
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            if (null === $this->reader->getPropertyAnnotation($reflectionProperty, NewObjectFlag::ANNOTATION)) {
                continue;
            }

            $objectClass = $doctrineMeta->getName();
            $property = $reflectionProperty->getName();

            if (!empty($meta['newObjectFlag'])) {
                if ($meta['newObjectFlag'] === $property) {
                    continue;
                }

                throw $this->createPropertyAnnotationInvalidException(
                    NewObjectFlag::ANNOTATION,
                    $objectClass,
                    $property,
                    sprintf('property "%s" is already marked as new object flag', $meta['newObjectFlag'])
                );
            }
            if (!$doctrineMeta->hasField($property)) {
                throw $this->createPropertyAnnotationInvalidException(
                    NewObjectFlag::ANNOTATION,
                    $objectClass,
                    $property,
                    'property must be mapped field'
                );
            }

            $meta['newObjectFlag'] = $property;
        }
    }
}
