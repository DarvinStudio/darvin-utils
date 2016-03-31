<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping;

use Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface;

/**
 * Metadata factory
 */
interface MetadataFactoryInterface
{
    /**
     * @param \Darvin\Utils\Mapping\AnnotationDriver\AnnotationDriverInterface $driver Annotation driver
     */
    public function addAnnotationDriver(AnnotationDriverInterface $driver);

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return mixed[]
     */
    public function getExtendedMetadata($objectOrClass);

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return string
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    public function getIdentifier($objectOrClass);

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    public function getDoctrineMetadata($objectOrClass);
}
