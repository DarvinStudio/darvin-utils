<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Doctrine\ORM;

use Darvin\Utils\Service\ServiceProviderInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Entity utility
 */
class EntityUtil
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $entityManagerProvider;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface $entityManagerProvider Entity manager service provider
     */
    public function __construct(ServiceProviderInterface $entityManagerProvider)
    {
        $this->entityManagerProvider = $entityManagerProvider;
    }

    /**
     * @param object $entity Entity
     *
     * @return bool
     */
    public function isDeleted($entity)
    {
        $em = $this->getEntityManager();

        $class = ClassUtils::getClass($entity);

        $meta = $em->getClassMetadata($class);

        $ids = $meta->getIdentifierValues($entity);
        $id  = reset($ids);

        foreach ($em->getUnitOfWork()->getScheduledEntityDeletions() as $deleted) {
            if (ClassUtils::getClass($deleted) === $class && in_array($id, $meta->getIdentifierValues($deleted))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->entityManagerProvider->getService();
    }
}
