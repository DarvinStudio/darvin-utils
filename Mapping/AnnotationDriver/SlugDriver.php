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
                $meta['slugs'][$reflectionProperty->getName()] = get_object_vars($slugAnnotation);
            }
        }
    }
}
