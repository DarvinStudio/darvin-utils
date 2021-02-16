<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\View\Data\Factory;

use Darvin\Utils\View\Data\Model\DataView;

/**
 * Data view factory
 */
interface DataViewFactoryInterface
{
    /**
     * @param mixed       $data Data
     * @param string|null $name Name
     *
     * @return \Darvin\Utils\View\Data\Model\DataView
     */
    public function createView($data, ?string $name = null): DataView;
}
