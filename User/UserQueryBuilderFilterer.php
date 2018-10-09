<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\User;

use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User query builder filterer
 */
class UserQueryBuilderFilterer implements UserQueryBuilderFiltererInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $authenticationTokenStorage;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $authenticationTokenStorage Authentication token storage
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface        $authorizationChecker       Authorization checker
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                                      $extendedMetadataFactory    Extended metadata factory
     */
    public function __construct(
        TokenStorageInterface $authenticationTokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        MetadataFactoryInterface $extendedMetadataFactory
    ) {
        $this->authenticationTokenStorage = $authenticationTokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(QueryBuilder $qb, UserInterface $user = null)
    {
        if (!$this->isFilterable($qb)) {
            throw new \InvalidArgumentException('Query builder is not filterable.');
        }
        if (empty($user)) {
            $user = $this->getUser();

            if (empty($user)) {
                return;
            }
        }

        $userIds = $this->extendedMetadataFactory->getDoctrineMetadata($user)->getIdentifierValues($user);
        $userId = reset($userIds);

        if (empty($userId)) {
            throw new \InvalidArgumentException('User ID is empty.');
        }
        foreach (array_combine($qb->getRootAliases(), $qb->getRootEntities()) as $alias => $entity) {
            $meta = $this->extendedMetadataFactory->getExtendedMetadata($entity)['user'];

            if (empty($meta)) {
                continue;
            }

            $filter = false;

            foreach ($meta['roles'] as $role) {
                if ($this->authorizationChecker->isGranted($role)) {
                    $filter = true;

                    break;
                }
            }
            if (!$filter) {
                continue;
            }

            $key = $meta['property'].'_id';

            $qb
                ->innerJoin(sprintf('%s.%s', $alias, $meta['property']), $meta['property'])
                ->andWhere(sprintf('%s = :%s', $meta['property'], $key))
                ->setParameter($key, $userId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable(QueryBuilder $qb)
    {
        $entities = $qb->getRootEntities();

        if (empty($entities)) {
            return false;
        }
        foreach ($entities as $entity) {
            $extendedMeta = $this->extendedMetadataFactory->getExtendedMetadata($entity);

            if (!empty($extendedMeta['user'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    private function getUser()
    {
        $token = $this->authenticationTokenStorage->getToken();

        if (empty($token)) {
            return null;
        }

        $user = $token->getUser();

        return $user instanceof UserInterface ? $user : null;
    }
}
