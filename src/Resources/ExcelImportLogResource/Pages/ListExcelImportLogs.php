<?php

namespace Upixels\FilamentLazyImport\Resources\ExcelImportLogResource\Pages;

use App\Filament\Resources\ExcelImportLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExcelImportLogs extends ListRecords
{
    protected static string $resource = ExcelImportLogResource::class;
}
