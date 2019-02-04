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

/**
 * Mailer
 */
interface MailerInterface
{
    /**
     * @param mixed    $to            To
     * @param string   $subject       Subject
     * @param string   $body          Body
     * @param array    $options       Message object property values, for example "['reply_to' => 'me@example.com']"
     * @param array    $subjectParams Subject translation parameters
     * @param string[] $attachments   Attachment file pathnames
     *
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function send($to, string $subject, string $body, array $options = [], array $subjectParams = [], array $attachments = []): int;
}
