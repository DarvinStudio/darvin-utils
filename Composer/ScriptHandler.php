<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Composer;

use Symfony\Component\Process\Process;

/**
 * Script handler
 */
class ScriptHandler
{
    const ENV_ASSETS_VERSION = 'DARVIN_ASSETS_VERSION';

    /**
     * Exports assets version environment variable
     */
    public static function exportAssetsVersion()
    {
        $process = new Process('git log -n 1');

        if (0 !== $process->run()) {
            return;
        }

        $output = $process->getOutput();

        if (empty($output)) {
            return;
        }

        putenv(self::ENV_ASSETS_VERSION.'='.md5($output));
    }
}
