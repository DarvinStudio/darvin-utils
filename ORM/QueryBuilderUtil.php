<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ORM;

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
    public static function findJoinByAlias(QueryBuilder $qb, $rootAlias, $joinAlias)
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
