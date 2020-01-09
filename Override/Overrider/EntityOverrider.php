<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Overrider;

use Darvin\ContentBundle\Translatable\TranslatableManagerInterface;
use Darvin\Utils\Override\Config\Model\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

/**
 * Entity overrider
 */
class EntityOverrider implements OverriderInterface
{
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
    public function override(Subject $subject): void
    {
        if (!isset($this->bundlesMeta[$subject->getBundle()])) {
            throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist.', $subject->getBundle()));
        }
        foreach ($subject->getEntities() as $entity) {
            $this->overrideEntity($entity, $subject->getBundle(), $this->bundlesMeta[$subject->getBundle()]['namespace']);
        }
    }

    /**
     * @param string $entity          Entity
     * @param string $bundleName      Bundle name
     * @param string $bundleNamespace Bundle namespace
     */
    private function overrideEntity(string $entity, string $bundleName, string $bundleNamespace): void
    {
        list($class, $entityNamespace) = $this->parseEntity($entity);

        $fqcn             = sprintf('%s\Entity\%s', $bundleNamespace, $entity);
        $packageNamespace = preg_replace('/^Darvin|Bundle$/', '', $bundleName);

        $repository   = $this->em->getClassMetadata($fqcn)->customRepositoryClassName;
        $translatable = null;
        $translation  = null;

        if ($this->translatableManager->isTranslation($fqcn)) {
            $translatable = preg_replace('/.*\\\\/', '', $this->translatableManager->getTranslatableClass($fqcn));
        }
        if ($this->translatableManager->isTranslatable($fqcn)) {
            $translation = preg_replace('/.*\\\\/', '', $this->translatableManager->getTranslationClass($fqcn));
        }

        $content = $this->twig->render('@DarvinUtils/override/entity.php.twig', [
            'bundle_namespace'  => $bundleNamespace,
            'entity_namespace'  => $entityNamespace,
            'package_namespace' => $packageNamespace,
            'class'             => $class,
            'fqcn'              => $fqcn,
            'repository'        => $repository,
            'translatable'      => $translatable,
            'translation'       => $translation,
        ]);

        $filenameParts = [$this->projectDir, 'src', 'Entity', $packageNamespace];

        if ('' !== $entityNamespace) {
            $filenameParts[] = $entityNamespace;
        }

        $filenameParts[] = sprintf('App%s', $class);

        $filename = sprintf('%s.php', str_replace('\\', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $filenameParts)));

        $this->filesystem->mkdir(dirname($filename), 0755);

        $this->filesystem->dumpFile($filename, $content);

        if ($this->translatableManager->isTranslatable($fqcn)) {
            $translationEntity = str_replace(sprintf('%s\\Entity\\', $bundleNamespace), '', $this->translatableManager->getTranslationClass($fqcn));

            $this->overrideEntity($translationEntity, $bundleName, $bundleNamespace);
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
