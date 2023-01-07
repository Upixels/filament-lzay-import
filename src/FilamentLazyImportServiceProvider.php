<?php

namespace Upixels\FilamentLazyImport;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Upixels\FilamentLazyImport\Console\Commands\ClearTemporaryDisk;

class FilamentLazyImportServiceProvider extends PluginServiceProvider {
    /**
     * Configure package
     *
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package->name('filament-lazy-import')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommands([
                ClearTemporaryDisk::class
            ]);
    }
}
