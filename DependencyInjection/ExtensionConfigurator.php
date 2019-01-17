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

use Darvin\Utils\DependencyInjection\Exception\UnableToConfigureExtensionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Extension configurator
 */
class ExtensionConfigurator
{
    /**
     * @var \Symfony\Component\Config\FileLocator
     */
    private $fileLocator;

    /**
     * @param string $configDir Extension configuration file directory
     */
    public function __construct(string $configDir)
    {
        $this->fileLocator = new FileLocator($configDir);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container  Container builder
     * @param string[]|string                                         $extensions Extension aliases
     *
     * @throws \Darvin\Utils\DependencyInjection\Exception\UnableToConfigureExtensionException
     */
    public function configure(ContainerBuilder $container, $extensions): void
    {
        if (!is_array($extensions)) {
            $extensions = [$extensions];
        }
        foreach ($extensions as $extension) {
            if (!$container->hasExtension($extension)) {
                continue;
            }
            try {
                $filename = $this->fileLocator->locate(sprintf('%s.yaml', $extension));
            } catch (\Exception $ex) {
                throw new UnableToConfigureExtensionException($extension, $ex->getMessage());
            }

            $yaml = @file_get_contents($filename);

            if (false === $yaml) {
                throw new UnableToConfigureExtensionException($extension, sprintf('unable to read file "%s"', $filename));
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
                    $container->setParameter($key, $value);
                }
            }

            $container->prependExtensionConfig($extension, $config[$extension]);
        }
    }
}
