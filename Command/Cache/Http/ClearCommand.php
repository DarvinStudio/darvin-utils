<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command\Cache\Http;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * HTTP cache clear command
 */
class ClearCommand extends Command
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $dir;

    /**
     * @param string                                   $name Command name
     * @param \Symfony\Component\Filesystem\Filesystem $fs   Filesystem
     * @param string                                   $dir  Cache directory
     */
    public function __construct(string $name, Filesystem $fs, string $dir)
    {
        parent::__construct($name);

        $this->fs = $fs;
        $this->dir = $dir;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Clears HTTP cache.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->fs->remove($this->dir);

        $io->success('HTTP cache cleared.');

        return 0;
    }
}
