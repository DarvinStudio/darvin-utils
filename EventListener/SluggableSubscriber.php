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
     * @param \Darvin\Utils\Sluggable\SluggableManagerInterface $sluggableManager Sluggable manager
     */
    public function __construct(SluggableManagerInterface $sluggableManager)
    {
        $this->sluggableManager = $sluggableManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        parent::onFlush($args);

        $generateSlugsCallback = array($this, 'generateSlugs');

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
        if (!$this->sluggableManager->isSluggable($entity)) {
            return;
        }
        if ($this->sluggableManager->generateSlugs($entity, AbstractOnFlushListener::OPERATION_UPDATE === $operation)) {
            $this->recomputeChangeSet($entity);
        }
    }
}
