<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Overrider;

use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Darvin\Utils\Override\Config\Model\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

/**
 * Entity overrider
 */
class EntityOverrider implements OverriderInterface
{
    use NameTrait;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslatableManagerInterface
     */
    private $translatableManager;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $bundlesMeta;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface                            $em                  Entity manager
     * @param \Symfony\Component\Filesystem\Filesystem                        $filesystem          Filesystem
     * @param \Darvin\ContentBundle\Translatable\TranslatableManagerInterface $translatableManager Translatable manager
     * @param \Twig\Environment                                               $twig                Twig
     * @param array                                                           $bundlesMeta         Bundles metadata
     * @param string                                                          $projectDir          Project directory
     */
    public function __construct(
        EntityManagerInterface $em,
        Filesystem $filesystem,
        TranslatableManagerInterface $translatableManager,
        Environment $twig,
        array $bundlesMeta,
        string $projectDir
    ) {
        $this->em = $em;
        $this->filesystem = $filesystem;
        $this->translatableManager = $translatableManager;
        $this->twig = $twig;
        $this->bundlesMeta = $bundlesMeta;
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritDoc}
     */
    public function override(Subject $subject, ?callable $output = null): void
    {
        if (!isset($this->bundlesMeta[$subject->getBundle()])) {
            throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist.', $subject->getBundle()));
        }
        foreach ($subject->getEntities() as $entity) {
            $this->overrideEntity($entity, $subject->getBundle(), $this->bundlesMeta[$subject->getBundle()]['namespace'], $output);
        }
    }

    /**
     * @param string        $entity          Entity
     * @param string        $bundleName      Bundle name
     * @param string        $bundleNamespace Bundle namespace
     * @param callable|null $output          Output callback
     */
    private function overrideEntity(string $entity, string $bundleName, string $bundleNamespace, ?callable $output): void
    {
        list($class, $entityNamespace) = $this->parseEntity($entity);

        $fqcn             = implode('\\', [$bundleNamespace, 'Entity', $entity]);
        $packageNamespace = preg_replace('/^Darvin|Bundle$/', '', $bundleName);

        $repository   = $this->em->getClassMetadata($fqcn)->customRepositoryClassName;
        $translatable = null;
        $translation  = null;

        if (is_a($fqcn, TranslationInterface::class, true)) {
            $translatable = preg_replace('/.*\\\\/', '', $this->translatableManager->getTranslatableClass($fqcn));
        }
        if (is_a($fqcn, TranslatableInterface::class, true)) {
            $translation = preg_replace('/.*\\\\/', '', $this->translatableManager->getTranslationClass($fqcn));
        }

        $this->renderFile(
            $this->nameFile('Entity', $class, $entityNamespace, $packageNamespace, 'App'),
            '@DarvinUtils/override/entity.php.twig',
            [
                'bundle_namespace'  => $bundleNamespace,
                'entity_namespace'  => $entityNamespace,
                'package_namespace' => $packageNamespace,
                'class'             => $class,
                'fqcn'              => $fqcn,
                'has_repository'    => null !== $repository,
                'translatable'      => $translatable,
                'translation'       => $translation,
            ],
            $output
        );

        if (null !== $repository) {
            $this->renderFile(
                $this->nameFile('Repository', $class, $entityNamespace, $packageNamespace, '', 'Repository'),
                '@DarvinUtils/override/repository.php.twig',
                [
                    'class'             => $class,
                    'entity_namespace'  => $entityNamespace,
                    'package_namespace' => $packageNamespace,
                    'repository'        => $repository,
                ],
                $output
            );
        }
        if (is_a($fqcn, TranslatableInterface::class, true)) {
            $translationEntity = str_replace(sprintf('%s\\Entity\\', $bundleNamespace), '', $this->translatableManager->getTranslationClass($fqcn));

            $this->overrideEntity($translationEntity, $bundleName, $bundleNamespace, $output);
        }
    }

    /**
     * @param string $dir              Directory
     * @param string $class            Class
     * @param string $entityNamespace  Entity namespace
     * @param string $packageNamespace Package namespace
     * @param string $prefix           Filename prefix
     * @param string $suffix           Filename suffix
     *
     * @return string
     */
    private function nameFile(string $dir, string $class, string $entityNamespace, string $packageNamespace, string $prefix, string $suffix = ''): string
    {
        $parts = [$this->projectDir, 'src', $dir, $packageNamespace];

        if ('' !== $entityNamespace) {
            $parts[] = $entityNamespace;
        }

        $parts[] = implode('', [$prefix, $class, $suffix]);

        return sprintf('%s.php', str_replace('\\', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $parts)));
    }

    /**
     * @param string        $pathname File pathname
     * @param string        $template Template name
     * @param array         $params   Template parameters
     * @param callable|null $output   Output callback
     */
    private function renderFile(string $pathname, string $template, array $params, ?callable $output): void
    {
        $this->filesystem->mkdir(dirname($pathname), 0755);
        $this->filesystem->dumpFile($pathname, $this->twig->render($template, $params));

        if (null !== $output) {
            $output($pathname);
        }
    }

    /**
     * @param string $entity Entity
     *
     * @return array
     */
    private function parseEntity(string $entity): array
    {
        $parts = explode('\\', $entity);

        $class = array_pop($parts);

        $namespace = implode('\\', $parts);

        return [$class, $namespace];
    }
}
