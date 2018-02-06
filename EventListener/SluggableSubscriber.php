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

use Darvin\Utils\Service\ServiceProviderInterface;
use Darvin\Utils\Sluggable\SluggableManagerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

/**
 * Sluggable event subscriber
 */
class SluggableSubscriber extends AbstractOnFlushListener implements EventSubscriber
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
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * @param object $entity Entity
     */
    public function blacklistEntity($entity)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->entityManagerProvider->getService();
        $this->init($em, $em->getUnitOfWork());

        $hash = $this->hashEntity($entity);

        $this->entityBlacklist[$hash] = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        parent::onFlush($args);

        $generateSlugsCallback = [$this, 'generateSlugs'];

        $this
            ->onInsert($generateSlugsCallback)
            ->onUpdate($generateSlugsCallback);
    }

    /**
     * @param object $entity    Entity
     * @param string $operation Operation type
     */
    protected function generateSlugs($entity, $operation)
    {
        if (isset($this->entityBlacklist[$this->hashEntity($entity)]) || !$this->sluggableManager->isSluggable($entity)) {
            return;
        }
        if ($this->sluggableManager->generateSlugs($entity, AbstractOnFlushListener::OPERATION_UPDATE === $operation)) {
            $this->recomputeChangeSet($entity);
        }
    }

    /**
     * @param object $entity Entity
     *
     * @return string
     */
    private function hashEntity($entity)
    {
        $class = ClassUtils::getClass($entity);

        $ids = $this->em->getClassMetadata($class)->getIdentifierValues($entity);

        return $class.spl_object_hash($entity).reset($ids);
    }
}
