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
 * Extracts an array of metadata from a file or a list of files.
 *
 * @experimental
 */
interface Resource_Extractor_Interface
{
    /**
     * Parses all metadata files and convert them in an array.
     *
     * @return ResourceMetadata[]
     */
    public function get_resources(): array;
}