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

use Darvin\Utils\Mapping\Annotation\Clonable\Clonable;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Cloner
 */
class Cloner implements ClonerInterface
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
     * @var array
     */
    private $cloneObjects;

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
    public function createClone($object)
    {
        $this->cloneObjects = array();

        return $this->cloneObject($object);
    }

    /**
     * @param object $object Object to clone
     *
     * @return object
     * @throws \Darvin\Utils\Cloner\ClonerException
     */
    private function cloneObject($object)
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->cloneObjects[$objectHash])) {
            return $this->cloneObjects[$objectHash];
        }

        $class = ClassUtils::getClass($object);

        if (false === strpos($class, '\\')) {
            $clone = clone $object;

            $this->cloneObjects[$objectHash] = $clone;

            return $clone;
        }

        $meta = $this->metadataFactory->getMetadata($class);

        if (!isset($meta['clonable'])) {
            $message = sprintf(
                'Class "%s" must be annotated with "%s" annotation in order to create clone of it\'s instance.',
                $class,
                Clonable::ANNOTATION
            );

            throw new ClonerException($message);
        }

        $clone = new $class();

        $this->cloneObjects[$objectHash] = $clone;

        foreach ($meta['clonable']['properties'] as $property) {
            if (!$this->propertyAccessor->isReadable($object, $property)) {
                throw new ClonerException(sprintf('Property "%s::$%s" is not readable.', $class, $property));
            }
            if (!$this->propertyAccessor->isWritable($clone, $property)) {
                throw new ClonerException(sprintf('Property "%s::$%s" is not writable.', $class, $property));
            }

            $value = $this->propertyAccessor->getValue($object, $property);

            $valueCopy = $this->copyValue($value);

            $this->propertyAccessor->setValue($clone, $property, $valueCopy);
        }

        return $clone;
    }

    /**
     * @param mixed $value Value to copy
     *
     * @return mixed
     * @throws \Darvin\Utils\Cloner\ClonerException
     */
    private function copyValue($value)
    {
        if (is_array($value)) {
            return $this->copyArray($value);
        }
        if (!is_object($value)) {
            return $value;
        }
        if ($value instanceof Collection) {
            return new ArrayCollection($this->copyArray($value->toArray()));
        }
        if ($value instanceof \Traversable) {
            throw new ClonerException(sprintf('Traversable class "%s" is not supported.', ClassUtils::getClass($value)));
        }

        return $this->cloneObject($value);
    }

    /**
     * @param array $array Array to copy
     *
     * @return array
     */
    private function copyArray(array $array)
    {
        $copy = array();

        foreach ($array as $key => $value) {
            $copy[$key] = $this->copyValue($value);
        }

        return $copy;
    }
}
