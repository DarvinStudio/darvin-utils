<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Mapping\AnnotationDriver;

use Darvin\Utils\Mapping\MappingException;
use Doctrine\Common\Annotations\Reader;

/**
 * Abstract annotation driver
 */
abstract class AbstractDriver implements AnnotationDriverInterface
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * @param \Doctrine\Common\Annotations\Reader $reader Annotation reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $annotation   Annotation
     * @param string $objectClass  Object class
     * @param string $property     Property
     * @param string $message      Error message
     *
     * @return \Darvin\Utils\Mapping\MappingException
     */
    protected function createPropertyAnnotationInvalidException($annotation, $objectClass, $property, $message)
    {
        $message = sprintf(
            'Configuration of annotation "%s" on property "%s::$%s" is invalid: %s.',
            $annotation,
            $objectClass,
            $property,
            $message
        );

        return new MappingException($message);
    }
}
