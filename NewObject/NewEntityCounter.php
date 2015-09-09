<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\NewObject;

use Darvin\Utils\Mapping\Annotation\NewObjectFlag;
use Darvin\Utils\Mapping\MetadataFactoryInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

/**
 * New entity counter
 */
class NewEntityCounter implements NewObjectCounterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Mapping\MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var array
     */
    private $counts;

    /**
     * @param \Doctrine\ORM\EntityManager                    $em              Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface $metadataFactory Metadata factory
     */
    public function __construct(EntityManager $em, MetadataFactoryInterface $metadataFactory)
    {
        $this->em = $em;
        $this->metadataFactory = $metadataFactory;
        $this->counts = array();
    }

    /**
     * {@inheritdoc}
     */
    public function count($objectClass)
    {
        if (!isset($this->counts[$objectClass])) {
            $newObjectFlag = $this->getNewObjectFlag($objectClass);

            if (empty($newObjectFlag)) {
                $message = sprintf(
                    'Class "%s" must be annotated with "%s" annotation in order to count new objects.',
                    $objectClass,
                    NewObjectFlag::ANNOTATION
                );

                throw new NewObjectException($message);
            }

            $this->counts[$objectClass] = (int) $this->em->getRepository($objectClass)->createQueryBuilder('o')
                ->select('COUNT(o)')
                ->where(sprintf('o.%s = :%1$s', $newObjectFlag))
                ->setParameter($newObjectFlag, true)
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->counts[$objectClass];
    }

    /**
     * {@inheritdoc}
     */
    public function isCountable($objectClass)
    {
        if (isset($this->counts[$objectClass])) {
            return true;
        }

        $newObjectFlag = $this->getNewObjectFlag($objectClass);

        return !empty($newObjectFlag);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return string
     * @throws \Darvin\Utils\NewObject\NewObjectException
     */
    private function getNewObjectFlag($entityClass)
    {
        try {
            $doctrineMeta = $this->em->getClassMetadata($entityClass);
        } catch (MappingException $ex) {
            throw new NewObjectException(sprintf('Unable to get Doctrine metadata for class "%s".', $entityClass));
        }

        $meta = $this->metadataFactory->getMetadata($doctrineMeta);

        return isset($meta['newObjectFlags'][$entityClass]) ? $meta['newObjectFlags'][$entityClass] : null;
    }
}
