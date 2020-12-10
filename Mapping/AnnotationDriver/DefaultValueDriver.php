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

use Darvin\Utils\Mapping\Annotation\DefaultValue;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Default value annotation driver
 */
class DefaultValueDriver extends AbstractDriver
{
    /**
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        if (!isset($meta['defaultValues'])) {
            $meta['defaultValues'] = [];
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $defaultValueAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, DefaultValue::class);

            if ($defaultValueAnnotation instanceof DefaultValue) {
                $meta['defaultValues'][$reflectionProperty->getName()] = $defaultValueAnnotation->sourcePropertyPath;
            }
        }
    }
}
