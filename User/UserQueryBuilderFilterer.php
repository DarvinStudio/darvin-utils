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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User query builder filterer
 */
class UserQueryBuilderFilterer implements UserQueryBuilderFiltererInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $authTokenStorage;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $authTokenStorage        Authentication token storage
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface                                      $extendedMetadataFactory Extended metadata factory
     */
    public function __construct(TokenStorageInterface $authTokenStorage, MetadataFactoryInterface $extendedMetadataFactory)
    {
        $this->authTokenStorage = $authTokenStorage;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(QueryBuilder $qb, UserInterface $user = null)
    {
        if (!$this->isFilterable($qb)) {
            throw new UserException('Query builder is not filterable.');
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
            throw new UserException('User ID is empty.');
        }
        foreach (array_combine($qb->getRootAliases(), $qb->getRootEntities()) as $alias => $entity) {
            $userProperty = $this->extendedMetadataFactory->getExtendedMetadata($entity)['user'];

            if (empty($userProperty)) {
                continue;
            }

            $key = $userProperty.'_id';

            $qb
                ->andWhere(sprintf('%s.%s = :%s', $alias, $userProperty, $key))
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
        $token = $this->authTokenStorage->getToken();

        if (empty($token)) {
            return null;
        }

        $user = $token->getUser();

        return $user instanceof UserInterface ? $user : null;
    }
}
