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

/**
 * Template mailer
 */
interface TemplateMailerInterface
{
    public const TYPE_PUBLIC  = 'public';
    public const TYPE_SERVICE = 'service';

    /**
     * @param mixed    $to             To
     * @param string   $subject        Subject
     * @param string   $template       Template
     * @param array    $templateParams Template parameters
     * @param array    $options        Message object property values, for example "['reply_to' => 'me@example.com']"
     * @param array    $subjectParams  Subject translation parameters
     * @param string[] $attachments    Attachment file pathnames
     *
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function sendPublicEmail($to, string $subject, string $template, array $templateParams = [], array $options = [], array $subjectParams = [], array $attachments = []): int;

    /**
     * @param mixed    $to             To
     * @param string   $subject        Subject
     * @param string   $template       Template
     * @param array    $templateParams Template parameters
     * @param array    $options        Message object property values, for example "['reply_to' => 'me@example.com']"
     * @param array    $subjectParams  Subject translation parameters
     * @param string[] $attachments    Attachment file pathnames
     *
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function sendServiceEmail($to, string $subject, string $template, array $templateParams = [], array $options = [], array $subjectParams = [], array $attachments = []): int;
}
