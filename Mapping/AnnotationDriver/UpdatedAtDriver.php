<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\AnnotationDriver;

use Darvin\Utils\Mapping\Annotation\UpdatedAt;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Types\Types;

/**
 * Updated at annotation driver
 */
class UpdatedAtDriver extends AbstractDriver
{
    /**
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        if (!isset($meta['updated_at'])) {
            $meta['updatedAt'] = null;
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, UpdatedAt::class);

            if (!$annotation instanceof UpdatedAt) {
                continue;
            }
            if (null !== $meta['updatedAt']) {
                if ($meta['updatedAt'] === $property->getName()) {
                    continue;
                }

                throw $this->createPropertyAnnotationInvalidException(
                    UpdatedAt::class,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('property "%s" is already annotated with this annotation', $meta['updatedAt'])
                );
            }

            $fieldType = $doctrineMeta->getTypeOfField($property->getName());

            if (Types::DATETIME_MUTABLE !== $fieldType) {
                throw $this->createPropertyAnnotationInvalidException(
                    UpdatedAt::class,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('field must be of type "%s", "%s" provided', Types::DATETIME_MUTABLE, $fieldType)
                );
            }

            $meta['updatedAt'] = $property->getName();
        }
    }
}
