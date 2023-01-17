<?php

namespace Upixels\FilamentLazyImport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExcelImportLog extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * get all types
     *
     * @return array
     */
    public static function types(): array
    {
        return array_flip(config('filament-lazy-import.type', []));
    }

    /**
     * Get the input file
     */
    public function inputFile()
    {
        return $this->morphOne(config('yuyuStorage.attachment_class'), 'attachable')
            ->where('attachment_type_id', config('filament-lazy-import.attachment_type.input_file'));
    }

    /**
     * Get the output file
     */
    public function outputFile()
    {
        return $this->morphOne(config('yuyuStorage.attachment_class'), 'attachable')
            ->where('attachment_type_id', config('filament-lazy-import.attachment_type.output_file'));
    }

    /**
     * Get the importer of this file.
     */
    public function importer()
    {
        return $this->belongsTo(config('filament-lazy-import.models.user', \App\Models\User::class), 'created_by');
    }
}
