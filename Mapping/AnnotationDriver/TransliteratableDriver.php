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

use Darvin\Utils\Mapping\Annotation\Transliteratable;
use Darvin\Utils\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Transliteratable annotation driver
 */
class TransliteratableDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        $meta['transliteratable'] = array();

        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $reflectionProperty) {
            $transliteratableAnnotation = $this->reader->getPropertyAnnotation(
                $reflectionProperty,
                Transliteratable::ANNOTATION
            );

            if ($transliteratableAnnotation instanceof Transliteratable) {
                if (!$doctrineMeta->hasField($reflectionProperty->getName())) {
                    $message = sprintf(
                        'Property "%s::$%s" annotated with "%s" annotation must be mapped field.',
                        $doctrineMeta->getName(),
                        $reflectionProperty->getName(),
                        Transliteratable::ANNOTATION
                    );

                    throw new MappingException($message);
                }

                $meta['transliteratable'][$reflectionProperty->getName()] = get_object_vars(
                    $transliteratableAnnotation
                );
            }
        }
    }
}
