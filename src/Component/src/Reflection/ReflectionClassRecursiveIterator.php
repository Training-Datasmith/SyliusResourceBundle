<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Resource\Reflection;

/**
 * Gets reflection classes for php files in the given directories.
 *
 * This class in inspired by this API Platform one:
 *
 * @see https://github.com/api-platform/core/blob/main/src/Metadata/Util/ReflectionClassRecursiveIterator.php
 *
 * @internal
 */
final class Reflection_Class_Recursive_Iterator
{
    /** @var array<string, array<class-string, \ReflectionClass<object>>> */
    private static array $local_cache;
    private function __construct()
    {
    }
    /**
     * @param string[] $directories
     *
     * @return array<class-string, \ReflectionClass<object>>
     */
    public static function get_reflection_classes_from_directories(array $directories, string $ignore_regex = ''): array
    {
        $id = hash('xxh3', implode('', $directories) . $ignore_regex);
        if (isset(self::$local_cache[$id])) {
            return self::$local_cache[$id];
        }
        $included_files = [];
        foreach ($directories as $path) {
            $iterator = new \Regex_Iterator(new \Recursive_Iterator_Iterator(new \Recursive_Directory_Iterator($path, \Filesystem_Iterator::SKIP_DOTS | \Filesystem_Iterator::FOLLOW_SYMLINKS), \Recursive_Iterator_Iterator::LEAVES_ONLY), '/^' . $ignore_regex . '.+\.php$/i', \Recursive_Regex_Iterator::GET_MATCH);
            foreach ($iterator as $file) {
                /** @var array{0: string} $file */
                $source_file = $file[0];
                if (!preg_match('(^phar:)i', (string) $source_file)) {
                    $source_file = realpath($source_file);
                }
                try {
                    require_once $source_file;
                } catch (\Throwable) {
                    // invalid PHP file (example: missing parent class)
                    continue;
                }
                $included_files[$source_file] = true;
            }
        }
        $sorted_classes = get_declared_classes();
        sort($sorted_classes);
        $sorted_interfaces = get_declared_interfaces();
        sort($sorted_interfaces);
        $declared = [...$sorted_classes, ...$sorted_interfaces];
        $ret = [];
        foreach ($declared as $class_name) {
            $reflection_class = new \ReflectionClass($class_name);
            $source_file = $reflection_class->get_file_name();
            if (isset($included_files[$source_file])) {
                $ret[$class_name] = $reflection_class;
            }
        }
        return self::$local_cache[$id] = $ret;
    }
}