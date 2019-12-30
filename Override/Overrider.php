<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override;

/**
 * Overrider
 */
class Overrider implements OverriderInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config Configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function override(string $subjectName, ?string $bundleName = null): void
    {
        dump($this->getSubject($subjectName, $bundleName));
    }

    /**
     * @param string      $subjectName Subject to override name
     * @param string|null $bundleName  Bundle name
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getSubject(string $subjectName, ?string $bundleName): array
    {
        if (null !== $bundleName) {
            if (!isset($this->config[$bundleName])) {
                throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist or has nothing to override.', $bundleName));
            }
            if (!isset($this->config[$bundleName][$subjectName])) {
                throw new \InvalidArgumentException(sprintf(
                    'Subject "%s" does not exist in bundle "%s". Existing subjects: "%s".',
                    $subjectName,
                    $bundleName,
                    implode('", "', array_keys($this->config[$bundleName]))
                ));
            }

            return $this->config[$bundleName][$subjectName];
        }

        $suitableSubject = null;

        foreach ($this->config as $subjects) {
            if (!isset($subjects[$subjectName])) {
                continue;
            }
            if (null !== $suitableSubject) {
                throw new \InvalidArgumentException(sprintf('Subject name "%s" is ambiguous. Please provide bundle name.', $subjectName));
            }

            $suitableSubject = $subjects[$subjectName];
        }
        if (null === $suitableSubject) {
            $existingSubjectNames = [];

            foreach ($this->config as $subjects) {
                $existingSubjectNames = array_combine($existingSubjectNames, array_keys($subjects));
            }

            $existingSubjectNames = array_unique($existingSubjectNames);

            throw new \InvalidArgumentException(
                sprintf('Subject "%s" does not exist. Existing subjects: "%s".', $subjectName, implode('", "', $existingSubjectNames))
            );
        }

        return $suitableSubject;
    }
}
