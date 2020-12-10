<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Metadata factory
 */
interface MetadataFactoryInterface
{
    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return array
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    public function getExtendedMetadata($objectOrClass): array;

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return mixed
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    public function getIdentifier($objectOrClass);

    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return \Doctrine\Persistence\Mapping\ClassMetadata
     * @throws \Darvin\Utils\Mapping\MappingException
     */
    public function getDoctrineMetadata($objectOrClass): ClassMetadata;
}
