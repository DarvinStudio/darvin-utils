<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Overrider;

use Darvin\AdminBundle\Configuration\SectionConfigurationInterface;
use Darvin\Utils\Override\Config\Model\Subject;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Admin section config overrider
 */
class AdminOverrider implements OverriderInterface
{
    use NameTrait;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Darvin\AdminBundle\Configuration\SectionConfigurationInterface
     */
    private $sectionConfig;

    /**
     * @var array
     */
    private $bundlesMeta;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem                        $filesystem    Filesystem
     * @param \Darvin\AdminBundle\Configuration\SectionConfigurationInterface $sectionConfig Admin section configuration
     * @param array                                                           $bundlesMeta   Bundles metadata
     * @param string                                                          $projectDir    Project directory
     */
    public function __construct(
        Filesystem $filesystem,
        SectionConfigurationInterface $sectionConfig,
        array $bundlesMeta,
        string $projectDir
    ) {
        $this->filesystem = $filesystem;
        $this->sectionConfig = $sectionConfig;
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
        foreach ($subject->getEntities() as $entity) {
            $this->overrideAdmin($entity, $this->bundlesMeta[$subject->getBundle()]['namespace'], $output);
        }
    }

    /**
     * @param string        $entity          Entity
     * @param string        $bundleNamespace Bundle namespace
     * @param callable|null $output          Output callback
     */
    private function overrideAdmin(string $entity, string $bundleNamespace, ?callable $output): void
    {
        $fqcn = implode('\\', [$bundleNamespace, 'Entity', $entity]);

        if (!$this->sectionConfig->hasSection($fqcn)) {
            return;
        }

        $section = $this->sectionConfig->getSection($fqcn);

        if (null === $section->getConfig() || 0 !== strpos($section->getConfig(), '@')) {
            return;
        }

        $configBundle = $configPathname = null;

        foreach (array_keys($this->bundlesMeta) as $bundleName) {
            $prefix = sprintf('@%s/', $bundleName);

            if (0 === strpos($section->getConfig(), $prefix)) {
                $configBundle   = $bundleName;
                $configPathname = substr_replace($section->getConfig(), '', 0, mb_strlen($prefix));

                break;
            }
        }
        if (null === $configBundle || null === $configPathname) {
            return;
        }

        $parts = explode(DIRECTORY_SEPARATOR, $configPathname);
        array_shift($parts);
        array_shift($parts);

        if (empty($parts)) {
            return;
        }

        $targetPathname = implode(
            DIRECTORY_SEPARATOR,
            array_merge([$this->projectDir, 'config', array_shift($parts), StringsUtil::toUnderscore(preg_replace('/Bundle$/', '', $configBundle))], $parts)
        );

        $this->filesystem->dumpFile($targetPathname, sprintf('extends: \'%s\'%s', $section->getConfig(), PHP_EOL));

        if (null !== $output) {
            $output($targetPathname);
        }
    }
}
