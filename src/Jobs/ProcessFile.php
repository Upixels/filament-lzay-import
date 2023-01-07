<?php

namespace Upixels\FilamentLazyImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Upixels\FilamentLazyImport\Import;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $logChannel;

    /**
     * Model
     *
     * @var object
     */
    public $model;

    /**
     * Auth User
     *
     * @var null|Model
     */
    public $authUser;

    /**
     * Data
     *
     * @var array
     */
    public $data;

    /**
     * Fields
     *
     * @var array
     */
    public $fields;

    /**
     * mutate before create
     *
     * @var string
     */
    public $mutateBeforeCreate;

    /**
     * mutate after create
     *
     * @var string
     */
    public $mutateAfterCreate;

    /**
     * handle record creation
     *
     * @var string
     */
    public $handleRecordCreation;

    /**
     * log model
     *
     * @var Model
     */
    public $logModel;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * notification handler
     *
     * @var string
     */
    protected $notificationHandler;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($authUser, $model, $logModel, $data, $fields, $mutateBeforeCreate, $mutateAfterCreate, $handleRecordCreation, $notificationHandler)
    {
        $this->tries = config('filament-lazy-import.tries', 5);
        $this->authUser = $authUser;
        $this->model = $model;
        $this->logModel = $logModel;
        $this->data = $data;
        $this->fields = $fields;
        $this->mutateBeforeCreate = $mutateBeforeCreate;
        $this->mutateAfterCreate = $mutateAfterCreate;
        $this->handleRecordCreation = $handleRecordCreation;
        $this->logChannel = config('filament-lazy-import.log_channel');
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $statuses = array_flip(config('filament-lazy-import.status', []));

        $disk = config('filament-lazy-import.file_log.disk');
        $originalFilePath = config('filament-lazy-import.file_log.original_file_directory');
        $outputFilePath = config('filament-lazy-import.file_log.output_file_directory');
        $fileDisk = config('filament-import.temporary_files.disk');
        $contents = Storage::disk($fileDisk)->get($this->data['file']);
        $arrFile = explode('/', $this->data['file']);
        $fileName = array_pop($arrFile);
        $inputFile = $originalFilePath .$fileName;
        $outputFile = $outputFilePath .$fileName;

        /**
         * Store original
         */
        Storage::disk($disk)
            ->put($inputFile, $contents, 'private');

        /**
         * Create output file
         */
        if(!Storage::disk($disk)->exists($outputFile)) {
            $outputHeaders = $this->getOutputHeaders($fileDisk, $this->data['file']);
            Storage::disk($disk)
                ->put($outputFile, implode(',', $outputHeaders), 'private');
        }

        /**
         * Create import log.
         */
        $excelImportLog = (new $this->logModel())->where('input_file', $inputFile)->first();

        /**
         * We are not processing already processed files. If we get a log entry with input file name and status is completed. In that case we will skip that file.
         */
        if($excelImportLog && $excelImportLog->status == $statuses['Completed']) {
            Log::channel($this->logChannel)->info('Skipping: input file is being already processed.');
            return;
        }

        $excelImportLog = new $this->logModel();
        $excelImportLog->input_file = $inputFile;
        $excelImportLog->output_file = $outputFile;
        $excelImportLog->status = $statuses['Scheduled'];
        $excelImportLog->started_at = now();
        $excelImportLog->started_at = now();
        $excelImportLog->save();

        /**
         * Import file.
         */
        try {
            $selectedField = collect($this->data)
                ->except('fileRealPath', 'file', 'skipHeader');

            Import::make(spreadsheetFilePath: $this->data['file'])
                ->outputDisk($disk)
                ->outputFile($outputFile)
                ->fields($selectedField)
                ->formSchemas($this->fields)
                ->model($this->model)
                ->importLog($excelImportLog)
                ->logStatuses($statuses)
                ->disk('local')
                ->skipHeader((bool) $this->data['skipHeader'])
                ->authUser($this->authUser)
                ->mutateBeforeCreate(
                    $this->mutateBeforeCreate && method_exists((string)$this->mutateBeforeCreate, 'mutateBeforeCreate') ? $this->mutateBeforeCreate::mutateBeforeCreate(...) : $this->mutateBeforeCreate
                )
                ->mutateAfterCreate(
                    $this->mutateAfterCreate && method_exists((string)$this->mutateAfterCreate, 'mutateAfterCreate') ? $this->mutateAfterCreate::mutateAfterCreate(...) : $this->mutateAfterCreate
                )
                ->handleRecordCreation(
                    $this->handleRecordCreation::handleRecordCreation(...)
                )
                ->execute();
        }
        catch(\Exception $e) {
            $excelImportLog->status = $statuses['Failed'];
            $excelImportLog->message = $e->getMessage();
        }
        $excelImportLog->ended_at = now();
        $excelImportLog->save();

        /**
         * If we have auth user instance in that case we will notify a user.
         */
        if($this->authUser instanceof Model) {
            if(method_exists((string)$this->notificationHandler, 'notificationHandler')) {
                $this->notificationHandler::notificationHandler($this->authUser, $excelImportLog);
            }
            else if($this->notificationHandler) {
                if($excelImportLog->status == $statuses['Failed']){
                    Notification::make()
                        ->danger()
                        ->title(trans('filament-lazy-import::actions.import_failed_title'))
                        ->body(trans('filament-lazy-import::actions.import_failed'))
                        ->persistent()
                        ->sendToDatabase($this->authUser);
                }
                else {
                    Notification::make()
                        ->success()
                        ->title(trans('filament-lazy-import::actions.import_finished_title'))
                        ->body(trans('filament-lazy-import::actions.import_finished', ['count' => $excelImportLog->no_of_records_passed, 'failed' => $excelImportLog->no_of_records_failed, 'total' => $excelImportLog->total_no_of_records]))
                        ->persistent()
                        ->sendToDatabase($this->authUser);
                }
            }
        }
    }

    /**
     * Get output headers
     */
    public function getOutputHeaders($disk, $path)
    {
        $headings = (new HeadingRowImport)->toArray(new UploadedFile(Storage::disk($disk)->path($path), $path));

        return array_merge(['Row No', 'Status', 'Is validation error?', 'Message'], $headings[0][0]);
    }
}
