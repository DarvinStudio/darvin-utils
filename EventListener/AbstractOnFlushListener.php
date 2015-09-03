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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

/**
 * Doctrine on flush event listener abstract implementation
 */
abstract class AbstractOnFlushListener
{
    const OPERATION_DELETE = 'delete';
    const OPERATION_INSERT = 'insert';
    const OPERATION_UPDATE = 'update';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     */
    protected $uow;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->init($args->getEntityManager(), $args->getEntityManager()->getUnitOfWork());
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em  Entity manager
     * @param \Doctrine\ORM\UnitOfWork    $uow Unit of work
     */
    protected function init(EntityManager $em, UnitOfWork $uow)
    {
        if ($this->initialized) {
            return;
        }

        $this->em = $em;
        $this->uow = $uow;

        $this->initialized = true;
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
     * @param callable $callback    Callback
     * @param string   $entityClass Entity class filter
     *
     * @return AbstractOnFlushListener
     */
    protected function onDelete(callable $callback, $entityClass = null)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityDeletions(), self::OPERATION_DELETE, $callback, $entityClass);
    }

    /**
     * @param callable $callback    Callback
     * @param string   $entityClass Entity class filter
     *
     * @return AbstractOnFlushListener
     */
    protected function onInsert(callable $callback, $entityClass = null)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityInsertions(), self::OPERATION_INSERT, $callback, $entityClass);
    }

    /**
     * @param callable $callback    Callback
     * @param string   $entityClass Entity class filter
     *
     * @return AbstractOnFlushListener
     */
    protected function onUpdate(callable $callback, $entityClass = null)
    {
        $this->checkIfInitialized();

        return $this->processEntities($this->uow->getScheduledEntityUpdates(), self::OPERATION_UPDATE, $callback, $entityClass);
    }

    /**
     * @param array    $entities    Entities to process
     * @param string   $operation   Operation type
     * @param callable $callback    Callback
     * @param string   $entityClass Entity class filter
     *
     * @return AbstractOnFlushListener
     */
    private function processEntities($entities, $operation, callable $callback, $entityClass = null)
    {
        $filterByEntityClass = !empty($entityClass);

        foreach ($entities as $entity) {
            if (!$filterByEntityClass || $entity instanceof $entityClass) {
                $callback($entity, $operation);
            }
        }

        return $this;
    }

    /**
     * @throws \Darvin\Utils\EventListener\EventListenerException
     */
    private function checkIfInitialized()
    {
        if (!$this->initialized) {
            throw new EventListenerException(sprintf('You forgot to call "%s::init()".', __CLASS__));
        }
    }
}
