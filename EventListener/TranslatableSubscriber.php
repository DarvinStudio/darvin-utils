<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber as BaseTranslatableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Translatable event subscriber
 */
class TranslatableSubscriber extends BaseTranslatableSubscriber
{
    /**
     * @var \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var string
     */
    private $translatableTrait;

    /**
     * @var string
     */
    private $translationTrait;

    /**
     * @var int
     */
    private $translatableFetchMode;

    /**
     * @var int
     */
    private $translationFetchMode;

    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    private $entityResolver;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer,
        callable $currentLocaleCallable = null,
        callable $defaultLocaleCallable = null,
        $translatableTrait,
        $translationTrait,
        $translatableFetchMode,
        $translationFetchMode,
        EntityResolverInterface $entityResolver
    ) {
        parent::__construct(
            $classAnalyzer,
            $currentLocaleCallable,
            $defaultLocaleCallable,
            $translatableTrait,
            $translationTrait,
            $translatableFetchMode,
            $translationFetchMode
        );

        $this->classAnalyzer = $classAnalyzer;
        $this->translatableTrait = $translatableTrait;
        $this->translationTrait = $translationTrait;

        $this->translatableFetchMode = constant(ClassMetadataInfo::class.'::FETCH_'.$translatableFetchMode);
        $this->translationFetchMode  = constant(ClassMetadataInfo::class.'::FETCH_'.$translationFetchMode);

        $this->entityResolver = $entityResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $meta */
        $meta = $args->getClassMetadata();

        if ($this->isTranslatable($meta)) {
            $this->mapTranslatable($meta);
        }
        if ($this->isTranslation($meta)) {
            $this->mapTranslation($meta);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta Metadata
     */
    private function mapTranslatable(ClassMetadataInfo $meta)
    {
        if (!$meta->hasAssociation('translations')) {
            $class = $this->entityResolver->resolve($meta->getName());

            $meta->mapOneToMany([
                'fieldName'     => 'translations',
                'targetEntity'  => $this->entityResolver->resolve($class::{'getTranslationEntityClass'}()),
                'mappedBy'      => 'translatable',
                'cascade'       => ['persist', 'merge', 'remove'],
                'orphanRemoval' => true,
                'fetch'         => $this->translatableFetchMode,
                'indexBy'       => 'locale',
            ]);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta Metadata
     */
    private function mapTranslation(ClassMetadataInfo $meta)
    {
        if (!$meta->hasField('id')) {
            (new ClassMetadataBuilder($meta))->createField('id', 'integer')->generatedValue('IDENTITY')->makePrimaryKey()->build();

            $meta->setIdGenerator(new IdentityGenerator());
        }
        if (!$meta->hasAssociation('translatable')) {
            $class = $this->entityResolver->resolve($meta->getName());

            $meta->mapManyToOne([
                'fieldName'    => 'translatable',
                'targetEntity' => $this->entityResolver->resolve($class::{'getTranslatableEntityClass'}()),
                'inversedBy'   => 'translations',
                'cascade'      => ['persist', 'merge'],
                'fetch'        => $this->translationFetchMode,
                'joinColumns'  => [
                    [
                        'name'                 => 'translatable_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'CASCADE',
                    ],
                ],
            ]);
        }

        $constraint = $meta->getTableName().'_unique_translation';

        if (!isset($meta->table['uniqueConstraints'][$constraint])) {
            $meta->table['uniqueConstraints'][$constraint] = [
                'columns' => ['translatable_id', 'locale'],
            ];
        }
        if (!($meta->hasField('locale') || $meta->hasAssociation('locale'))) {
            $meta->mapField([
                'fieldName' => 'locale',
                'type'      => 'string',
            ]);
        }
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta Metadata
     *
     * @return bool
     */
    private function isTranslatable(ClassMetadataInfo $meta)
    {
        return $this->getClassAnalyzer()->hasTrait($meta->getReflectionClass(), $this->translatableTrait, true);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $meta Metadata
     *
     * @return bool
     */
    private function isTranslation(ClassMetadataInfo $meta)
    {
        return $this->getClassAnalyzer()->hasTrait($meta->getReflectionClass(), $this->translationTrait, true);
    }
}