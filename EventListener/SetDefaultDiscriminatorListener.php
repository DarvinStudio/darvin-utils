<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;

/**
 * Set discriminator field default value event listener
 */
class SetDefaultDiscriminatorListener
{
    /**
     * @param \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $args Event arguments
     */
    public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $args): void
    {
        $meta = $args->getClassMetadata();

        if (!empty($meta->discriminatorMap) && !$meta->getReflectionClass()->isAbstract()) {
            $args->getClassTable()->getColumn($meta->discriminatorColumn['name'])->setDefault($meta->discriminatorValue);
        }
    }
}
