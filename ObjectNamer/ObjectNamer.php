<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ObjectNamer;

use Darvin\Utils\Strings\StringsUtil;
use Doctrine\Common\Util\ClassUtils;

/**
 * Object namer
 */
class ObjectNamer implements ObjectNamerInterface
{
    /**
     * @var array
     */
    private $names;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->names = [];
    }

    /**
     * {@inheritdoc}
     */
    public function name($objectOrClass)
    {
        $class = preg_replace('/(.*[^\\\]+)Interface$/', '$1', is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass);

        if (!isset($this->names[$class])) {
            $nsParts = array_map(function ($nsPart) {
                return explode('_', StringsUtil::toUnderscore($nsPart));
            }, explode('\\', $class));
            $offset = array_search(['entity'], $nsParts);

            if ($offset) {
                $nsParts = array_slice($nsParts, $offset + 1);
            }

            $nsPartsCount = count($nsParts);

            for ($i = 0; $i < $nsPartsCount - 1; $i++) {
                if (array_intersect($nsParts[$i], $nsParts[$i + 1]) === $nsParts[$i]) {
                    unset($nsParts[$i]);
                }
            }

            $parts = [];

            foreach ($nsParts as $nsPart) {
                $parts = array_merge($parts, $nsPart);
            }

            $this->names[$class] = implode('_', $parts);
        }

        return $this->names[$class];
    }
}
