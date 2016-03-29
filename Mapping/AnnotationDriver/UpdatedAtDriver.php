<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\AnnotationDriver;

use Darvin\Utils\Mapping\Annotation\UpdatedAt;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Types\Type;

/**
 * Updated at annotation driver
 */
class UpdatedAtDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        if (!isset($meta['updated_at'])) {
            $meta['updatedAt'] = null;
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, UpdatedAt::ANNOTATION);

            if (!$annotation instanceof UpdatedAt) {
                continue;
            }
            if (!empty($meta['updatedAt'])) {
                throw $this->createPropertyAnnotationInvalidException(
                    UpdatedAt::ANNOTATION,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('property "%s" is already annotated with this annotation', $meta['updatedAt'])
                );
            }

            $fieldType = $doctrineMeta->getTypeOfField($property->getName());

            if (Type::DATETIME !== $fieldType) {
                throw $this->createPropertyAnnotationInvalidException(
                    UpdatedAt::ANNOTATION,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('field must be of type "%s", "%s" provided', Type::DATETIME, $fieldType)
                );
            }

            $meta['updatedAt'] = $property->getName();
        }
    }
}
