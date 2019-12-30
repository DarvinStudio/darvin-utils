<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override;

/**
 * Overrider
 */
interface OverriderInterface
{
    /**
     * @param string      $subjectName Subject name
     * @param string|null $bundleName  Bundle name
     *
     * @throws \InvalidArgumentException
     */
    public function override(string $subjectName, ?string $bundleName = null): void;
}
