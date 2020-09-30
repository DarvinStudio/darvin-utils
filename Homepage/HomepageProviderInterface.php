<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Homepage;

/**
 * Homepage provider
 */
interface HomepageProviderInterface
{
    /**
     * @return object|null
     */
    public function getHomepage();

    /**
     * @param object|null $object Object to check
     *
     * @return bool
     */
    public function isHomepage($object);
}
