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

use Darvin\Utils\Data\View\Model\DataView;

/**
 * Data view renderer
 */
interface DataViewRendererInterface
{
    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view View
     *
     * @return string
     */
    public function renderBlock(DataView $view): string;

    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view View
     *
     * @return string
     */
    public function renderTable(DataView $view): string;

    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view View
     *
     * @return string
     */
    public function renderText(DataView $view): string;
}
