<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Config;

use Darvin\Utils\Override\Config\Model\Subject;

/**
 * Override config
 */
interface OverrideConfigInterface
{
    /**
     * @param string      $subjectName Subject name
     * @param string|null $bundleName  Bundle name
     *
     * @return \Darvin\Utils\Override\Config\Model\Subject
     * @throws \InvalidArgumentException
     */
    public function getSubject(string $subjectName, ?string $bundleName): Subject;
}