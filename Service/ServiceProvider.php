<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Service provider
 */
class ServiceProvider implements ServiceProviderInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id Service ID
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getService()
    {
        return $this->container->get($this->id);
    }
}
