<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2020, Darvin Studio
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
     * @param string                     $join      Join
     * @param string|null                $joinAlias Join alias
     * @param bool                       $inner     Whether to use inner join
     *
     * @return \Doctrine\ORM\Query\Expr\Join
     */
    public static function findOrCreateJoin(QueryBuilder $qb, string $join, ?string $joinAlias = null, bool $inner = true): Join
    {
        $rootAliases = $qb->getRootAliases();

        $rootAlias = reset($rootAliases);

        $canonicalJoin = false !== strpos($join, '.') ? $join : implode('.', [$rootAlias, $join]);

        $dqlParts = $qb->getDQLParts();

        if (isset($dqlParts['join'][$rootAlias])) {
            /** @var \Doctrine\ORM\Query\Expr\Join $existingJoin */
            foreach ($dqlParts['join'][$rootAlias] as $existingJoin) {
                if ($canonicalJoin === $existingJoin->getJoin()) {
                    return $existingJoin;
                }
            }
        }
        if (null === $joinAlias) {
            $joinAlias = str_replace('.', '_', $join);
        }

        $index           = 0;
        $uniqueJoinAlias = $joinAlias;

        while (null !== self::findJoinByAlias($qb, $rootAlias, $uniqueJoinAlias)) {
            $uniqueJoinAlias = implode('_', [$joinAlias, ++$index]);
        }

        $inner
            ? $qb->innerJoin($canonicalJoin, $uniqueJoinAlias)
            : $qb->leftJoin($canonicalJoin, $uniqueJoinAlias);

        return self::findJoinByAlias($qb, $rootAlias, $uniqueJoinAlias);
    }

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
}
