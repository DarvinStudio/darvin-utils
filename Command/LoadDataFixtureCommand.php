<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load data fixture command
 */
class LoadDataFixtureCommand extends Command
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param string                                                    $name      Command name
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container DI container
     * @param \Doctrine\ORM\EntityManager                               $em        Entity manager
     */
    public function __construct($name, ContainerInterface $container, EntityManager $em)
    {
        parent::__construct($name);

        $this->container = $container;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Loads data fixture to your database.')
            ->setDefinition([
                new InputArgument('fixture', InputArgument::REQUIRED, 'Pathname of data fixture class file.'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $offset = count(get_declared_classes());

        require_once $input->getArgument('fixture');

        $classes = [];

        foreach (array_slice(get_declared_classes(), $offset) as $class) {
            $reflection = new \ReflectionClass($class);

            if (!$reflection->isAbstract() && $reflection->implementsInterface(FixtureInterface::class)) {
                $classes[] = $class;
            }
        }
        if (empty($classes)) {
            throw new \RuntimeException(
                sprintf('File "%s" does not contain data fixture classes.', $input->getArgument('fixture'))
            );
        }
        foreach ($classes as $class) {
            /** @var \Doctrine\Common\DataFixtures\FixtureInterface $fixture */
            $fixture = new $class();

            if ($fixture instanceof ContainerAwareInterface) {
                $fixture->setContainer($this->container);
            }

            $output->writeln(sprintf('  <comment>></comment> <info>loading %s</info>', $class));

            $fixture->load($this->em);
        }
    }
}
