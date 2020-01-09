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
use Twig\Environment;

/**
 * Entity overrider
 */
class EntityOverrider implements OverriderInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $bundlesMeta;

    /**
     * @param \Twig\Environment $twig        Twig
     * @param array             $bundlesMeta Bundles metadata
     */
    public function __construct(Environment $twig, array $bundlesMeta)
    {
        $this->twig = $twig;
        $this->bundlesMeta = $bundlesMeta;
    }

    /**
     * {@inheritDoc}
     */
    public function override(Subject $subject): void
    {
        if (!isset($this->bundlesMeta[$subject->getBundle()])) {
            throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist.', $subject->getBundle()));
        }
        foreach ($subject->getEntities() as $entity) {
            $this->overrideEntity($entity, $subject->getBundle(), $this->bundlesMeta[$subject->getBundle()]['namespace']);
        }
    }

    /**
     * @param string $entity          Entity class
     * @param string $bundleName      Bundle name
     * @param string $bundleNamespace Bundle namespace
     */
    private function overrideEntity(string $entity, string $bundleName, string $bundleNamespace): void
    {
        $parts = explode('\\', $entity);

        $class            = array_pop($parts);
        $entityNamespace  = implode('\\', $parts);
        $packageNamespace = preg_replace('/^Darvin|Bundle$/', '', $bundleName);

        $content = $this->twig->render('@DarvinUtils/override/entity.php.twig', [
            'bundle_namespace'  => $bundleNamespace,
            'entity_namespace'  => $entityNamespace,
            'package_namespace' => $packageNamespace,
            'class'             => $class,
        ]);

        dump($content);
    }
}
