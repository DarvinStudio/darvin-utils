<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Importable;

/**
 * Importable
 */
interface ImportableInterface
{
    public const IMPORT_ID_FIELD = 'importId';

    /**
     * @return string|null
     */
    public function getImportId(): ?string;

    /**
     * @param string|null $importId Import ID
     */
    public function setImportId(?string $importId): void;
}
