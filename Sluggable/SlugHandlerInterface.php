<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Sluggable;

use Doctrine\ORM\EntityManager;

/**
 * Slug handler
 */
interface SlugHandlerInterface
{
    /**
     * @param string                      $slug   Slug
     * @param string                      $suffix Slug suffix
     * @param object                      $entity Entity
     * @param \Doctrine\ORM\EntityManager $em     Entity manager
     */
    public function handle(string &$slug, string &$suffix, object $entity, EntityManager $em): void;
}
