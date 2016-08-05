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
use Darvin\Utils\Service\ServiceProviderInterface;

/**
 * Accessibility checker
 */
class AccessibilityChecker
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $authorizationCheckerProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $authorizationCheckerProvider Authorization checker provider
     */
    public function __construct(ServiceProviderInterface $authorizationCheckerProvider)
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

        $authorizationChecker = $this->getAuthorizationChecker();

        foreach ($allowedRoles as $allowedRole) {
            if ($authorizationChecker->isGranted($allowedRole)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private function getAuthorizationChecker()
    {
        return $this->authorizationCheckerProvider->getService();
    }
}
