<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection\Exception;

/**
 * Configuration file not loadable exception
 */
class ConfigNotLoadableException extends \Exception
{
    /**
     * @param string $name  Configuration file name
     * @param string $dir   Configuration file directory
     * @param string $error Error message
     */
    public function __construct(string $name, string $dir, string $error)
    {
        parent::__construct(sprintf('Unable to load configuration file "%s" from directory "%s": %s.', $name, $dir, lcfirst($error)));
    }
}
