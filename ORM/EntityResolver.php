<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ORM;

/**
 * Entity resolver
 */
class EntityResolver implements EntityResolverInterface
{
    /**
     * @var array
     */
    private $replacements;

    /**
     * Entity resolver constructor.
     */
    public function __construct()
    {
        $this->replacements = [];
    }

    /**
     * @param string $entity      Entity class or interface
     * @param string $replacement Replacement class
     */
    public function addReplacement(string $entity, string $replacement)
    {
        $this->replacements[$entity] = $replacement;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $entity): string
    {
        return $this->replacements[$entity] ?? $entity;
    }
}
