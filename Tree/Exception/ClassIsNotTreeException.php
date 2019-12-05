<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Tree\Exception;

/**
 * Class is not a tree exception
 */
class ClassIsNotTreeException extends \Exception
{
    /**
     * @param string $class Class
     */
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Class "%s" is not a tree.', $class));
    }
}
