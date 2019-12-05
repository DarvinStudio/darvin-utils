<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        if (!isset($meta['newObjectFlag'])) {
            $meta['newObjectFlag'] = [];
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $annotation = $this->reader->getPropertyAnnotation($reflectionProperty, NewObjectFlag::class);

            if (!$annotation instanceof NewObjectFlag) {
                continue;
            }

            $class    = $doctrineMeta->getName();
            $property = $reflectionProperty->getName();

            if (!empty($meta['newObjectFlag']) && $meta['newObjectFlag']['property'] !== $property) {
                throw $this->createPropertyAnnotationInvalidException(
                    NewObjectFlag::class,
                    $class,
                    $property,
                    sprintf('property "%s" is already marked as new object flag', $meta['newObjectFlag']['property'])
                );
            }
            if (!$doctrineMeta->hasField($property)) {
                throw $this->createPropertyAnnotationInvalidException(
                    NewObjectFlag::class,
                    $class,
                    $property,
                    'property must be mapped field'
                );
            }

            $meta['newObjectFlag'] = array_merge(get_object_vars($annotation), [
                'property' => $property,
            ]);
        }
    }
}
