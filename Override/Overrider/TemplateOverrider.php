<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Overrider;

use Darvin\Utils\Override\Config\Model\Subject;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Template overrider
 */
class TemplateOverrider implements OverriderInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $bundlesMeta;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem  Filesystem
     * @param array                                    $bundlesMeta Bundles metadata
     * @param string                                   $projectDir  Project directory
     */
    public function __construct(Filesystem $filesystem, array $bundlesMeta, string $projectDir)
    {
        $this->filesystem = $filesystem;
        $this->bundlesMeta = $bundlesMeta;
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritDoc}
     */
    public function override(Subject $subject, ?callable $output = null): void
    {
        if (!isset($this->bundlesMeta[$subject->getBundle()])) {
            throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist.', $subject->getBundle()));
        }
        foreach ($subject->getTemplates() as $template) {
            $this->overrideTemplate($template, $subject->getBundle(), $this->bundlesMeta[$subject->getBundle()]['path'], $output);
        }
    }

    /**
     * @param string        $template   Template
     * @param string        $bundleName Bundle name
     * @param string        $bundlePath Bundle path
     * @param callable|null $output     Output callback
     *
     * @throws \InvalidArgumentException
     */
    private function overrideTemplate(string $template, string $bundleName, string $bundlePath, ?callable $output): void
    {
        $originPath = implode(DIRECTORY_SEPARATOR, [$bundlePath, 'Resources', 'views', $template]);

        if (!$this->filesystem->exists($originPath)) {
            throw new \InvalidArgumentException(sprintf('Template file or directory "%s" does not exist.', $originPath));
        }

        $targetPath = implode(DIRECTORY_SEPARATOR, [$this->projectDir, 'templates', 'bundles', $bundleName, $template]);

        if (is_dir($originPath)) {
            $this->filesystem->mirror($originPath, $targetPath, null, [
                'override' => true,
            ]);
        } else {
            $this->filesystem->copy($originPath, $targetPath, true);
        }
        if (null !== $output) {
            $output($targetPath);
        }
    }
}
