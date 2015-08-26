<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Flash;

/**
 * Flash notifier
 */
interface FlashNotifierInterface
{
    const MESSAGE_FORM_ERROR = 'flash.error.form';

    const TYPE_ERROR   = 'error';
    const TYPE_SUCCESS = 'success';

    /**
     * Adds form error message.
     */
    public function formError();

    /**
     * @param string $message Message
     */
    public function error($message);

    /**
     * @param string $message Message
     */
    public function success($message);
}
