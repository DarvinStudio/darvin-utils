<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Darvin\Utils\Transliteratable\TransliteratorInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Transliterate event subscriber
 */
class TransliterateSubscriber implements EventSubscriber
{
    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\Utils\Transliteratable\TransliteratorInterface
     */
    private $transliterator;

    /**
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $extendedMetadataFactory Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor        Property accessor
     * @param \Darvin\Utils\Transliteratable\TransliteratorInterface      $transliterator          Transliterator
     */
    public function __construct(
        MetadataFactoryInterface $extendedMetadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        TransliteratorInterface $transliterator
    ) {
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->transliterator = $transliterator;
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
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach (array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()) as $entity) {
            $this->transliterate($em, $entity);
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em     Entity manager
     * @param object                      $entity Entity
     *
     * @throws \RuntimeException
     */
    private function transliterate(EntityManager $em, $entity)
    {
        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

        if (!isset($meta['transliteratable']) || empty($meta['transliteratable'])) {
            return;
        }

        $changeSet = $em->getUnitOfWork()->getEntityChangeSet($entity);

        $recomputeChangeSet = false;

        foreach ($meta['transliteratable'] as $property => $params) {
            if (!isset($changeSet[$property])) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($entity, $property)) {
                throw new \RuntimeException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $property));
            }

            $transliterated = $this->transliterator->transliterate(
                $changeSet[$property][1],
                $params['sanitize'],
                $params['allowedSymbols'],
                $params['separator']
            );
            $this->propertyAccessor->setValue($entity, $property, $transliterated);

            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata($entityClass), $entity);
        }
    }
}
