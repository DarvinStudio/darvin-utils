<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mailer;

use Darvin\Mailer\MailerException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $from;

    /**
     * @param \Psr\Log\LoggerInterface                           $logger       Logger
     * @param \Symfony\Component\HttpFoundation\RequestStack     $requestStack Request stack
     * @param \Swift_Mailer                                      $swiftMailer  Swift Mailer
     * @param \Symfony\Component\Translation\TranslatorInterface $translator   Translator
     * @param string                                             $charset      Charset
     * @param string                                             $from         From
     */
    public function __construct(
        LoggerInterface $logger,
        RequestStack $requestStack,
        \Swift_Mailer $swiftMailer,
        TranslatorInterface $translator,
        $charset,
        $from
    ) {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->swiftMailer = $swiftMailer;
        $this->translator = $translator;
        $this->charset = $charset;
        $this->from = $from;
    }

    /**
     * {@inheritdoc}
     */
    public function send($subject, $body, $to, array $subjectParams = [], $contentType = 'text/html', array $filePathnames = [])
    {
        if (empty($to)) {
            return 0;
        }

        $subject = $this->translateSubject($subject, $subjectParams);

        $request = $this->requestStack->getCurrentRequest();

        if (!empty($request)) {
            $subject = $request->getHost().' '.$subject;
        }

        $message = new \Swift_Message($subject, $body, $contentType, $this->charset);
        $message
            ->setFrom($this->from)
            ->setTo($to);

        foreach ($filePathnames as $filePathname) {
            if (!is_readable($filePathname)) {
                throw new MailerException(sprintf('File "%s" is not readable.', $filePathname));
            }

            $message->attach(\Swift_Attachment::fromPath($filePathname));
        }

        $failedRecipients = [];
        $sent = $this->swiftMailer->send($message, $failedRecipients);

        if (!empty($failedRecipients)) {
            $message = sprintf(
                '%s: unable to send e-mail with subject "%s" to recipient(s) "%s".',
                __METHOD__,
                $subject,
                implode('", "', $failedRecipients)
            );
            $this->logger->error($message);
        }

        return $sent;
    }

    /**
     * @param string $subject       Subject
     * @param array  $subjectParams Subject translation parameters
     *
     * @return string
     */
    private function translateSubject($subject, array $subjectParams)
    {
        foreach ($subjectParams as &$param) {
            $param = $this->translator->trans($param);
        }

        unset($param);

        return $this->translator->trans($subject, $subjectParams);
    }
}
