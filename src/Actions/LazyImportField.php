<?php

namespace Upixels\FilamentLazyImport\Actions;

use Konnco\FilamentImport\Actions\ImportField as KonncoImportField;

class LazyImportField extends KonncoImportField
{
    /**
     * Make
     *
     * @var string $name
     * @return self
     */
    public static function make(string $name): self
    {
        return new self($name);
    }
}
