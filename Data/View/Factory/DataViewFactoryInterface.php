<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Data\View\Factory;

use Darvin\Utils\Data\View\Model\DataView;

/**
 * Data view factory
 */
interface DataViewFactoryInterface
{
    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     * @param bool        $allowEmpty  Whether to allow empty view
     *
     * @return \Darvin\Utils\Data\View\Model\DataView|null
     */
    public function createView($data, ?string $name = null, ?string $transDomain = null, bool $allowEmpty = false): ?DataView;
}
