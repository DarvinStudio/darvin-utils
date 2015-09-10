<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\Annotation;

/**
 * Custom object annotation
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class CustomObject
{
    const ANNOTATION = __CLASS__;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $classPropertyPath;

    /**
     * @var string
     */
    public $initByPropertyName;

    /**
     * @var string
     */
    public $initByPropertyValuePath;
}
