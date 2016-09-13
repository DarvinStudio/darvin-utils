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

use Darvin\Utils\Mapping\Annotation\User;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * User annotation driver
 */
class UserDriver extends AbstractDriver
{
    /**
     * {@inheritdoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta)
    {
        if (!isset($meta['user'])) {
            $meta['user'] = null;
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $property) {
            if (null === $this->reader->getPropertyAnnotation($property, User::ANNOTATION)) {
                continue;
            }
            if (!empty($meta['user'])) {
                if ($meta['user'] === $property->getName()) {
                    continue;
                }

                throw $this->createPropertyAnnotationInvalidException(
                    User::ANNOTATION,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('property "%s" is already annotated with this annotation', $meta['user'])
                );
            }
            if (!$doctrineMeta->hasAssociation($property->getName())) {
                throw $this->createPropertyAnnotationInvalidException(
                    User::ANNOTATION,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    'field must be mapped association'
                );
            }

            $meta['user'] = $property->getName();
        }
    }
}
