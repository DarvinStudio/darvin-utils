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
        foreach ($subject->getEntities() as $entity) {
            $this->overrideEntity($entity);
        }
    }

    /**
     * @param string $entity Entity class
     */
    private function overrideEntity(string $entity): void
    {
        $content = $this->twig->render('@DarvinUtils/override/entity.php.twig');
    }
}
