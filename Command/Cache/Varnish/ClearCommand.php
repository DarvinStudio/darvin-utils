<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command\Cache\Varnish;

use Darvin\Utils\Cache\Varnish\VarnishCacheClearerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Varnish cache clear command
 */
class ClearCommand extends Command
{
    /**
     * @var \Darvin\Utils\Cache\Varnish\VarnishCacheClearerInterface
     */
    private $clearer;

    /**
     * @param string                                                   $name    Command name
     * @param \Darvin\Utils\Cache\Varnish\VarnishCacheClearerInterface $clearer Varnish cache clearer
     */
    public function __construct(string $name, VarnishCacheClearerInterface $clearer)
    {
        parent::__construct($name);

        $this->clearer = $clearer;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Clears Varnish cache.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->clearer->clearCache();
        } catch (\RuntimeException $ex) {
            $io->error($ex->getMessage());

            return $ex->getCode();
        }

        $io->success('Varnish cache cleared.');

        return 0;
    }
}
