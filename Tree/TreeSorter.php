<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Tree;

use Darvin\Utils\Tree\Exception\ClassIsNotTreeException;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\TreeListener;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Tree sorter
 */
class TreeSorter implements TreeSorterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Gedmo\Tree\TreeListener
     */
    private $treeListener;

    /**
     * @param \Doctrine\ORM\EntityManager                                 $em               Entity manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Gedmo\Tree\TreeListener                                    $treeListener     Tree event listener
     */
    public function __construct(EntityManager $em, PropertyAccessorInterface $propertyAccessor, TreeListener $treeListener)
    {
        $this->em = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->treeListener = $treeListener;
    }

    /**
     * {@inheritdoc}
     */
    public function sortTree(array $entities)
    {
        if (empty($entities)) {
            return $entities;
        }

        $class = get_class(reset($entities));

        $config = $this->treeListener->getConfiguration($this->em, $class);

        if (empty($config)) {
            throw new ClassIsNotTreeException($class);
        }

        $children = [];

        foreach ($entities as $entity) {
            $parent = $this->propertyAccessor->getValue($entity, $config['parent']);

            if (empty($parent)) {
                continue;
            }

            $parentPath = $this->propertyAccessor->getValue($parent, $config['path_source']);

            if (!isset($children[$parentPath])) {
                $children[$parentPath] = [];
            }

            $children[$parentPath][$this->propertyAccessor->getValue($entity, $config['path_source'])] = $entity;
        }

        $sorted = [];

        foreach ($entities as $entity) {
            $this->addEntity($sorted, $entity, $children, $config);
        }

        return array_values($sorted);
    }

    /**
     * @param object[] $sorted   Sorted entities
     * @param object   $entity   Entity to add
     * @param object[] $children Child entities
     * @param array    $config   Tree configuration
     */
    private function addEntity(array &$sorted, $entity, array $children, array $config)
    {
        $path = $this->propertyAccessor->getValue($entity, $config['path_source']);

        if (isset($sorted[$path])) {
            return;
        }

        $sorted[$path] = $entity;

        if (!isset($children[$path])) {
            return;
        }
        foreach ($children[$path] as $child) {
            $this->addEntity($sorted, $child, $children, $config);
        }
    }
}
