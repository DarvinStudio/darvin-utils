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

use Darvin\Utils\DependencyInjection\Exception\ExtensionConfigNotPrependableException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Extension configuration prepender
 */
class ExtensionConfigPrepender
{
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
    public function __construct(ContainerBuilder $container, string $configDir)
    {
        $this->container = $container;

        $this->fileLocator = new FileLocator($configDir);
    }

    /**
     * @param string[] $extensions Extension aliases
     *
     * @throws \Darvin\Utils\DependencyInjection\Exception\ExtensionConfigNotPrependableException
     */
    public function prependConfigs(array $extensions): void
    {
        foreach ($extensions as $extension) {
            if (!$this->container->hasExtension($extension)) {
                continue;
            }
            try {
                $filename = $this->fileLocator->locate(sprintf('%s.yaml', $extension));
            } catch (\Exception $ex) {
                throw new ExtensionConfigNotPrependableException($extension, $ex->getMessage());
            }

            $yaml = @file_get_contents($filename);

            if (false === $yaml) {
                throw new ExtensionConfigNotPrependableException($extension, sprintf('unable to read file "%s"', $filename));
            }
            try {
                $config = Yaml::parse($yaml);
            } catch (\Exception $ex) {
                throw new ExtensionConfigNotPrependableException($extension, $ex->getMessage());
            }
            if (!is_array($config)) {
                throw new ExtensionConfigNotPrependableException($extension, 'configuration is not array');
            }
            if (!isset($config[$extension])) {
                throw new ExtensionConfigNotPrependableException($extension, sprintf('configuration does not contain root key "%s"', $extension));
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
