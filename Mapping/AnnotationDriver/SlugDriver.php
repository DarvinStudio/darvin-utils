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

use Darvin\Utils\Mapping\Annotation\Slug;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Slug annotation driver
 */
class SlugDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        $meta['slugs'] = array();

        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $slugAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, Slug::ANNOTATION);

            if ($slugAnnotation instanceof Slug) {
                $this->validateSourcePropertyPaths(
                    $slugAnnotation->sourcePropertyPaths,
                    $doctrineMeta->getName(),
                    $reflectionProperty->getName()
                );

                $meta['slugs'][$reflectionProperty->getName()] = get_object_vars($slugAnnotation);
            }
        }
    }

    /**
     * @param array  $sourcePropertyPaths Source property paths
     * @param string $objectClass         Object class
     * @param string $slugProperty        Slug property
     *
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function validateSourcePropertyPaths(array $sourcePropertyPaths, $objectClass, $slugProperty)
    {
        if (empty($sourcePropertyPaths)) {
            throw $this->createPropertyAnnotationInvalidException(
                Slug::ANNOTATION,
                $objectClass,
                $slugProperty,
                'source property paths array must not be empty'
            );
        }

        $lastSourcePropertyPath = $sourcePropertyPaths[count($sourcePropertyPaths) - 1];

        if (false !== strpos($lastSourcePropertyPath, '.')) {
            throw $this->createPropertyAnnotationInvalidException(
                Slug::ANNOTATION,
                $objectClass,
                $slugProperty,
                sprintf(
                    'last source property path must not contain relations ("." symbols), "%s" provided',
                    $lastSourcePropertyPath
                )
            );
        }
        foreach ($sourcePropertyPaths as $sourcePropertyPath) {
            if (substr_count($sourcePropertyPath, '.') > 1) {
                throw $this->createPropertyAnnotationInvalidException(
                    Slug::ANNOTATION,
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
