<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Doctrine\ORM;

/**
 * Entity manager provider
 */
interface EntityManagerProviderInterface
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager();
}
