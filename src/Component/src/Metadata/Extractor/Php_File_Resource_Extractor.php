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
namespace Sylius\Resource\Metadata\Extractor;

use Sylius\Resource\Metadata\Resource_Metadata;
/**
 * @experimental
 */
final class Php_File_Resource_Extractor extends Abstract_Resource_Extractor
{
    protected function extract_from_path(string $path): void
    {
        $resource = $this->get_php_file_closure($path)();
        if (!$resource instanceof Resource_Metadata) {
            return;
        }
        $resource_reflection = new \ReflectionClass($resource);
        foreach ($resource_reflection->get_properties() as $property) {
            $resolved_value = $this->resolve($property->get_value($resource));
            $property->set_value($resource, $resolved_value);
        }
        $this->resources[] = $resource;
    }
    /**
     * Scope isolated include.
     *
     * Prevents access to $this/self from included files.
     */
    private function get_php_file_closure(string $file_path): \Closure
    {
        return \Closure::bind(fn(): mixed => require $file_path, null, null);
    }
}