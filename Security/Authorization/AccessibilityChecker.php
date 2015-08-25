<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Security\Authorization;

use Darvin\Utils\Security\SecurableInterface;

/**
 * Accessibility checker
 */
class AccessibilityChecker
{
    /**
     * @var \Darvin\Utils\Security\Authorization\AuthorizationCheckerProviderInterface
     */
    private $authorizationCheckerProvider;

    /**
     * @param \Darvin\Utils\Security\Authorization\AuthorizationCheckerProviderInterface $authorizationCheckerProvider Authorization checker provider
     */
    public function __construct(AuthorizationCheckerProviderInterface $authorizationCheckerProvider)
    {
        $this->authorizationCheckerProvider = $authorizationCheckerProvider;
    }

    /**
     * @param \Darvin\Utils\Security\SecurableInterface $securable Securable
     *
     * @return bool
     */
    public function isAccessible(SecurableInterface $securable)
    {
        $allowedRoles = $securable->getAllowedRoles();

        if (empty($allowedRoles)) {
            return true;
        }

        $authorizationChecker = $this->authorizationCheckerProvider->getAuthorizationChecker();

        foreach ($allowedRoles as $allowedRole) {
            if ($authorizationChecker->isGranted($allowedRole)) {
                return true;
            }
        }

        return false;
    }
}
