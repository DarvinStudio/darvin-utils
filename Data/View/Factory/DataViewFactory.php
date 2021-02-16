<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2021, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Data\View\Factory;

use Darvin\Utils\Data\View\Model\DataView;
use Darvin\Utils\Strings\Stringifier\StringifierInterface;

/**
 * Data view factory
 */
class DataViewFactory implements DataViewFactoryInterface
{
    /**
     * @var \Darvin\Utils\Strings\Stringifier\StringifierInterface
     */
    private $stringifier;

    /**
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     */
    public function __construct(StringifierInterface $stringifier)
    {
        $this->stringifier = $stringifier;
    }

    /**
     * {@inheritDoc}
     */
    public function createView($data, ?string $name = null): DataView
    {
        return $this->buildView($data, $name);
    }

    /**
     * @param mixed                                       $data   Data
     * @param string|null                                 $name   Name
     * @param \Darvin\Utils\Data\View\Model\DataView|null $parent Parent
     *
     * @return \Darvin\Utils\Data\View\Model\DataView
     */
    private function buildView($data, ?string $name, ?DataView $parent = null): DataView
    {
        $view = new DataView($this->prepareName($name), $parent);

        if (!is_iterable($data)) {
            $view->setValue($this->stringifier->stringify($data));
        } else {
            if (!is_array($data)) {
                $data = iterator_to_array($data);
            }

            $view->setAssociated(array_keys($data) !== range(0, count($data) - 1));

            foreach ($data as $key => $value) {
                $view->addChild($this->buildView($value, $this->nameChild((string)$key, $view), $view));
            }
        }

        $view->setTitle($this->buildViewTitle($view));

        return $view;
    }

    /**
     * @param string                                 $key    Child key
     * @param \Darvin\Utils\Data\View\Model\DataView $parent Parent
     *
     * @return string
     */
    private function nameChild(string $key, DataView $parent): string
    {
        $parts = [];

        if (null !== $parent->getName()) {
            $parts[] = $parent->getName();
        }
        if ($parent->isAssociated()) {
            if ($parent->hasParent()) {
                $parts[] = 'item';
            }

            $parts[] = $key;
        }

        return implode('.', $parts);
    }

    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view View
     *
     * @return string|null
     */
    private function buildViewTitle(DataView $view): ?string
    {
        if (null === $view->getName() || !$view->hasParent() || !$view->getParent()->isAssociated()) {
            return null;
        }
        if (!$view->hasChildren()) {
            return $view->getName();
        }

        return implode('.', [$view->getName(), 'title']);
    }

    /**
     * @param string|null $name Name
     *
     * @return string|null
     */
    private function prepareName(?string $name): ?string
    {
        if (null === $name) {
            return null;
        }

        return rtrim($name, '.');
    }
}
