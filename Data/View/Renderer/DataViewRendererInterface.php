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
    public const TEMPLATE_BLOCK = 'block';
    public const TEMPLATE_TABLE = 'table';
    public const TEMPLATE_TEXT  = 'text';

    /**
     * @param string                                 $template Template
     * @param \Darvin\Utils\Data\View\Model\DataView $view     View
     * @param array                                  $options  Options
     *
     * @return string
     */
    public function render(string $template, DataView $view, array $options = []): string;
}
