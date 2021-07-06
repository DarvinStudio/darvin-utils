<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DependencyInjection;

use Darvin\Utils\DependencyInjection\Exception\UnableToConfigureExtensionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Extension configurator
 */
class ExtensionConfigurator
{
    const FILE_EXTENSION = '.yaml';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var \Symfony\Component\Config\FileLocator
     */
    private $fileLocator;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container Container builder
     * @param string                                                  $configDir Extension configuration file directory
     */
    public function __construct(ContainerBuilder $container, $configDir)
    {
        $this->container = $container;

        $this->fileLocator = new FileLocator($configDir);
    }

    /**
     * @param string[]|string $extensions Extension aliases
     *
     * @throws \Darvin\Utils\DependencyInjection\Exception\UnableToConfigureExtensionException
     */
    public function configure($extensions)
    {
        if (!is_array($extensions)) {
            $extensions = [$extensions];
        }
        foreach ($extensions as $extension) {
            $filename = $extension;

            $extension = preg_replace('/\/.*$/', '', $extension);

            if (!$this->container->hasExtension($extension)) {
                continue;
            }
            try {
                $pathname = $this->fileLocator->locate($filename.self::FILE_EXTENSION);
            } catch (\Exception $ex) {
                throw new UnableToConfigureExtensionException($extension, $ex->getMessage());
            }

            $yaml = @file_get_contents($pathname);

            if (false === $yaml) {
                throw new UnableToConfigureExtensionException($extension, sprintf('unable to read file "%s"', $pathname));
            }
            try {
                $config = Yaml::parse($yaml);
            } catch (\Exception $ex) {
                throw new UnableToConfigureExtensionException($extension, $ex->getMessage());
            }
            if (!is_array($config)) {
                throw new UnableToConfigureExtensionException($extension, 'configuration is not array');
            }
            if (!isset($config[$extension])) {
                throw new UnableToConfigureExtensionException($extension, sprintf('configuration does not contain root key "%s"', $extension));
            }
            if (isset($config['parameters']) && is_array($config['parameters'])) {
                foreach ($config['parameters'] as $key => $value) {
                    $this->container->setParameter($key, $value);
                }
            }

            $this->container->prependExtensionConfig($extension, $config[$extension]);
        }
    }
}
