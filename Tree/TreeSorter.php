<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Tree;

use Darvin\Utils\Tree\Exception\ClassIsNotTreeException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\TreeListener;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Tree sorter
 */
class TreeSorter implements TreeSorterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
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
     * @param \Doctrine\ORM\EntityManagerInterface                        $em               Entity manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Gedmo\Tree\TreeListener                                    $treeListener     Tree event listener
     */
    public function __construct(EntityManagerInterface $em, PropertyAccessorInterface $propertyAccessor, TreeListener $treeListener)
    {
        $this->em = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->treeListener = $treeListener;
    }

    /**
     * {@inheritDoc}
     */
    public function sortTree(array $entities): array
    {
        if (empty($entities)) {
            return $entities;
        }

        $class = ClassUtils::getClass(reset($entities));

        $config = $this->treeListener->getConfiguration($this->em, $class);

        if (empty($config)) {
            throw new ClassIsNotTreeException($class);
        }

        $children = [];

        foreach ($entities as $entity) {
            $parent = $this->propertyAccessor->getValue($entity, $config['parent']);

            if (null === $parent) {
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
    private function addEntity(array &$sorted, object $entity, array $children, array $config): void
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
