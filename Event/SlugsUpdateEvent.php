<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Event;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Slugs update event
 */
class SlugsUpdateEvent extends Event
{
    /**
     * @var array
     */
    private $changeSet;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param array                                $changeSet Slugs change set: keys - old slugs, values - new slugs
     * @param \Doctrine\ORM\EntityManagerInterface $em        Entity manager
     */
    public function __construct(array $changeSet, EntityManagerInterface $em)
    {
        $this->changeSet = $changeSet;
        $this->em = $em;
    }

    /**
     * @param array $changeSet Change set
     *
     * @return SlugsUpdateEvent
     */
    public function setChangeSet(array $changeSet): SlugsUpdateEvent
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * @return array
     */
    public function getChangeSet(): array
    {
        return $this->changeSet;
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em Entity manager
     *
     * @return SlugsUpdateEvent
     */
    public function setEntityManager(EntityManagerInterface $em): SlugsUpdateEvent
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
