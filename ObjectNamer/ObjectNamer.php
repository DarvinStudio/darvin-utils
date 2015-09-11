<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\ObjectNamer;

use Darvin\Utils\Strings\StringsUtil;

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
        $this->names = array();
    }

    /**
     * {@inheritdoc}
     */
    public function name($objectClass)
    {
        if (!isset($this->names[$objectClass])) {
            $parts = explode('_', str_replace('\\', '_', StringsUtil::toUnderscore($objectClass)));
            $offset = array_search('entity', $parts);

            if ($offset) {
                $parts = array_slice($parts, $offset + 1);
            }

            $partsCount = count($parts);

            for ($i = 0; $i < $partsCount - 1; $i++) {
                if ($parts[$i + 1] === $parts[$i]) {
                    unset($parts[$i]);
                }
            }

            $this->names[$objectClass] = implode('_', $parts);
        }

        return $this->names[$objectClass];
    }
}
