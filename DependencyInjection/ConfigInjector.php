<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config injector
 */
class ConfigInjector
{
    /**
     * @param array                                                     $config    Config
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     * @param string                                                    $prefix    Parameter name prefix
     */
    public function inject(array $config, ContainerInterface $container, $prefix)
    {
        foreach ($config as $name => $value) {
            $name = $prefix.'.'.$name;
            $container->setParameter($name, $value);

            if (is_array($value)) {
                $this->inject($value, $container, $name);
            }
        }
    }
}
