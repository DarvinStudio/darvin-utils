<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * @var \Darvin\Utils\User\UserQueryBuilderFiltererInterface|null
     */
    private $userQueryBuilderFilterer;

    /**
     * @var array
     */
    private $counts;

    /**
     * @param \Doctrine\ORM\EntityManager                               $em                       Entity manager
     * @param \Darvin\Utils\Mapping\MetadataFactoryInterface            $extendedMetadataFactory  Extended metadata factory
     * @param \Darvin\Utils\User\UserQueryBuilderFiltererInterface|null $userQueryBuilderFilterer User query builder filterer
     */
    public function __construct(
        EntityManager $em,
        MetadataFactoryInterface $extendedMetadataFactory,
        ?UserQueryBuilderFiltererInterface $userQueryBuilderFilterer = null
    ) {
        $this->em = $em;
        $this->extendedMetadataFactory = $extendedMetadataFactory;
        $this->userQueryBuilderFilterer = $userQueryBuilderFilterer;

        $this->counts = [];
    }

    /**
     * {@inheritDoc}
     */
    public function count(string $objectClass): int
    {
        if (!isset($this->counts[$objectClass])) {
            $meta = $this->getNewObjectFlagMeta($objectClass);

            if (empty($meta)) {
                $message = sprintf(
                    'Class "%s" must be annotated with "%s" annotation in order to count new objects.',
                    $objectClass,
                    NewObjectFlag::class
                );

                throw new \InvalidArgumentException($message);
            }

            $qb = $this->em->getRepository($objectClass)->createQueryBuilder('o')
                ->select('COUNT(o)')
                ->where(sprintf($meta['inverse'] ? 'o.%s != :%1$s' : 'o.%s = :%1$s', $meta['property']))
                ->setParameter($meta['property'], true);

            if (null !== $this->userQueryBuilderFilterer && $this->userQueryBuilderFilterer->isFilterable($qb)) {
                $this->userQueryBuilderFilterer->filter($qb);
            }

            $this->counts[$objectClass] = (int)$qb->getQuery()->getSingleScalarResult();
        }

        return $this->counts[$objectClass];
    }

    /**
     * {@inheritDoc}
     */
    public function isCountable(string $objectClass): bool
    {
        if (isset($this->counts[$objectClass])) {
            return true;
        }

        $meta = $this->getNewObjectFlagMeta($objectClass);

        return !empty($meta);
    }

    /**
     * @param string $entityClass Entity class
     *
     * @return array
     */
    private function getNewObjectFlagMeta(string $entityClass): array
    {
        $meta = $this->extendedMetadataFactory->getExtendedMetadata($entityClass);

        return isset($meta['newObjectFlag']) ? $meta['newObjectFlag'] : [];
    }
}
