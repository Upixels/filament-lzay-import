<?php

namespace Upixels\FilamentLazyImport\Resources;

use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Upixels\FilamentLazyImport\Models\ExcelImportLog;
use App\Filament\Resources\ExcelImportLogResource\Pages;
use Upixels\FilamentLazyImport\Tables\Columns\LinkColumn;
use Upixels\FilamentLazyImport\Resources\ExcelImportLogResource\Pages\ListExcelImportLogs;

class ExcelImportLogResource extends Resource
{
    /**
     * Model
     */
    protected static ?string $model = ExcelImportLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    /**
     * Get Model
     */
    public static function getModel(): string
    {
        $model = config('filament-lazy-import.models.import_log', static::$model);
        return $model ?? (string) Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->prepend('App\\Models\\');
    }

    /**
     * form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    /**
     * table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    /**
     * Get table columns
     */
    public static function getTableColumns(array $hiddenColumns = []): array
    {
        return [
            TextColumn::make('type')
                ->enum(
                    config('filament-lazy-import.type', [])
                )->hidden(in_array('type', $hiddenColumns)),
            LinkColumn::make('inputFile.id')
                ->label('Input File')
                ->formatStateUsing(function() {
                    return "";
                })

                ->icon('fas-file-download')
                ->link(function($state, $record) {
                    return $record->inputFile->download;
                })
                ->alignment('center'),
            TextColumn::make('total_no_of_records')
                ->label('Total No of Record(s)'),
            TextColumn::make('no_of_records_passed')
                ->label('Passed Record(s)'),
            TextColumn::make('no_of_records_failed')
                ->label('Failed Record(s)'),
            LinkColumn::make('outputFile.id')
                ->label('Output File')
                ->formatStateUsing(function() {
                    return "";
                })

                ->icon('fas-file-download')
                ->link(function($state, $record) {
                    return $record->outputFile->download;
                })
                ->alignment('center'),
            TextColumn::make('status')
                ->label('Status')
                ->enum(config('filament-lazy-import.status', [])),
            TextColumn::make('message')
                ->label('Message'),
            TextColumn::make('importer.name')
                ->label('Imported By'),
            TextColumn::make('started_at')
                ->label('Started At')
                ->dateTime(config('filament-lazy-import.timestamp_format', '')),
            TextColumn::make('ended_at')
                ->label('Finished At')
                ->dateTime(config('filament-lazy-import.timestamp_format', '')),
        ];
    }

    /**
     * Get Pages
     */
    public static function getPages(): array
    {
        return [
            'index' => ListExcelImportLogs::route('/'),
        ];
    }

    /**
     * Get global search eloquent query
     */
    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'inputFile' => function ($query) {
                $query->selectRaw('id, attachable_id, attachable_type, attachment_type_id, original_file_name, extension, mime_type, status, "WEB:14400" as download');
            },
            'outputFile' => function ($query) {
                $query->selectRaw('id, attachable_id, attachable_type, attachment_type_id, original_file_name, extension, mime_type, status, "WEB:14400" as download');
            },
            'importer'
        ]);
    }
}
