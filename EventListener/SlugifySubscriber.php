<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Darvin\Utils\Service\ServiceProviderInterface;
use Darvin\Utils\Sluggable\SluggableManagerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Slugify event subscriber
 */
class SlugifySubscriber implements EventSubscriber, SlugifySubscriberInterface
{
    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $entityManagerProvider;

    /**
     * @var \Darvin\Utils\Sluggable\SluggableManagerInterface
     */
    private $sluggableManager;

    /**
     * @var array
     */
    private $entityBlacklist;

    /**
     * @param \Darvin\Utils\Service\ServiceProviderInterface    $entityManagerProvider Entity manager service provider
     * @param \Darvin\Utils\Sluggable\SluggableManagerInterface $sluggableManager      Sluggable manager
     */
    public function __construct(ServiceProviderInterface $entityManagerProvider, SluggableManagerInterface $sluggableManager)
    {
        $this->entityManagerProvider = $entityManagerProvider;
        $this->sluggableManager = $sluggableManager;

        $this->entityBlacklist = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * @param object $entity Entity
     */
    public function blacklistEntity(object $entity): void
    {
        $hash = $this->hashEntity($this->getEntityManager(), $entity);

        $this->entityBlacklist[$hash] = $hash;
    }

    /**
     * {@inheritDoc}
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->slugify($em, $entity);
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->slugify($em, $entity, true);
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em                  Entity manager
     * @param object                      $entity              Entity
     * @param bool                        $dispatchUpdateEvent Whether to dispatch update event
     */
    private function slugify(EntityManager $em, object $entity, bool $dispatchUpdateEvent = false): void
    {
        if (isset($this->entityBlacklist[$this->hashEntity($em, $entity)]) || !$this->sluggableManager->isSluggable($entity)) {
            return;
        }
        if ($this->sluggableManager->generateSlugs($entity, $dispatchUpdateEvent)) {
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(ClassUtils::getClass($entity)), $entity);
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em     Entity manager
     * @param object                      $entity Entity
     *
     * @return string
     */
    private function hashEntity(EntityManager $em, object $entity): string
    {
        $class = ClassUtils::getClass($entity);

        $ids = $em->getClassMetadata($class)->getIdentifierValues($entity);

        return $class.spl_object_hash($entity).reset($ids);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager(): EntityManager
    {
        return $this->entityManagerProvider->getService();
    }
}
