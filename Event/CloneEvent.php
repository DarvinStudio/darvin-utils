<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Event;

use Symfony\Component\EventDispatcher\Event;

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
     * @var object
     */
    private $clone;

    /**
     * @param object $original Original
     * @param object $clone    Clone
     */
    public function __construct($original, $clone)
    {
        $this->original = $original;
        $this->clone = $clone;
    }

    /**
     * @return object
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @return object
     */
    public function getClone()
    {
        return $this->clone;
    }
}
