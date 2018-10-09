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

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User query builder filterer
 */
interface UserQueryBuilderFiltererInterface
{
    /**
     * @param \Doctrine\ORM\QueryBuilder                          $qb   Query builder
     * @param \Symfony\Component\Security\Core\User\UserInterface $user User
     *
     * @throws \InvalidArgumentException
     */
    public function filter(QueryBuilder $qb, UserInterface $user = null);

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return bool
     */
    public function isFilterable(QueryBuilder $qb);
}
