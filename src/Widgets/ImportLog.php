<?php

namespace Upixels\FilamentLazyImport\Widgets;

use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Upixels\FilamentLazyImport\Resources\ExcelImportLogResource;

class ImportLog extends BaseWidget
{
    /**
     * Column Span
     *
     * @var int|string|array
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Hidden Columns
     *
     * @var array
     */
    protected array $hiddenColumns = ['type'];

    /**
     * File Type
     */
    protected static int $fileType = 0;

    /**
     * Default records per page
     *
     * @return int
     */
    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 5;
    }

    /**
     * Default sort column
     *
     * @return string
     */
    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    /**
     * Default sort direction
     *
     * @return string
     */
    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    /**
     * Get table query
     *
     * @return Builder
     */
    protected function getTableQuery(): Builder
    {
        $query = ExcelImportLogResource::getEloquentQuery();

        if(static::$fileType > 0) {
            $query->where('type', static::$fileType);
        }

        return $query;
    }

    /**
     * Get table columns
     *
     * @return array
     */
    protected function getTableColumns(): array
    {
        return ExcelImportLogResource::getTableColumns($this->hiddenColumns);
    }
}
