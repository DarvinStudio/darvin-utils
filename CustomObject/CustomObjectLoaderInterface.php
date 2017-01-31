<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\CustomObject;

/**
 * Custom object loader
 */
interface CustomObjectLoaderInterface
{
    /**
     * @param mixed    $objectOrObjects      Object or array of objects
     * @param callable $queryBuilderCallback Callback to process query builder
     *
     * @throws \Darvin\Utils\CustomObject\CustomObjectException
     */
    public function loadCustomObjects($objectOrObjects, callable $queryBuilderCallback = null);

    /**
     * @param string $objectClass Object class
     *
     * @return bool
     */
    public function customObjectsLoadable($objectClass);
}
