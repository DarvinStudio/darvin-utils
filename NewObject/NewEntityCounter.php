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
     * @param \Doctrine\ORM\EntityManager                    $em              Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface $metadataFactory Metadata factory
     */
    public function __construct(EntityManager $em, MetadataFactoryInterface $metadataFactory)
    {
        $this->em = $em;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function count($objectClass)
    {
        try {
            $doctrineMeta = $this->em->getClassMetadata($objectClass);
        } catch (MappingException $ex) {
            throw new NewObjectException(sprintf('Unable to get Doctrine metadata for class "%s".', $objectClass));
        }

        $meta = $this->metadataFactory->getMetadata($doctrineMeta);

        if (!isset($meta['newObjectFlags'][$objectClass])) {
            $message = sprintf(
                'Class "%s" must be annotated with "%s" annotation in order to count new objects.',
                $objectClass,
                NewObjectFlag::ANNOTATION
            );

            throw new NewObjectException($message);
        }

        $newObjectFlag = $meta['newObjectFlags'][$objectClass];

        return (int) $this->em->getRepository($objectClass)->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where(sprintf('o.%s = :%1$s', $newObjectFlag))
            ->setParameter($newObjectFlag, true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
