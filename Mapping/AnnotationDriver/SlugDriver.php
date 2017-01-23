<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\AnnotationDriver;

use Darvin\Utils\Mapping\Annotation\ServiceCallback;
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

                if (!empty($slugAnnotation->prefixProvider)) {
                    $meta['slugs'][$reflectionProperty->getName()]['prefixProvider'] = get_object_vars(
                        $slugAnnotation->prefixProvider
                    );
                }
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
    private function validateAnnotation(Slug $annotation, $objectClass, $slugProperty)
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

        $prefixProvider = $annotation->prefixProvider;

        if (!empty($prefixProvider)) {
            $this->validatePrefixProvider($prefixProvider, $objectClass, $slugProperty);
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
                if (!empty($prefixProvider) && 0 === $index) {
                    throw $this->createPropertyAnnotationInvalidException(
                        Slug::class,
                        $objectClass,
                        $slugProperty,
                        sprintf(
                            'source property paths can contain relations ("." symbol) only if prefix provider is not used, "%s" provided',
                            $sourcePropertyPath
                        )
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

    /**
     * @param \Darvin\Utils\Mapping\Annotation\ServiceCallback $prefixProvider Prefix provider
     * @param string                                           $objectClass    Object class
     * @param string                                           $slugProperty   Slug property
     *
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function validatePrefixProvider(ServiceCallback $prefixProvider, $objectClass, $slugProperty)
    {
        foreach (get_object_vars($prefixProvider) as $property => $value) {
            if (empty($value)) {
                throw $this->createPropertyAnnotationInvalidException(
                    Slug::class,
                    $objectClass,
                    $slugProperty,
                    sprintf('prefix provider parameter "%s" must not be empty', $property)
                );
            }
        }
    }
}
