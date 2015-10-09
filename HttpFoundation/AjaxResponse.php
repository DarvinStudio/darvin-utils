<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\HttpFoundation;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AJAX response
 */
class AjaxResponse extends JsonResponse
{
    /**
     * @param string $html       HTML
     * @param bool   $success    Is success
     * @param string $message    Message
     * @param array  $data       Additional data
     * @param bool   $reloadPage Whether to reload page
     * @param int    $status     Response status code
     * @param array  $headers    Response headers
     */
    public function __construct(
        $html = '',
        $success = true,
        $message = null,
        array $data = array(),
        $reloadPage = false,
        $status = 200,
        array $headers = array()
    ) {
        parent::__construct(array_merge($data, array(
            'html'       => $html,
            'message'    => $message,
            'reloadPage' => $reloadPage,
            'success'    => $success,
        )), $status, $headers);
    }
}
