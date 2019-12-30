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
     * @param \Darvin\Utils\Override\Config\OverrideConfigInterface $config Override configuration
     */
    public function __construct(OverrideConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function override(string $subjectName, ?string $bundleName = null): void
    {
        dump($this->config->getSubject($subjectName, $bundleName));
    }
}
