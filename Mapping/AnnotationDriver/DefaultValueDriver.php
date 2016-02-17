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

use Darvin\Utils\Mapping\Annotation\DefaultValue;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Default value annotation driver
 */
class DefaultValueDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        if (!isset($meta['defaultValues'])) {
            $meta['defaultValues'] = array();
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $defaultValueAnnotation = $this->reader->getPropertyAnnotation($reflectionProperty, DefaultValue::ANNOTATION);

            if ($defaultValueAnnotation instanceof DefaultValue) {
                $meta['defaultValues'][$reflectionProperty->getName()] = $defaultValueAnnotation->sourcePropertyPath;
            }
        }
    }
}
