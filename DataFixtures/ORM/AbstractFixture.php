<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\DataFixtures\ORM;

use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Data fixture abstract implementation
 */
abstract class AbstractFixture implements FixtureInterface, ContainerAwareInterface
{
    protected const DEFAULT_IMAGE_WIDTH  = 800;
    protected const DEFAULT_IMAGE_HEIGHT = 600;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $entityIds = [];

    /**
     * @var array|null
     */
    private $fakerLocales = null;

    /**
     * @var \Faker\Generator[]
     */
    private $fakers = [];

    /**
     * {@inheritDoc}
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @return string[]
     */
    protected function listProviders()
    {
        return [
            'Andyftw\Faker\ImageProvider',
            'Bezhanov\Faker\Provider\Commerce',
            'insolita\faker\ShopProvider',
        ];
    }

    /**
     * @param string $fakerLocale Faker locale
     *
     * @return string
     */
    final protected function generateHtml(string $fakerLocale): string
    {
        if ('ru_RU' === $fakerLocale && class_exists('Darvin\TextGenerator\TextGenerator')) {
            return call_user_func(['Darvin\TextGenerator\TextGenerator', 'html'], 20);
        }

        $faker = $this->getFaker($fakerLocale);
        $parts = [];

        $count = $faker->numberBetween(3, 5);

        for ($i = 0; $i < $count; $i++) {
            $parts[] = sprintf('<p>%s</p>', $faker->realText(1000));
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * Generate a new image to disk and return its location.
     *
     * @example '/path/to/dir/13b73edae8443990be1aa8f1a483bc27.jpg'
     *
     * @param int|null    $width           Width of the picture in pixels
     * @param int|null    $height          Height of the picture in pixels
     * @param string|null $text            Text to generate on the picture
     * @param string|null $textColor       Text color in hexadecimal format
     * @param string|null $backgroundColor Background color in hexadecimal format
     * @param string|null $fontPath        The name/path to the font
     * @param string      $format          Image format, jpg or png
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     * @throws \RuntimeException
     */
    final protected function generateImageFile(
        ?int $width = null,
        ?int $height = null,
        ?string $text = null,
        ?string $textColor = null,
        ?string $backgroundColor = null,
        ?string $fontPath = null,
        string $format = 'png'
    ): UploadedFile
    {
        if (!class_exists('Andyftw\Faker\ImageProvider')) {
            throw new \RuntimeException('Please install "andyftw/image-faker" in order to generate images.');
        }
        if (null === $width) {
            $width = self::DEFAULT_IMAGE_WIDTH;
        }
        if (null === $height) {
            $height = self::DEFAULT_IMAGE_HEIGHT;
        }
        if (null !== $text) {
            $text = trim($text);

            if ('' === $text) {
                $text = null;
            }
        }

        $pathname = $this->getFaker()->imageFile(null, $width, $height, $format, true, $text, $textColor, $backgroundColor, $fontPath);

        return new UploadedFile($pathname, $pathname, null, null, null, true);
    }

    /**
     * @param string $entity     Entity class or interface
     * @param string $idProperty ID property
     *
     * @return object|null
     */
    final protected function getRandomEntity(string $entity, string $idProperty = 'id'): ?object
    {
        $entity = $this->getEntityResolver()->resolve($entity);

        if (!isset($this->entityIds[$entity])) {
            $this->entityIds[$entity] = array_column(
                $this->getEntityManager()->getRepository($entity)->createQueryBuilder('o')->select('o.'.$idProperty)->getQuery()->getScalarResult(),
                $idProperty
            );
        }

        $ids = $this->entityIds[$entity];

        if (!empty($ids)) {
            $id = $ids[array_rand($ids)];

            if (preg_match('/^\d+$/', $id)) {
                $id = (int)$id;
            }

            return $this->getEntityManager()->getReference($entity, $id);
        }

        return null;
    }

    /**
     * @return \Faker\Generator
     */
    final protected function getRandomFaker(): Generator
    {
        return $this->getFaker($this->getRandomFakerLocale());
    }

    /**
     * @return string
     */
    final protected function getRandomFakerLocale(): string
    {
        $fakerLocales = $this->getFakerLocales();

        return $fakerLocales[array_rand($fakerLocales)];
    }

    /**
     * @param string $fakerLocale Faker locale
     *
     * @return \Faker\Generator
     */
    final protected function getFaker(string $fakerLocale = Factory::DEFAULT_LOCALE): Generator
    {
        if (!isset($this->fakers[$fakerLocale])) {
            $faker = Factory::create($fakerLocale);

            foreach ($this->listProviders() as $class) {
                if (class_exists($class)) {
                    $faker->addProvider(new $class($faker));
                }
            }

            $this->fakers[$fakerLocale] = $faker;
        }

        return $this->fakers[$fakerLocale];
    }

    /**
     * @return array
     */
    final protected function getFakerLocales(): array
    {
        if (null === $this->fakerLocales) {
            $fakerLocales = [];

            foreach ($this->container->getParameter('locales') as $locale) {
                $fakerLocales[$locale] = implode('_', [$locale, strtoupper($locale)]);
            }
            if ($this->container->hasParameter('faker_locales') && is_array($this->container->getParameter('faker_locales'))) {
                $fakerLocales = array_merge($fakerLocales, $this->container->getParameter('faker_locales'));
            }

            $this->fakerLocales = $fakerLocales;
        }

        return $this->fakerLocales;
    }

    /**
     * @param string $entity Entity class or interface
     *
     * @return object
     */
    final protected function instantiateEntity(string $entity): object
    {
        $class = $this->getEntityResolver()->resolve($entity);

        return new $class();
    }

    /**
     * @param string $entity Entity class or interface
     *
     * @return object
     */
    final protected function instantiateTranslation(string $entity): object
    {
        $callback = [$this->getEntityResolver()->resolve($entity), 'getTranslationEntityClass'];

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not translatable.', $entity));
        }

        $class = $callback();

        return new $class();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    final protected function getEntityManager(): EntityManager
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Darvin\Utils\ORM\EntityResolverInterface
     */
    final protected function getEntityResolver(): EntityResolverInterface
    {
        return $this->container->get('darvin_utils.orm.entity_resolver');
    }
}
