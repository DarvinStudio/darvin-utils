<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
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
     * @param string|null $html        HTML
     * @param bool        $success     Is success
     * @param string|null $message     Message
     * @param array       $data        Additional data
     * @param string|null $redirectUrl Redirect URL
     * @param int         $status      Response status code
     * @param array       $headers     Response headers
     */
    public function __construct(
        ?string $html = '',
        bool $success = true,
        ?string $message = null,
        array $data = [],
        ?string $redirectUrl = null,
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct(array_merge($data, [
            'html'        => $html,
            'message'     => $message,
            'redirectUrl' => $redirectUrl,
            'success'     => $success,
        ]), $status, $headers);
    }
}
