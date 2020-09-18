<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ORM;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Query builder utility
 */
class QueryBuilderUtil
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param string                     $rootAlias Root alias
     * @param string                     $joinAlias Join alias
     *
     * @return \Doctrine\ORM\Query\Expr\Join
     */
    public static function findJoinByAlias(QueryBuilder $qb, string $rootAlias, string $joinAlias): ?Join
    {
        $dqlParts = $qb->getDQLParts();

        if (!isset($dqlParts['join'][$rootAlias])) {
            return null;
        }
        /** @var \Doctrine\ORM\Query\Expr\Join $join */
        foreach ($dqlParts['join'][$rootAlias] as $join) {
            if ($joinAlias === $join->getAlias()) {
                return $join;
            }
        }

        return null;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param string                     $rootAlias Root alias
     * @param string                     $joinName  Join name
     *
     * @return \Doctrine\ORM\Query\Expr\Join
     */
    public static function findJoinByName(QueryBuilder $qb, string $rootAlias, string $joinName): ?Join
    {
        $dqlParts = $qb->getDQLParts();

        if (!isset($dqlParts['join'][$rootAlias])) {
            return null;
        }
        if (false === strpos($joinName, '.')) {
            $joinName = implode('.', [$rootAlias, $joinName]);
        }
        /** @var \Doctrine\ORM\Query\Expr\Join $join */
        foreach ($dqlParts['join'][$rootAlias] as $join) {
            if ($joinName === $join->getJoin()) {
                return $join;
            }
        }

        return null;
    }
}
