<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

/**
 * Phone validation constraint
 *
 * @Annotation
 */
class Phone extends Regex
{
    private const PATTERN = '/^\+?\d+\s*(\(\s*\d+\s*\))?\s*(\d+(-|\s+))*\d+$/';

    /**
     * @var string
     */
    public $message = 'phone.regex';

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        if (null === $options) {
            $options = [];
        }
        if (!isset($options['pattern'])) {
            $options['pattern'] = self::PATTERN;
        }

        parent::__construct($options);
    }

    /**
     * {@inheritDoc}
     */
    public function validatedBy(): string
    {
        return RegexValidator::class;
    }
}
