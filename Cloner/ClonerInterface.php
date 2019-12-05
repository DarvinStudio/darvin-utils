<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Cloner;

/**
 * Cloner
 */
interface ClonerInterface
{
    /**
     * @param object $object Object to clone
     *
     * @return object
     * @throws \InvalidArgumentException
     */
    public function createClone(object $object): object;
}
