<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command;

use Darvin\Utils\Override\OverriderPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override command
 */
class OverrideCommand extends Command
{
    /**
     * @var \Darvin\Utils\Override\OverriderPoolInterface
     */
    private $overrider;

    /**
     * @param string                                        $name      Command name
     * @param \Darvin\Utils\Override\OverriderPoolInterface $overrider Overrider
     */
    public function __construct(string $name, OverriderPoolInterface $overrider)
    {
        parent::__construct($name);

        $this->overrider = $overrider;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Overrides files related to passed subject (entity classes, repository classes, templates, admin configs etc.).')
            ->setDefinition([
                new InputArgument('subject', InputArgument::REQUIRED, 'Subject to override name'),
                new InputArgument('bundle', InputArgument::OPTIONAL, 'Bundle name'),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->overrider->override($input->getArgument('subject'), $input->getArgument('bundle'));

        return 0;
    }
}
