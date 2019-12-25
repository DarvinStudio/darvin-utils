<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Entity to ID form data transformer
 */
class EntityToIDTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var bool
     */
    private $multiple;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em          Entity manager
     * @param string                               $entityClass Entity class
     * @param bool                                 $multiple    Is multiple mode enabled
     */
    public function __construct(EntityManagerInterface $em, string $entityClass, bool $multiple)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
        $this->multiple = $multiple;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        if (!$this->multiple) {
            if (null === $value) {
                return null;
            }

            $ids = $this->em->getClassMetadata($this->entityClass)->getIdentifierValues($value);

            return reset($ids);
        }

        $transformed = [];

        if (null !== $value) {
            $meta = $this->em->getClassMetadata($this->entityClass);

            foreach ($value as $entity) {
                $ids = $meta->getIdentifierValues($entity);

                $transformed[] = reset($ids);
            }
        }

        return $transformed;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value): ?object
    {
        if (!$this->multiple) {
            if (null === $value) {
                return null;
            }

            return $this->em->find($this->entityClass, $value);
        }
        if (null === $value) {
            return new ArrayCollection();
        }

        $identifier = $this->em->getClassMetadata($this->entityClass)->getIdentifier();

        return new ArrayCollection($this->em->getRepository($this->entityClass)->findBy([reset($identifier) => $value]));
    }
}
