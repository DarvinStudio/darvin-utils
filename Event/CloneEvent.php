<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Clone event
 */
class CloneEvent extends Event
{
    /**
     * @var object
     */
    private $original;

    /**
     * @var object|null
     */
    private $clone;

    /**
     * @param object $original Original
     * @param object $clone    Clone
     */
    public function __construct(object $original, object $clone)
    {
        $this->original = $original;
        $this->clone = $clone;
    }

    /**
     * @return object
     */
    public function getOriginal(): object
    {
        return $this->original;
    }

    /**
     * @return object|null
     */
    public function getClone(): ?object
    {
        return $this->clone;
    }

    /**
     * @param object|null $clone clone
     *
     * @return CloneEvent
     */
    public function setClone(?object $clone): CloneEvent
    {
        $this->clone = $clone;

        return $this;
    }
}
