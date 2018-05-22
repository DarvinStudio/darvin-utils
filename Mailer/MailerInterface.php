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

/**
 * Mailer
 */
interface MailerInterface
{
    /**
     * @param string   $subject           Subject
     * @param string   $body              Body
     * @param mixed    $to                To
     * @param array    $subjectParams     Subject translation parameters
     * @param string   $contentType       Content type
     * @param string[] $filePathnames     Attached file pathnames
     * @param array    $messageProperties Message object property values, for example "['reply_to' => 'me@example.com']"
     *
     * @return int
     */
    public function send($subject, $body, $to, array $subjectParams = [], $contentType = 'text/html', array $filePathnames = [], array $messageProperties = []);
}
