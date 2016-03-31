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
use Darvin\Utils\Transliteratable\TransliteratableException;
use Darvin\Utils\Transliteratable\TransliteratorInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Transliteratable event subscriber
 */
class TransliteratableSubscriber extends AbstractOnFlushListener implements EventSubscriber
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

        $transliterateCallback = array($this, 'transliterate');

        $this
            ->onInsert($transliterateCallback)
            ->onUpdate($transliterateCallback);
    }

    /**
     * @param object $entity Entity
     *
     * @throws \Darvin\Utils\Transliteratable\TransliteratableException
     */
    protected function transliterate($entity)
    {
        $entityClass = ClassUtils::getClass($entity);

        $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

        if (!isset($meta['transliteratable']) || empty($meta['transliteratable'])) {
            return;
        }

        $changeSet = $this->uow->getEntityChangeSet($entity);

        $recomputeChangeSet = false;

        foreach ($meta['transliteratable'] as $property => $params) {
            if (!isset($changeSet[$property])) {
                continue;
            }
            if (!$this->propertyAccessor->isWritable($entity, $property)) {
                throw new TransliteratableException(sprintf('Property "%s::$%s" is not writable.', $entityClass, $property));
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
            $this->recomputeChangeSet($entity);
        }
    }
}
