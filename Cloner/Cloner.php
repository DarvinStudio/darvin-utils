<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Cloner;

use Darvin\Utils\Event\ClonableEvents;
use Darvin\Utils\Event\CloneEvent;
use Darvin\Utils\Mapping\Annotation\Clonable\Clonable;
use Darvin\Utils\Mapping\MappingException;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Cloner
 */
class Cloner implements ClonerInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $extendedMetadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var array|null
     */
    private $cloned;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher         Event dispatcher
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface              $extendedMetadataFactory Extended metadata factory
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor        Property accessor
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MetadataFactoryInterface $extendedMetadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->propertyAccessor = $propertyAccessor;

        $this->cloned = null;
    }

    /**
     * {@inheritdoc}
     */
    public function createClone($object)
    {
        $this->cloned = [];

        return $this->cloneObject($object, true);
    }

    /**
     * @param object $object          Object to clone
     * @param bool   $requireClonable Whether to require object to be clonable
     *
     * @return object
     * @throws \LogicException
     */
    private function cloneObject($object, $requireClonable)
    {
        $hash = spl_object_hash($object);

        if (isset($this->cloned[$hash])) {
            return $this->cloned[$hash];
        }

        $meta = [];
        $isClonable = $isEntity = false;

        $class = ClassUtils::getClass($object);

        try {
            $meta = $this->extendedMetadataFactory->getExtendedMetadata($class);
            $isClonable = isset($meta['clonable']);
            $isEntity = true;
        } catch (MappingException $ex) {
        }
        if (!$isClonable) {
            if ($requireClonable) {
                $message = sprintf(
                    'Class "%s" must be annotated with "%s" annotation in order to create clone of it\'s instance.',
                    $class,
                    Clonable::class
                );

                throw new \LogicException($message);
            }
            if ($isEntity) {
                return $object;
            }

            return $this->cloned[$hash] = clone $object;
        }

        $reflectionClass = $this->extendedMetadataFactory->getDoctrineMetadata($class)->getReflectionClass();

        $clone = $this->cloned[$hash] = $reflectionClass->newInstance();

        foreach ($meta['clonable']['properties'] as $property) {
            if (isset($meta['slugs'][$property])) {
                $this->setValue($clone, $reflectionClass, $property, null);

                continue;
            }

            $value = $this->getValue($object, $reflectionClass, $property);

            $valueCopy = $this->copyValue($value);

            $this->setValue($clone, $reflectionClass, $property, $valueCopy);
        }

        $event = new CloneEvent($object, $clone);
        $this->eventDispatcher->dispatch(ClonableEvents::CLONED, $event);

        return $event->getClone();
    }

    /**
     * @param object           $object          Object
     * @param \ReflectionClass $reflectionClass Reflection class
     * @param string           $property        Property
     *
     * @return mixed
     */
    private function getValue($object, \ReflectionClass $reflectionClass, $property)
    {
        try {
            return $this->propertyAccessor->getValue($object, $property);
        } catch (\Exception $ex) {
        }

        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param object           $object          Object
     * @param \ReflectionClass $reflectionClass Reflection class
     * @param string           $property        Property
     * @param mixed            $value           Value
     */
    private function setValue($object, \ReflectionClass $reflectionClass, $property, $value)
    {
        try {
            $this->propertyAccessor->setValue($object, $property, $value);

            return;
        } catch (\Exception $ex) {
        }

        $reflectionProperty = $reflectionClass->getProperty($property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @param mixed $value Value to copy
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function copyValue($value)
    {
        if (is_array($value)) {
            return $this->copyArray($value, true);
        }
        if (!is_object($value)) {
            return $value;
        }
        if ($value instanceof Collection) {
            return new ArrayCollection($this->copyArray($value->toArray(), false));
        }
        if ($value instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf('Traversable class "%s" is not supported.', ClassUtils::getClass($value)));
        }

        return $this->cloneObject($value, false);
    }

    /**
     * @param array $array         Array to copy
     * @param bool  $addNullValues Whether to add null values to array copy
     *
     * @return array
     */
    private function copyArray(array $array, $addNullValues)
    {
        $copy = [];

        foreach ($array as $key => $value) {
            $valueCopy = $this->copyValue($value);

            if (null !== $valueCopy || $addNullValues) {
                $copy[$key] = $valueCopy;
            }
        }

        return $copy;
    }
}
