<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Cache\Varnish;

/**
 * Varnish cache clearer
 */
class VarnishCacheClearer implements VarnishCacheClearerInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @param string $url     URL
     * @param string $method  Method
     * @param int    $timeout Timeout
     */
    public function __construct(string $url, string $method, int $timeout)
    {
        $this->url = $url;
        $this->method = $method;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache(): void
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);

        if (false === curl_exec($curl)) {
            throw new \RuntimeException(curl_error($curl), curl_errno($curl));
        }
    }
}
