<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Callback;

/**
 * Callback runner
 */
interface CallbackRunnerInterface
{
    /**
     * @param string      $id      Class or service ID
     * @param string|null $method  Method to call
     * @param mixed       ...$args Arguments to pass to callable method
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function runCallback(string $id, ?string $method = null, ...$args);
}
