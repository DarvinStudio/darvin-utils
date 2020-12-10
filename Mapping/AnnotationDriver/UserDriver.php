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

use Darvin\Utils\Mapping\Annotation\User;
use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * User annotation driver
 */
class UserDriver extends AbstractDriver
{
    /**
     * {@inheritDoc}
     */
    public function readMetadata(ClassMetadata $doctrineMeta, array &$meta): void
    {
        if (!isset($meta['user'])) {
            $meta['user'] = [];
        }
        foreach ($doctrineMeta->getReflectionClass()->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, User::class);

            if (!$annotation instanceof User) {
                continue;
            }
            if (!empty($meta['user'])) {
                if ($meta['user']['property'] === $property->getName()) {
                    continue;
                }

                throw $this->createPropertyAnnotationInvalidException(
                    User::class,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    sprintf('property "%s" is already annotated with this annotation', $meta['user']['property'])
                );
            }
            if (!$doctrineMeta->hasAssociation($property->getName())) {
                throw $this->createPropertyAnnotationInvalidException(
                    User::class,
                    $doctrineMeta->getName(),
                    $property->getName(),
                    'property must be mapped association'
                );
            }

            $meta['user'] = [
                'property' => $property->getName(),
                'roles'    => $annotation->roles,
            ];
        }
    }
}
