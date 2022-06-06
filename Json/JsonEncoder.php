<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Json;

/**
 * JSON encoder
 */
class JsonEncoder implements JsonEncoderInterface
{
    /**
     * @var bool
     */
    protected $debug;

    /**
     * @param bool $debug Is debug mode enabled
     */
    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function encode($value): string
    {
        $json = json_encode($value, $this->getOptions());

        if (false === $json) {
            throw new \RuntimeException((string)json_last_error_msg(), json_last_error());
        }

        return $json;
    }

    /**
     * @return int
     */
    protected function getOptions(): int
    {
        if ($this->debug) {
            return JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        return 0;
    }
}
