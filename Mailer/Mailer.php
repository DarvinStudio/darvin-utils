<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mailer;

use Darvin\Utils\Strings\StringsUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Mailer
 */
class Mailer implements MailerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @var bool
     */
    private $prependHost;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string|null
     */
    private $fromName;

    /**
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @param \Psr\Log\LoggerInterface                           $logger         Logger
     * @param \Symfony\Component\HttpFoundation\RequestStack     $requestStack   Request stack
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator     Translator
     * @param array                                              $defaultOptions Default options
     * @param bool                                               $prependHost    Whether to prepend host to subject
     * @param string                                             $fromEmail      From email
     * @param string|null                                        $fromName       From name
     * @param \Swift_Mailer|null                                 $swiftMailer    Swift Mailer
     */
    public function __construct(
        LoggerInterface $logger,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        array $defaultOptions,
        bool $prependHost,
        string $fromEmail,
        ?string $fromName = null,
        ?\Swift_Mailer $swiftMailer = null
    ) {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->defaultOptions = $defaultOptions;
        $this->prependHost = $prependHost;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->swiftMailer = $swiftMailer;
    }

    /**
     * {@inheritdoc}
     */
    public function send($to, string $subject, string $body, array $options = [], array $subjectParams = [], array $attachments = []): int
    {
        if (empty($this->swiftMailer) || empty($to)) {
            return 0;
        }

        $options = array_merge($this->defaultOptions, $options);
        $request = $this->requestStack->getCurrentRequest();
        $subject = $this->translateSubject($subject, $subjectParams);

        if ($this->prependHost && !empty($request)) {
            $subject = implode(' ', [$request->getHost(), $subject]);
        }

        $failed = [];
        $sent   = 0;

        try {
            $message = new \Swift_Message($subject, $body);
            $message->setFrom($this->fromEmail, $this->fromName);
            $message->setTo($to);

            foreach ($attachments as $attachment) {
                if (!is_readable($attachment)) {
                    throw new \RuntimeException(sprintf('Attachment file "%s" is not readable.', $attachment));
                }

                $message->attach(\Swift_Attachment::fromPath($attachment));
            }
            foreach ($options as $name => $value) {
                $setter = sprintf('set%s', StringsUtil::toCamelCase($name));

                if (!method_exists($message, $setter)) {
                    throw new \InvalidArgumentException(sprintf('Option "%s" does not exist.', $name));
                }

                $message->{$setter}($value);
            }

            $sent = $this->swiftMailer->send($message, $failed);
        } catch (\Swift_SwiftException $ex) {
            $this->logger->error(sprintf('%s: %s', __METHOD__, $ex->getMessage()));
        }
        if (!empty($failed)) {
            $this->logger->error(sprintf(
                '%s: unable to send e-mail with subject "%s" to recipient(s) "%s".',
                __METHOD__,
                $subject,
                implode('", "', $failed)
            ));
        }

        return $sent;
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
}
