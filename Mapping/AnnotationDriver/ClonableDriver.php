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

use Darvin\Utils\Mapping\Annotation\Clonable\Clonable;
use Darvin\Utils\Mapping\Annotation\Clonable\Copy;
use Darvin\Utils\Mapping\Annotation\Clonable\Skip;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Clonable annotation driver
 */
class ClonableDriver extends AbstractDriver
{
    /**
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        $reflectionClass = $doctrineMeta->getReflectionClass();

        $copyingPolicy = null;

        if (isset($meta['clonable'])) {
            $copyingPolicy = $meta['clonable']['copyingPolicy'];
        }

        $clonableAnnotation = $this->reader->getClassAnnotation($reflectionClass, Clonable::class);

        if ($clonableAnnotation instanceof Clonable) {
            $copyingPolicy = $clonableAnnotation->copyingPolicy;
        }
        if (null === $copyingPolicy) {
            return;
        }
        if (!isset($meta['clonable'])) {
            $meta['clonable'] = [];
        }

        $meta['clonable']['copyingPolicy'] = $copyingPolicy;

        if (!isset($meta['clonable']['callAfter'])) {
            $meta['clonable']['callAfter'] = [];
        }
        if ($clonableAnnotation instanceof Clonable) {
            $meta['clonable']['callAfter'] = array_merge($meta['clonable']['callAfter'], $clonableAnnotation->callAfter);
        }

        $properties = $this->getPropertiesToCopy($reflectionClass, $copyingPolicy, $doctrineMeta->getIdentifier());

        $meta['clonable']['properties'] = isset($meta['clonable']['properties'])
            ? array_merge($meta['clonable']['properties'], $properties)
            : $properties;
    }

    /**
     * @param \ReflectionClass $reflectionClass Reflection class
     * @param string           $copyingPolicy   Copying policy
     * @param array            $idProperties    Identifier properties
     *
     * @return array
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    private function getPropertiesToCopy(\ReflectionClass $reflectionClass, string $copyingPolicy, array $idProperties): array
    {
        $properties = [];

        switch ($copyingPolicy) {
            case Clonable::COPYING_POLICY_ALL:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null === $this->reader->getPropertyAnnotation($reflectionProperty, Skip::class)
                        && !in_array($reflectionProperty->getName(), $idProperties)
                    ) {
                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
            case Clonable::COPYING_POLICY_NONE:
                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    if (null !== $this->reader->getPropertyAnnotation($reflectionProperty, Copy::class)) {
                        if (in_array($reflectionProperty->getName(), $idProperties)) {
                            throw $this->createPropertyAnnotationInvalidException(
                                Copy::class,
                                $reflectionClass->getName(),
                                $reflectionProperty->getName(),
                                'property is identifier and it\'s value must not be copied during cloning'
                            );
                        }

                        $properties[] = $reflectionProperty->getName();
                    }
                }

                break;
        }

        return $properties;
    }
}
