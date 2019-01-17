<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection;

use Darvin\Utils\DependencyInjection\Exception\ConfigNotLoadableException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Configuration file loader
 */
class ConfigFileLoader
{
    public const PARAM_BUNDLE   = 'bundle';
    public const PARAM_CALLBACK = 'callback';
    public const PARAM_ENV      = 'env';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var array
     */
    private $bundles;

    /**
     * @var string
     */
    private $env;

    /**
     * @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader
     */
    private $loader;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container Container builder
     * @param string                                                  $dir       Configuration file directory
     */
    public function __construct(ContainerBuilder $container, string $dir)
    {
        $this->container = $container;
        $this->dir       = $dir;

        $this->bundles = $container->getParameter('kernel.bundles');
        $this->env     = $container->getParameter('kernel.environment');
        $this->loader  = new YamlFileLoader($container, new FileLocator($dir));
    }

    /**
     * @param array  $configs   Configuration files
     * @param string $extension File name extension
     *
     * @throws \Darvin\Utils\DependencyInjection\Exception\ConfigNotLoadableException
     */
    public function load(array $configs, string $extension = 'yaml'): void
    {
        foreach ($configs as $name => $params) {
            if (!is_array($params)) {
                $name   = $params;
                $params = [];
            }
            if (!preg_match('/\.[0-9a-z]+$/i', $name)) {
                $name .= sprintf('.%s', $extension);
            }
            if ($this->isLoadable($name, $params)) {
                try {
                    $this->loader->load($name);
                } catch (\Exception $ex) {
                    throw new ConfigNotLoadableException($name, $this->dir, $ex->getMessage());
                }
            }
        }
    }

    /**
     * @param string $name   Configuration file name
     * @param array  $params Parameters
     *
     * @return bool
     * @throws \Darvin\Utils\DependencyInjection\Exception\ConfigNotLoadableException
     */
    private function isLoadable(string $name, array $params): bool
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case self::PARAM_BUNDLE:
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $bundle) {
                        if (!isset($this->bundles[$bundle])) {
                            return false;
                        }
                    }

                    break;
                case self::PARAM_CALLBACK:
                    if (!is_callable($value)) {
                        throw new ConfigNotLoadableException($name, $this->dir, '"callback" parameter\'s value is not callable');
                    }

                    $result = $value();

                    if (!is_bool($result)) {
                        throw new ConfigNotLoadableException($name, $this->dir, sprintf('callback must return boolean, got "%s"', gettype($result)));
                    }
                    if (!$result) {
                        return false;
                    }

                    break;
                case self::PARAM_ENV:
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    if (!in_array($this->env, $value)) {
                        return false;
                    }

                    break;
                default:
                    throw new ConfigNotLoadableException($name, $this->dir, sprintf('parameter "%s" is not supported', $key));
            }
        }

        return true;
    }
}
