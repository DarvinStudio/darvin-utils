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
    public function createView($data, ?string $name = null, ?string $transDomain = null): ?DataView
    {
        $view = $this->buildView($data, $this->trimName($name), $transDomain);

        if (!$this->isEmpty($view)) {
            return $view;
        }

        return null;
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
        $view = new DataView($parent);

        if (!is_iterable($data)) {
            $value = $this->stringifier->stringify($data);

            if ('' === $value) {
                $value = null;
            }
            if (null !== $value) {
                $url = $this->buildUrl($value, $name);

                if (null === $url) {
                    $value = $this->translate($value, $transDomain);
                }

                $view->setUrl($url);
            }

            $view->setValue($value);
        } else {
            if (!is_array($data)) {
                $data = iterator_to_array($data);
            }

            $view->setAssociative(array_keys($data) !== range(0, count($data) - 1));

            foreach ($data as $key => $value) {
                $child = $this->buildView($value, $this->nameChild((string)$key, $view, $name), $transDomain, $view);

                if (!$this->isEmpty($child)) {
                    $view->addChild($child);
                }
            }
        }

        $view->setTitle($this->translate($this->buildTitle($view, $name), $transDomain));

        return $view;
    }

    /**
     * @param \Darvin\Utils\Data\View\Model\DataView $view View
     * @param string|null                            $name Name
     *
     * @return string|null
     */
    private function buildTitle(DataView $view, ?string $name): ?string
    {
        if (null === $name || !$view->hasParent() || !$view->getParent()->isAssociative()) {
            return null;
        }
        if (!$view->hasChildren()) {
            return $name;
        }

        return implode('.', [$name, 'title']);
    }

    /**
     * @param string      $value Value
     * @param string|null $name  Name
     *
     * @return string|null
     */
    private function buildUrl(string $value, ?string $name): ?string
    {
        if (false !== strpos($value, '://')) {
            return $value;
        }

        $suffix = preg_replace('/^.*\./', '', $name);

        switch ($suffix) {
            case 'email':
                return sprintf('mailto:%s', $value);
            case 'phone':
                return sprintf('tel:%s', $value);
            default:
                return null;
        }
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
        if ($this->isTranslationId($key)) {
            return $key;
        }

        $parts = [];

        if (null !== $parentName) {
            $parts[] = $parentName;
        }
        if ($parent->isAssociative()) {
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
     * @return bool
     */
    private function isEmpty(DataView $view): bool
    {
        return !$view->hasChildren() && !$view->hasValue();
    }

    /**
     * @param string $text Text
     *
     * @return bool
     */
    private function isTranslationId(string $text): bool
    {
        return false !== strpos($text, '.');
    }

    /**
     * @param string|null $id     Translation ID
     * @param string|null $domain Translation domain
     *
     * @return string|null
     */
    private function translate(?string $id, ?string $domain): ?string
    {
        if (null === $id) {
            return null;
        }
        if (null === $domain) {
            return $id;
        }

        return $this->translator->trans($id, [], $domain);
    }

    /**
     * @param string|null $name Name
     *
     * @return string|null
     */
    private function trimName(?string $name): ?string
    {
        if (null === $name) {
            return null;
        }

        return rtrim($name, '.');
    }
}
