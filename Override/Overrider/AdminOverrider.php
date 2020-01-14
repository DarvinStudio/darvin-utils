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

/**
 * Admin section config overrider
 */
class AdminOverrider implements OverriderInterface
{
    /**
     * @var \Darvin\AdminBundle\Configuration\SectionConfigurationInterface
     */
    private $sectionConfig;

    /**
     * @var array
     */
    private $bundlesMeta;

    /**
     * @param \Darvin\AdminBundle\Configuration\SectionConfigurationInterface $sectionConfig Admin section configuration
     * @param array                                                           $bundlesMeta   Bundles metadata
     */
    public function __construct(SectionConfigurationInterface $sectionConfig, array $bundlesMeta)
    {
        $this->sectionConfig = $sectionConfig;
        $this->bundlesMeta = $bundlesMeta;
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
            $this->overrideAdmin($entity, $this->bundlesMeta[$subject->getBundle()]['namespace']);
        }
    }

    /**
     * @param string $entity          Entity
     * @param string $bundleNamespace Bundle namespace
     */
    private function overrideAdmin(string $entity, string $bundleNamespace): void
    {
        $fqcn = implode('\\', [$bundleNamespace, 'Entity', $entity]);

        if (!$this->sectionConfig->hasSection($fqcn)) {
            return;
        }

        $section = $this->sectionConfig->getSection($fqcn);

        dump($section);
    }
}
