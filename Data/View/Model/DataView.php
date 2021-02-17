<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Data\View\Model;

/**
 * Data view
 */
class DataView
{
    /**
     * @var DataView|null
     */
    private $parent;

    /**
     * @var DataView[]
     */
    private $children;

    /**
     * @var bool
     */
    private $associated;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @param DataView|null $parent Parent
     */
    public function __construct(?DataView $parent)
    {
        $this->parent = $parent;

        $this->children = [];
        $this->associated = false;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    /**
     * @return DataView|null
     */
    public function getParent(): ?DataView
    {
        return $this->parent;
    }

    /**
     * @return DataView[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param DataView $child Child
     */
    public function addChild(DataView $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return bool
     */
    public function isAssociated(): bool
    {
        return $this->associated;
    }

    /**
     * @param bool $associated associated
     */
    public function setAssociated(bool $associated): void
    {
        $this->associated = $associated;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
