<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Event;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;

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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param array                       $changeSet Slugs change set: keys - old slugs, values - new slugs
     * @param \Doctrine\ORM\EntityManager $em        Entity manager
     */
    public function __construct(array $changeSet, EntityManager $em)
    {
        $this->changeSet = $changeSet;
        $this->em = $em;
    }

    /**
     * @param array $changeSet Change set
     *
     * @return SlugsUpdateEvent
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     *
     * @return SlugsUpdateEvent
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
