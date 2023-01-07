<?php

return [
    /**
     * Log file information
     */
    'file_log' => [
        'disk' => 'local',
        'original_file_directory' => 'filament-import/original/',
        'output_file_directory' => 'filament-import/processed/',
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
];
