<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Security\Authorization\Accessibility;

use Darvin\Utils\Security\Authorization\AuthorizationCheckerProviderInterface;
use Darvin\Utils\Security\Authorization\AuthorizationException;
use Darvin\Utils\Security\SecurableInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Securable accessibility checker
 */
class SecurableAccessibilityChecker implements AccessibilityCheckerInterface
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
     * {@inheritdoc}
     */
    public function isAccessible($object)
    {
        if (!is_object($object)) {
            throw new AuthorizationException(sprintf('Only objects can be checked, "%s" provided.', gettype($object)));
        }
        if (!$object instanceof SecurableInterface) {
            throw new AuthorizationException(sprintf('Class "%s" is not supported.', ClassUtils::getClass($object)));
        }

        $allowedRoles = $object->getAllowedRoles();

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
