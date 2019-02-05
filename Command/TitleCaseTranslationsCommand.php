<?php
/**
 * @author    Alexey Gorshkov <moonhorn33@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Title case translations command
 */
class TitleCaseTranslationsCommand extends Command
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @param string $name    Command name
     * @param string $baseUrl Base URL
     * @param int    $timeout HTTP timeout
     */
    public function __construct($name, $baseUrl, $timeout)
    {
        parent::__construct($name);

        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Converts translations to title case')
            ->setDefinition([
                new InputArgument('pathname', InputArgument::REQUIRED, 'File pathname'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pathname = $input->getArgument('pathname');

        if (!is_file($pathname)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not file.', $pathname));
        }

        $content = @file_get_contents($pathname);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Unable to read file "%s".', $pathname));
        }

        $results     = Yaml::parse($this->toTitleCase($content));
        $flatResults = [];

        array_walk_recursive($results, function ($result) use (&$flatResults) {
            $flatResults[] = $result;
        });

        $translations = Yaml::parse($content);
        $i            = 0;

        array_walk_recursive($translations, function (&$translation) use ($flatResults, &$i) {
            if (!isset($flatResults[$i])) {
                throw new \RuntimeException(sprintf('Key "%d" does not exist.', $i));
            }

            $translation = $flatResults[$i];

            $i++;
        });

        if (false === @file_put_contents($pathname, Yaml::dump($translations, PHP_INT_MAX))) {
            throw new \RuntimeException(sprintf('Unable to write file "%s".', $pathname));
        }
    }

    /**
     * @param string $content Translation file content
     *
     * @return string
     */
    final protected function toTitleCase($content)
    {
        $curl = curl_init(sprintf('%s/titlecase/', $this->baseUrl));

        if (!$curl) {
            throw new \RuntimeException('Unable to init cURL.');
        }

        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POSTFIELDS     => [
                'title' => $content,
            ],
        ]);

        $result = curl_exec($curl);

        if (!$result) {
            throw new \RuntimeException(sprintf('Unable to get response from "%s": "%s".', $this->baseUrl, curl_error($curl)));
        }

        return $result;
    }
}
