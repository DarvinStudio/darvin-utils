<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Data\View\Renderer;

/**
 * Data view renderer
 */
interface DataViewRendererInterface
{
    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderBlock($data, ?string $name = null, ?string $transDomain = null): string;

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderTable($data, ?string $name = null, ?string $transDomain = null): string;

    /**
     * @param mixed       $data        Data
     * @param string|null $name        Name
     * @param string|null $transDomain Translation domain
     *
     * @return string
     */
    public function renderText($data, ?string $name = null, ?string $transDomain = null): string;
}
