<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Overrider;

use Darvin\Utils\Override\Config\Model\Subject;

/**
 * Overrider
 */
interface OverriderInterface
{
    /**
     * @param \Darvin\Utils\Override\Config\Model\Subject $subject Subject
     *
     * @throws \InvalidArgumentException
     */
    public function override(Subject $subject): void;
}
