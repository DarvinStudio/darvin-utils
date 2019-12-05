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

use Darvin\Utils\Mapping\Annotation\Slug;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Slug annotation driver
 */
class SlugDriver extends AbstractDriver
{
    /**
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        if (!isset($meta['slugs'])) {
            $meta['slugs'] = [];
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $slugAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Slug::class);

            if ($slugAnnotation instanceof Slug) {
                $this->validateAnnotation(
                    $slugAnnotation,
                    $doctrineMeta->getName(),
                    $reflectionProperty->getName()
                );

                $meta['slugs'][$reflectionProperty->getName()] = get_object_vars($slugAnnotation);
            }
        }
    }

    /**
     * @param \Darvin\Utils\Mapping\Annotation\Slug $annotation   Annotation
     * @param string                                $objectClass  Object class
     * @param string                                $slugProperty Slug property
     *
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function validateAnnotation(Slug $annotation, string $objectClass, string $slugProperty): void
    {
        $sourcePropertyPaths = $annotation->sourcePropertyPaths;

        if (empty($sourcePropertyPaths)) {
            throw $this->createPropertyAnnotationInvalidException(
                Slug::class,
                $objectClass,
                $slugProperty,
                'source property paths array must not be empty'
            );
        }
        foreach (array_values($sourcePropertyPaths) as $index => $sourcePropertyPath) {
            if (false !== strpos($sourcePropertyPath, '.')) {
                if ($index > 0) {
                    throw $this->createPropertyAnnotationInvalidException(
                        Slug::class,
                        $objectClass,
                        $slugProperty,
                        'only first source property path can contain relation ("." symbol)'
                    );
                }
            }
            if (substr_count($sourcePropertyPath, '.') > 1) {
                throw $this->createPropertyAnnotationInvalidException(
                    Slug::class,
                    $objectClass,
                    $slugProperty,
                    sprintf(
                        'source property path can contain only single relation ("." symbol), "%s" provided',
                        $sourcePropertyPath
                    )
                );
            }
        }
    }
}
