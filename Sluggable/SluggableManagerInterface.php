<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Sluggable;

/**
 * Sluggable manager
 */
interface SluggableManagerInterface
{
    /**
     * @param object|string $objectOrClass Object or class
     *
     * @return bool
     */
    public function isSluggable($objectOrClass): bool;

    /**
     * @param object $object             Object
     * @param bool   $triggerUpdateEvent Whether to trigger update event
     * @param string $prefix             Slug prefix
     *
     * @return bool Is at least one slug updated
     * @throws \InvalidArgumentException
     */
    public function generateSlugs(object $object, bool $triggerUpdateEvent = false, ?string $prefix = null): bool;
}
