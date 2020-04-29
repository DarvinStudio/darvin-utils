<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\EventListener\Cloner;

use Darvin\Utils\Event\ClonableEvents;
use Darvin\Utils\Event\CloneEvent;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Metadata\MetadataReader;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Copy cloned uploadables event subscriber
 */
class CopyUploadablesSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Vich\UploaderBundle\Metadata\MetadataReader
     */
    private $metadataReader;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Vich\UploaderBundle\Storage\StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem                    $filesystem       Filesystem
     * @param \Vich\UploaderBundle\Metadata\MetadataReader                $metadataReader   Uploader metadata reader
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor Property accessor
     * @param \Vich\UploaderBundle\Storage\StorageInterface               $storage          Uploader storage
     * @param string                                                      $tmpDir           Temporary file directory
     */
    public function __construct(
        Filesystem $filesystem,
        MetadataReader $metadataReader,
        PropertyAccessorInterface $propertyAccessor,
        StorageInterface $storage,
        string $tmpDir
    ) {
        $this->filesystem = $filesystem;
        $this->metadataReader = $metadataReader;
        $this->propertyAccessor = $propertyAccessor;
        $this->storage = $storage;
        $this->tmpDir = $tmpDir;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ClonableEvents::CLONED => ['copyClonedUploadables', 10],
        ];
    }

    /**
     * @param \Darvin\Utils\Event\CloneEvent $event Event
     */
    public function copyClonedUploadables(CloneEvent $event): void
    {
        $original = $event->getOriginal();

        $class = ClassUtils::getClass($original);

        if (!$this->metadataReader->isUploadable($class)) {
            return;
        }

        $clone = $event->getClone();

        foreach ($this->metadataReader->getUploadableFields($class) as $field => $attr) {
            $pathname = $this->storage->resolvePath($original, $field);

            if (null === $pathname) {
                continue;
            }

            $this->propertyAccessor->setValue($clone, $attr['fileNameProperty'], null);

            $tmpPathname = $this->generateTmpPathname();

            try {
                $this->filesystem->copy($pathname, $tmpPathname, true);
            } catch (FileNotFoundException $ex) {
                continue;
            }

            $this->propertyAccessor->setValue(
                $clone,
                $attr['propertyName'],
                new UploadedFile($tmpPathname, $this->propertyAccessor->getValue($original, $attr['fileNameProperty']), null, null, true)
            );
        }
    }

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    private function generateTmpPathname(): string
    {
        if (!is_dir($this->tmpDir) && !mkdir($this->tmpDir, 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create temporary files directory "%s".', $this->tmpDir));
        }

        $pathname = @tempnam($this->tmpDir, '');

        if (false === $pathname) {
            throw new \RuntimeException(sprintf('Unable to create temporary file for cloned uploadable in "%s".', $this->tmpDir));
        }

        return $pathname;
    }
}
