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
     * @param mixed    $objectOrObjects            Object or array of objects
     * @param bool     $exceptionOnMissingMetadata Whether to throw exception if custom object metadata is missing
     * @param callable $queryBuilderCallback       Callback to process query builder
     */
    public function loadCustomObjects($objectOrObjects, $exceptionOnMissingMetadata = true, callable $queryBuilderCallback = null);
}
