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
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\Utils\Strings\Stringifier\StringifierInterface $stringifier Stringifier
     * @param \Symfony\Contracts\Translation\TranslatorInterface     $translator  Translator
     */
    public function __construct(StringifierInterface $stringifier, TranslatorInterface $translator)
    {
        $this->stringifier = $stringifier;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function createView($data, ?string $name = null, ?string $transDomain = null): DataView
    {
        return $this->buildView($data, $name, $transDomain);
    }

    /**
     * @param mixed                                       $data        Data
     * @param string|null                                 $name        Name
     * @param string|null                                 $transDomain Translation domain
     * @param \Darvin\Utils\Data\View\Model\DataView|null $parent      Parent
     *
     * @return \Darvin\Utils\Data\View\Model\DataView
     */
    private function buildView($data, ?string $name, ?string $transDomain, ?DataView $parent = null): DataView
    {
        $name = $this->prepareName($name);
        $view = new DataView($parent);

        if (!is_iterable($data)) {
            $view->setValue($this->stringifier->stringify($data));
        } else {
            if (!is_array($data)) {
                $data = iterator_to_array($data);
            }

            $view->setAssociated(array_keys($data) !== range(0, count($data) - 1));

            foreach ($data as $key => $value) {
                $view->addChild($this->buildView($value, $this->nameChild((string)$key, $view, $name), $transDomain, $view));
            }
        }

        $title = $this->buildTitle($view, $name);

        if (null !== $title && null !== $transDomain) {
            $title = $this->translator->trans($title, [], $transDomain);
        }

        $view->setTitle($title);

        return $view;
    }

    /**
     * @param string                                 $key        Child key
     * @param \Darvin\Utils\Data\View\Model\DataView $parent     Parent
     * @param string|null                            $parentName Parent name
     *
     * @return string
     */
    private function nameChild(string $key, DataView $parent, ?string $parentName): string
    {
        $parts = [];

        if (null !== $parentName) {
            $parts[] = $parentName;
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
     * @param string|null                            $name Name
     *
     * @return string|null
     */
    private function buildTitle(DataView $view, ?string $name): ?string
    {
        if (null === $name || !$view->hasParent() || !$view->getParent()->isAssociated()) {
            return null;
        }
        if (!$view->hasChildren()) {
            return $name;
        }

        return implode('.', [$name, 'title']);
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
