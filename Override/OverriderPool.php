<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
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
     *
     * @throws \InvalidArgumentException
     */
    public function addOverrider(OverriderInterface $overrider): void
    {
        $name = $overrider->getName();

        if (isset($this->overriders[$name])) {
            throw new \InvalidArgumentException(sprintf('Overrider "%s" already exists.', $name));
        }

        $this->overriders[$name] = $overrider;
    }

    /**
     * {@inheritDoc}
     */
    public function override(string $subjectName, ?string $bundleName = null, ?string $overriderName = null, ?callable $output = null): void
    {
        $subject = $this->config->getSubject($subjectName, $bundleName);

        if (null !== $overriderName) {
            $this->getOverrider($overriderName)->override($subject, $output);

            return;
        }
        foreach ($this->overriders as $overrider) {
            $overrider->override($subject, $output);
        }
    }

    /**
     * @param string $name Overrider name
     *
     * @return \Darvin\Utils\Override\Overrider\OverriderInterface
     * @throws \InvalidArgumentException
     */
    private function getOverrider(string $name): OverriderInterface
    {
        if (!isset($this->overriders[$name])) {
            $existingNames = array_keys($this->overriders);

            sort($existingNames, SORT_STRING);

            throw new \InvalidArgumentException(
                sprintf('Overrider "%s" does not exist. Existing overriders: "%s".', $name, implode('", "', $existingNames))
            );
        }

        return $this->overriders[$name];
    }
}
