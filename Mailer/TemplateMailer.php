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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Template mailer
 */
class TemplateMailer implements TemplateMailerInterface
{
    private const TYPES = [
        TemplateMailerInterface::TYPE_PUBLIC,
        TemplateMailerInterface::TYPE_SERVICE,
    ];

    /**
     * @var \Darvin\Utils\Mailer\MailerInterface
     */
    private $genericMailer;

    /**
     * @var \Darvin\Utils\Service\ServiceProviderInterface
     */
    private $templatingProvider;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\Utils\Mailer\MailerInterface               $genericMailer      Generic mailer
     * @param \Darvin\Utils\Service\ServiceProviderInterface     $templatingProvider Templating service provider
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator         Translator
     */
    public function __construct(MailerInterface $genericMailer, ServiceProviderInterface $templatingProvider, TranslatorInterface $translator)
    {
        $this->genericMailer = $genericMailer;
        $this->templatingProvider = $templatingProvider;
        $this->translator = $translator;
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
        return $this->sendEmail(
            TemplateMailerInterface::TYPE_PUBLIC,
            $to,
            $subject,
            $template,
            $templateParams,
            $options,
            $subjectParams,
            $attachments
        );
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
        return $this->sendEmail(
            TemplateMailerInterface::TYPE_SERVICE,
            $to,
            $subject,
            $template,
            $templateParams,
            $options,
            $subjectParams,
            $attachments
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sendEmail(
        string $type,
        $to,
        string $subject,
        string $template,
        array $templateParams = [],
        array $options = [],
        array $subjectParams = [],
        array $attachments = []
    ): int {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException(sprintf('Email type "%s" does not exist.', $type));
        }
        if (empty($to)) {
            return 0;
        }

        $body = $this->getTemplating()->render($template, array_merge([
            'email_type' => $type,
            'subject'    => $this->translateSubject($subject, $subjectParams),
        ], $templateParams));

        return $this->genericMailer->send($to, $subject, $body, $options, $subjectParams, $attachments);
    }

    /**
     * @param string $subject Subject
     * @param array  $params  Subject translation parameters
     *
     * @return string
     */
    private function translateSubject(string $subject, array $params): string
    {
        foreach ($params as $key => $param) {
            $params[$key] = $this->translator->trans($param);
        }

        return $this->translator->trans($subject, $params);
    }

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    private function getTemplating(): EngineInterface
    {
        return $this->templatingProvider->getService();
    }
}
