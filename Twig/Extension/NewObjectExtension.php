<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Twig\Extension;

use Darvin\Utils\NewObject\NewObjectCounterInterface;

/**
 * New object Twig extension
 */
class NewObjectExtension extends \Twig_Extension
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
        return array(
            new \Twig_SimpleFunction('utils_count_new_objects', array($this->newObjectCounter, 'count')),
            new \Twig_SimpleFunction('utils_new_objects_countable', array($this->newObjectCounter, 'isCountable')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'darvin_utils_new_object_extension';
    }
}
