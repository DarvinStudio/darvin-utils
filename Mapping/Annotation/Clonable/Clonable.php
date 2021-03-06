<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\Annotation\Clonable;

use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Clonable annotation
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Clonable
{
    public const COPYING_POLICY_ALL  = 'ALL';
    public const COPYING_POLICY_NONE = 'NONE';

    /**
     * @var string
     *
     * @Enum({"ALL", "NONE"})
     */
    public $copyingPolicy = self::COPYING_POLICY_NONE;

    /**
     * @var array
     */
    public $callAfter = [];
}
