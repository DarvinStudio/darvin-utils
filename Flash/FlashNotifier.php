<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Flash;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Flash notifier
 */
class FlashNotifier implements FlashNotifierInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    private $flashBag;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag Flash bag
     */
    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * {@inheritdoc}
     */
    public function formError()
    {
        $this->error(FlashNotifierInterface::MESSAGE_FORM_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->flashBag->add(FlashNotifierInterface::TYPE_ERROR, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        $this->flashBag->add(FlashNotifierInterface::TYPE_SUCCESS, $message);
    }
}
