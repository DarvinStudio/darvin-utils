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

use Darvin\AdminBundle\Configuration\SectionConfiguration;
use Darvin\Utils\Override\Config\Model\Subject;

/**
 * Admin section config overrider
 */
class AdminOverrider implements OverriderInterface
{
    /**
     * @var \Darvin\AdminBundle\Configuration\SectionConfiguration
     */
    private $adminSectionConfig;

    /**
     * @param \Darvin\AdminBundle\Configuration\SectionConfiguration $adminSectionConfig Admin section configuration
     */
    public function __construct(SectionConfiguration $adminSectionConfig)
    {
        $this->adminSectionConfig = $adminSectionConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function override(Subject $subject, ?callable $output = null): void
    {
        foreach ($subject->getEntities() as $entity) {
            $this->overrideAdmin($entity);
        }
    }

    /**
     * @param string $entity Entity
     */
    private function overrideAdmin(string $entity): void
    {
        dump($entity);
    }
}
