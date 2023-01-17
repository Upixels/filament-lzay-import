<?php

return [
    /**
     * Log file information
     */
    'file_log' => [
        'disk' => 'local',
        'input_file_directory' => 'filament-import/input/',
        'output_file_directory' => 'filament-import/output/',
    ],

    /**
     * Status that we are using for logging.
     */
    'status' => [
        1 => 'Scheduled',
        2 => 'Completed',
        3 => 'Failed'
    ],

    /**
     * Attachment Type
     */
    'attachment_type' => [
        'input_file' => 1,
        'output_file' => 2
    ],

    /**
     * File Type
     */
    'type' => [
        1 => 'Shop Brands',
    ],

    /**
     * File process logging
     */
    'log_channel' => 'daily',

    /**
     * Time in hour to delete temporary files.
     * Command to delete temporary files: php artisan filament-lazy-import:clear-temporary-disk
     */
    'temp_file_clearance_time' => 24,

    /**
     * Number of tires for job processing.
     *
     * Default: 5
     */
    'trie' => 5,

    'models' => [
        'user' => App\Models\User::class,
        'import_log' => Upixels\FilamentLazyImport\Models\ExcelImportLog::class
    ],

    'timestamp_format' => 'M d, Y H:i',
];
