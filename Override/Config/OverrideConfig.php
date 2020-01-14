<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Override\Config;

use Darvin\Utils\Override\Config\Model\Subject;

/**
 * Override config
 */
class OverrideConfig implements OverrideConfigInterface
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
        foreach ($config as $bundle => $subjects) {
            foreach ($subjects as $key => $subject) {
                $subject['bundle'] = $bundle;

                $config[$bundle][$key] = $subject;
            }
        }

        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubject(string $subjectName, ?string $bundleName): Subject
    {
        return $this->createSubject($subjectName, $this->getSubjectConfig($subjectName, $bundleName));
    }

    /**
     * @param string $subjectName Subject name
     * @param array  $config      Subject config
     *
     * @return \Darvin\Utils\Override\Config\Model\Subject
     */
    private function createSubject(string $subjectName, array $config): Subject
    {
        return new Subject($subjectName, $config['bundle'], $config['entities'], $config['templates']);
    }

    /**
     * @param string      $subjectName Subject name
     * @param string|null $bundleName  Bundle name
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getSubjectConfig(string $subjectName, ?string $bundleName): array
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
                $existingSubjectNames = array_merge($existingSubjectNames, array_keys($subjects));
            }

            $existingSubjectNames = array_unique($existingSubjectNames);

            throw new \InvalidArgumentException(
                sprintf('Subject "%s" does not exist. Existing subjects: "%s".', $subjectName, implode('", "', $existingSubjectNames))
            );
        }

        return $suitableSubject;
    }
}
