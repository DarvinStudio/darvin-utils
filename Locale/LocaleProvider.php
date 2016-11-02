<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Locale;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Locale provider
 */
class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack  Request stack
     * @param string                                         $defaultLocale Default locale
     */
    public function __construct(RequestStack $requestStack, $defaultLocale)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale()
    {
        $request = $this->requestStack->getCurrentRequest();

        return !empty($request) ? $request->getLocale() : $this->defaultLocale;
    }
}
