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
use Darvin\Utils\User\UserQueryBuilderFiltererInterface;
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
    private $extendedMetadataFactory;

    /**
     * @var \Darvin\Utils\User\UserQueryBuilderFiltererInterface
     */
    private $userQueryBuilderFilterer;

    /**
     * @var array
     */
    private $counts;

    /**
     * @param \Doctrine\ORM\EntityManager                          $em                       Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface       $extendedMetadataFactory  Extended metadata factory
     * @param \Darvin\Utils\User\UserQueryBuilderFiltererInterface $userQueryBuilderFilterer User query builder filterer
     */
    public function __construct(
        EntityManager $em,
        MetadataFactoryInterface $extendedMetadataFactory,
        UserQueryBuilderFiltererInterface $userQueryBuilderFilterer
    ) {
        $this->em = $em;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->userQueryBuilderFilterer = $userQueryBuilderFilterer;

        $this->counts = [];
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

            $qb = $this->em->getRepository($objectClass)->createQueryBuilder('o')
                ->select('COUNT(o)')
                ->where(sprintf('o.%s = :%1$s', $newObjectFlag))
                ->setParameter($newObjectFlag, true);

            if ($this->userQueryBuilderFilterer->isFilterable($qb)) {
                $this->userQueryBuilderFilterer->filter($qb);
            }

            $this->counts[$objectClass] = (int) $qb->getQuery()->getSingleScalarResult();
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
     */
    private function getNewObjectFlag($entityClass)
    {
        $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

        return isset($meta['newObjectFlag']) ? $meta['newObjectFlag'] : null;
    }
}
