<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mailer;

use Darvin\Utils\Service\ServiceProviderInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Template mailer
 */
class TemplateMailer implements TemplateMailerInterface
{
    /**
     * @var \Darvin\Utils\Mailer\MailerInterface
     */
    private $genericMailer;

    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $templatingProvider;

    /**
     * @param \Darvin\Utils\Mailer\MailerInterface           $genericMailer      Generic mailer
     * @param \Darvin\Utils\Service\ServiceProviderInterface $templatingProvider Templating service provider
     */
    public function __construct(MailerInterface $genericMailer, ServiceProviderInterface $templatingProvider)
    {
        $this->genericMailer = $genericMailer;
        $this->templatingProvider = $templatingProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function sendPublicEmail(
        $to,
        string $subject,
        string $template,
        array $templateParams = [],
        array $options = [],
        array $subjectParams = [],
        array $attachments = []
    ): int {
        $body = $this->getTemplating()->render($template, array_merge([
            'email_type' => TemplateMailerInterface::TYPE_PUBLIC,
        ], $templateParams));

        return $this->genericMailer->send($to, $subject, $body, $options, $subjectParams, $attachments);
    }

    /**
     * {@inheritDoc}
     */
    public function sendServiceEmail(
        $to,
        string $subject,
        string $template,
        array $templateParams = [],
        array $options = [],
        array $subjectParams = [],
        array $attachments = []
    ): int {
        $body = $this->getTemplating()->render($template, array_merge([
            'email_type' => TemplateMailerInterface::TYPE_SERVICE,
        ], $templateParams));

        return $this->genericMailer->send($to, $subject, $body, $options, $subjectParams, $attachments);
    }

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    private function getTemplating(): EngineInterface
    {
        return $this->templatingProvider->getService();
    }
}
