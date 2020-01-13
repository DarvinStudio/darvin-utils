<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override;

use Darvin\Utils\Override\Config\OverrideConfigInterface;
use Darvin\Utils\Override\Overrider\OverriderInterface;

/**
 * Overrider pool
 */
class OverriderPool implements OverriderPoolInterface
{
    /**
     * @var \Darvin\Utils\Override\Config\OverrideConfigInterface
     */
    private $config;

    /**
     * @var \Darvin\Utils\Override\Overrider\OverriderInterface[]
     */
    private $overriders;

    /**
     * @param \Darvin\Utils\Override\Config\OverrideConfigInterface $config Override configuration
     */
    public function __construct(OverrideConfigInterface $config)
    {
        $this->config = $config;

        $this->overriders = [];
    }

    /**
     * @param \Darvin\Utils\Override\Overrider\OverriderInterface $overrider Overrider
     */
    public function addOverrider(OverriderInterface $overrider): void
    {
        $this->overriders[] = $overrider;
    }

    /**
     * {@inheritDoc}
     */
    public function override(string $subjectName, ?string $bundleName = null, ?callable $output = null): void
    {
        $subject = $this->config->getSubject($subjectName, $bundleName);

        foreach ($this->overriders as $overrider) {
            $overrider->override($subject, $output);
        }
    }
}
