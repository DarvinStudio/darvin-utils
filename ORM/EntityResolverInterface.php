<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ORM;

/**
 * Entity resolver
 */
interface EntityResolverInterface
{
    /**
     * @param string $entity Entity class or interface
     *
     * @return string
     */
    public function resolve(string $entity): string;

    /**
     * @param string $entity Entity class or interface
     *
     * @return string
     */
    public function reverseResolve(string $entity): string;
}
