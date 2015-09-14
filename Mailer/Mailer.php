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

use Psr\Log\LoggerInterface;

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
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $from;

    /**
     * @param \Psr\Log\LoggerInterface $logger      Logger
     * @param \Swift_Mailer            $swiftMailer Swift Mailer
     * @param string                   $charset     Charset
     * @param string                   $from        From
     */
    public function __construct(LoggerInterface $logger, \Swift_Mailer $swiftMailer, $charset, $from)
    {
        $this->logger = $logger;
        $this->swiftMailer = $swiftMailer;
        $this->charset = $charset;
        $this->from = $from;
    }

    /**
     * {@inheritdoc}
     */
    public function send($subject, $body, $to, $contentType = 'text/html')
    {
        $message = new \Swift_Message($subject, $body, $contentType, $this->charset);
        $message
            ->setFrom($this->from)
            ->setTo($to);

        $failedRecipients = array();
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
}
