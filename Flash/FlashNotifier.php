<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * {@inheritDoc}
     */
    public function formError(): void
    {
        $this->error(FlashNotifierInterface::MESSAGE_FORM_ERROR);
    }

    /**
     * {@inheritDoc}
     */
    public function done(bool $success, string $message): void
    {
        $success ? $this->success($message) : $this->error($message);
    }

    /**
     * {@inheritDoc}
     */
    public function error(string $message): void
    {
        $this->flashBag->add(FlashNotifierInterface::TYPE_ERROR, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function success(string $message): void
    {
        $this->flashBag->add(FlashNotifierInterface::TYPE_SUCCESS, $message);
    }
}
