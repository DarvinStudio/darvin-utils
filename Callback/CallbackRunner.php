<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Callback;

use Psr\Container\ContainerInterface;

/**
 * Callback runner
 */
class CallbackRunner implements CallbackRunnerInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @param \Psr\Container\ContainerInterface $container Service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function runCallback(string $id, ?string $method = null, ...$args)
    {
        if ($this->container->has($id)) {
            $service = $this->container->get($id);

            if (null === $method) {
                if (!is_callable($service)) {
                    throw new \InvalidArgumentException(sprintf('Class "%s" is not callable. Make sure it has "__invoke()" method.', get_class($service)));
                }

                return $service(...$args);
            }
            if (!method_exists($service, $method)) {
                throw new \InvalidArgumentException(sprintf('Method "%s::%s()" does not exist.', get_class($service), $method));
            }

            return $service->$method(...$args);
        }
        if (!class_exists($id)) {
            throw new \InvalidArgumentException(
                sprintf('Service or class "%s" does not exist. If it is a service, make sure it is public.', $id)
            );
        }
        if (null === $method) {
            throw new \InvalidArgumentException('Method not specified.');
        }
        if (!method_exists($id, $method)) {
            throw new \InvalidArgumentException(sprintf('Method "%s::%s()" does not exist.', $id, $method));
        }

        $callable = [$id, $method];

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('Method "%s::%s()" is not statically callable.', $id, $method));
        }

        return $callable(...$args);
    }
}
