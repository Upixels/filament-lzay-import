<?php

namespace Upixels\FilamentLazyImport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClearTemporaryDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-lazy-import:clear-temporary-disk {--R|recursive : Delete files recursively}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear temporary disk.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $disk = config('filament-import.temporary_files.disk');
        $path = config('filament-import.temporary_files.directory');
        $clearanceTime = config('filament-lazy-import.temp_file_clearance_time', 24);
        $storage = Storage::disk($disk);
        $recursive = $this->option('recursive');
        $count = 0;

        foreach($storage->files($path, $recursive) as $file) {
            if(Carbon::parse($storage->lastModified($file))->addHours($clearanceTime)->isPast()) {
                $storage->delete($file);
                $this->info("{$file} deleted successfully!");
                $count += 1;
            }
        }

        $this->info("Total {$count} file(s) deleted.");

        return Command::SUCCESS;
    }
}
