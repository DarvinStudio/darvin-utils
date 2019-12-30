<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Config\Model;

/**
 * Subject
 */
class Subject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $entities;

    /**
     * @var string[]
     */
    private $templates;

    /**
     * @param string   $name      Subject name
     * @param string[] $entities  Entity classes
     * @param string[] $templates Templates
     */
    public function __construct(string $name, array $entities = [], array $templates = [])
    {
        $this->name = $name;
        $this->entities = $entities;
        $this->templates = $templates;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @return string[]
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
