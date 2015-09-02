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

use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Darvin\Utils\Slug\SlugException;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Slug event subscriber
 */
class SlugSubscriber extends AbstractOnFlushListener implements EventSubscriber
{
    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $metadataFactory  Metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
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

        $updateSlugsCallback = array($this, 'updateSlugs');

        $this
            ->onInsert($updateSlugsCallback)
            ->onUpdate($updateSlugsCallback);
    }

    /**
     * @param object $entity Entity
     *
     * @throws \Darvin\Utils\Slug\SlugException
     */
    protected function updateSlugs($entity)
    {
        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->metadataFactory->getMetadata($this->em->getClassMetadata($entityClass));

        if (!isset($meta['slugs']) || empty($meta['slugs'])) {
            return;
        }

        $recomputeChangeSet = false;

        foreach ($meta['slugs'] as $slugProperty => $params) {
            if (!$this->propertyAccessor->isReadable($entity, $slugProperty)) {
                throw new SlugException(sprintf('Property "%s::$%s" is not readable.', $entityClass, $slugProperty));
            }

            $oldSlug = $this->propertyAccessor->getValue($entity, $slugProperty);

            $slugParts = array();

            foreach ($params['sourcePropertyPaths'] as $propertyPath) {
                try {
                    $slugParts[] = $this->propertyAccessor->getValue($entity, $propertyPath);
                } catch (UnexpectedTypeException $ex) {
                }
            }

            $slug = implode($params['separator'], $slugParts);

            if ($slug == $oldSlug) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($entity, $slugProperty)) {
                throw new SlugException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $slugProperty));
            }

            $this->propertyAccessor->setValue($entity, $slugProperty, $slug);

            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $this->recomputeChangeSet($entity);
        }
    }
}
