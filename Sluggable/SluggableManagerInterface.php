<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
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
     * @param mixed $objectOrClass Object or class
     *
     * @return bool
     */
    public function isSluggable($objectOrClass);

    /**
     * @param object $object              Object
     * @param bool   $dispatchUpdateEvent Whether to dispatch update event
     *
     * @return bool Is at least one slug updated
     */
    public function generateSlugs($object, $dispatchUpdateEvent = false);

    /**
     * @param object $object       Object
     * @param string $slugProperty Slug property
     *
     * @return string
     */
    public function getSlugPrefix($object, $slugProperty);
}
