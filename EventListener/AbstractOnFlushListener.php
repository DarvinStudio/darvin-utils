<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Doctrine on flush event listener abstract implementation
 */
abstract class AbstractOnFlushListener
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     */
    private $uow;

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();
    }

    /**
     * @param object $entity Entity to recompute change set
     */
    protected function recomputeChangeSet($entity)
    {
        $this->checkIfInitialized();

        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(ClassUtils::getClass($entity)), $entity);
    }

    /**
     * @param string   $entityClass Entity class filter
     * @param callable $callback    Callback
     *
     * @return AbstractOnFlushListener
     */
    protected function onDelete($entityClass, callable $callback)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityDeletions(), $entityClass, $callback);
    }

    /**
     * @param string   $entityClass Entity class filter
     * @param callable $callback    Callback
     *
     * @return AbstractOnFlushListener
     */
    protected function onInsert($entityClass, callable $callback)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityInsertions(), $entityClass, $callback);
    }

    /**
     * @param string   $entityClass Entity class filter
     * @param callable $callback    Callback
     *
     * @return AbstractOnFlushListener
     */
    protected function onUpdate($entityClass, callable $callback)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityUpdates(), $entityClass, $callback);
    }

    /**
     * @param array    $entities    Entities to process
     * @param string   $entityClass Entity class filter
     * @param callable $callback    Callback
     *
     * @return AbstractOnFlushListener
     */
    private function processEntities($entities, $entityClass, callable $callback)
    {
        foreach ($entities as $entity) {
            if ($entity instanceof $entityClass) {
                $callback($entity);
            }
        }

        return $this;
    }

    /**
     * @throws \Darvin\Utils\EventListener\EventListenerException
     */
    private function checkIfInitialized()
    {
        if (empty($this->em) || empty($this->uow)) {
            throw new EventListenerException('You forgot to call parent::onFlush().');
        }
    }
}
