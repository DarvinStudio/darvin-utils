<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Translate translations command
 */
class TranslateTranslationsCommand extends Command
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string|null
     */
    private $direction;

    /**
     * @param string      $name   Command name
     * @param string|null $apiKey Yandex Translate API key
     */
    public function __construct(string $name, ?string $apiKey = null)
    {
        parent::__construct($name);

        $this->apiKey = $apiKey;

        $this->direction = null;

        $this->setDefinition([
            new InputArgument('target_languages', InputArgument::REQUIRED, 'Target language(s), comma separated'),
            new InputArgument('directory', InputArgument::REQUIRED, 'Translation file directory'),
            new InputArgument('yandex_translate_api_key', null !== $apiKey ? InputArgument::OPTIONAL : InputArgument::REQUIRED, 'Yandex Translate API key'),
            new InputOption('source_language', 's', InputOption::VALUE_OPTIONAL, 'Source language', 'en'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Translates translation files from one language to another.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from   = $input->getOption('source_language');
        $apiKey = $input->getArgument('yandex_translate_api_key');
        $io     = new SymfonyStyle($input, $output);

        if (null !== $apiKey) {
            $this->apiKey = $apiKey;
        }
        foreach (array_map('trim', explode(',', $input->getArgument('target_languages'))) as $to) {
            $io->section($to);

            $this->direction = implode('-', [$from, $to]);

            /** @var \SplFileInfo $file */
            foreach ((new Finder())->in($input->getArgument('directory'))->files()->name(sprintf('*.%s.yaml', $from)) as $file) {
                $io->comment('Translating '.$file->getPathname());

                file_put_contents(
                    $file->getPath().DIRECTORY_SEPARATOR.str_replace(sprintf('.%s.', $from), sprintf('.%s.', $to), $file->getFilename()),
                    Yaml::dump($this->translate(Yaml::parse(file_get_contents($file->getPathname()))), 100, 4)
                );
            }
        }
    }

    /**
     * @param array $translations Translations
     *
     * @return array
     */
    private function translate(array $translations): array
    {
        foreach ($translations as $key => $part) {
            if (is_array($part)) {
                $translations[$key] = $this->translate($part);
            } else {
                $translations[$key] = $this->translateText($part);
            }
        }

        return $translations;
    }

    /**
     * @param string|null $text Text
     *
     * @return string|null
     */
    private function translateText(?string $text): ?string
    {
        if (null === $text || preg_match('/^\s+$/', $text)) {
            return $text;
        }
        if (false !== strpos($text, '|')) {
            $parts = preg_split('/\s*\|\s*/', $text);

            foreach ($parts as $key => $part) {
                $parts[$key] = $this->translateText($part);
            }

            return implode(' | ', $parts);
        }

        preg_match_all('/%.*?%/', $text, $matches);

        if (!empty($matches[0])) {
            $placeholders = $matches[0];
            $words = preg_split('/%.*?%/', $text);
            $parts = [];

            foreach ($words as $i => $word) {
                $parts[] = '' !== $word && !preg_match('/^\s+$/', $word) ? $this->translateText($word) : $word;

                if (isset($placeholders[$i])) {
                    $parts[] = $placeholders[$i];
                }
            }

            return implode('', $parts);
        }

        $params = [
            'key'    => $this->apiKey,
            'text'   => $text,
            'lang'   => $this->direction,
            'format' => 'html',
        ];
        $json = file_get_contents('https://translate.yandex.net/api/v1.5/tr.json/translate?'.http_build_query($params));

        return json_decode($json, true)['text'][0];
    }
}
