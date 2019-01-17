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
 * Unable to configure extension exception
 */
class UnableToConfigureExtensionException extends \Exception
{
    /**
     * @param string $extension Extension alias
     * @param string $error     Error message
     */
    public function __construct(string $extension, string $error)
    {
        parent::__construct(sprintf('Unable to configure extension "%s": %s.', $extension, lcfirst($error)));
    }
}
