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
     * @var \Darvin\Utils\Sluggable\SluggableManagerInterface
     */
    private $sluggableManager;

    /**
     * @var array
     */
    private $entityBlacklist;

    /**
     * @param \Darvin\Utils\Sluggable\SluggableManagerInterface $sluggableManager Sluggable manager
     */
    public function __construct(SluggableManagerInterface $sluggableManager)
    {
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
        $class = ClassUtils::getClass($entity);
        $hash  = spl_object_hash($entity);

        if (!isset($this->entityBlacklist[$class])) {
            $this->entityBlacklist[$class] = [];
        }

        $this->entityBlacklist[$class][$hash] = $hash;
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
        if (isset($this->entityBlacklist[ClassUtils::getClass($entity)][spl_object_hash($entity)])
            || !$this->sluggableManager->isSluggable($entity)
        ) {
            return;
        }
        if ($this->sluggableManager->generateSlugs($entity, AbstractOnFlushListener::OPERATION_UPDATE === $operation)) {
            $this->recomputeChangeSet($entity);
        }
    }
}
