<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration injector
 */
class ConfigInjector
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array  $config Configuration
     * @param string $prefix Parameter name prefix
     */
    public function inject(array $config, string $prefix): void
    {
        foreach ($config as $name => $value) {
            $name = implode('.', [$prefix, $name]);

            $this->container->setParameter($name, $value);

            if (is_array($value) && $this->isAssociative($value)) {
                $this->inject($value, $name);
            }
        }
    }

    /**
     * @param array $array Array
     *
     * @return bool
     */
    private function isAssociative(array $array): bool
    {
        return array_values($array) !== $array;
    }
}
