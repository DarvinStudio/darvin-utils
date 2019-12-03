<?php declare(strict_types=1);
/**
 * @author    Darvin Studio <info@darvin-studio.ru>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Compress response event subscriber
 */
class CompressResponseSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'compressResponse',
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event Event
     */
    public function compressResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        $contentType = $response->headers->get('content-type');

        if (is_array($contentType)) {
            $contentType = reset($contentType);
        }
        if (Response::class === get_class($response) && (null === $contentType || 0 === strpos($contentType, 'text/html'))) {
            $response->setContent(trim(preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', PHP_EOL, preg_replace('/\h+/u', ' ', $response->getContent()))));
        }
    }
}
