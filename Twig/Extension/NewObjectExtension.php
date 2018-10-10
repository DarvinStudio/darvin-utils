<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension;

use Darvin\Utils\NewObject\NewObjectCounterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * New object Twig extension
 */
class NewObjectExtension extends AbstractExtension
{
    /**
     * @var \Darvin\Utils\NewObject\NewObjectCounterInterface
     */
    private $newObjectCounter;

    /**
     * @param \Darvin\Utils\NewObject\NewObjectCounterInterface $newObjectCounter New object counter
     */
    public function __construct(NewObjectCounterInterface $newObjectCounter)
    {
        $this->newObjectCounter = $newObjectCounter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('utils_count_new_objects', [$this->newObjectCounter, 'count']),
            new TwigFunction('utils_new_objects_countable', [$this->newObjectCounter, 'isCountable']),
        ];
    }
}
