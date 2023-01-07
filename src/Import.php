<?php

namespace Upixels\FilamentLazyImport;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Import as KonncoImport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class Import extends KonncoImport {
    /**
     * Lazy Disk
     *
     * @var string
     */
    protected $lazyDisk = 'local';

    /**
     * Output file
     *
     * @var string
     */
    protected $outputFile = '';

    /**
     * excel import log
     *
     * @var Model
     */
    protected $excelImportLog;

    /**
     * log statuses
     *
     * @var array
     */
    protected $logStatuses;

    /**
     * Auth User: User by whom file is being imported
     *
     * @var null|Model
     */
    protected $authUser;

    /**
     * Make
     *
     * @param string $spreadsheetFilePath
     * @return self
     */
    public static function make(string $spreadsheetFilePath): self
    {
        return (new self)
            ->spreadsheet($spreadsheetFilePath);
    }

    /**
     * Execute: Process File
     *
     * @return void
     */
    public function execute(): void
    {
        $failed = 0;
        $total = 0;
        $passed = 0;


        /**
         * Process spreadsheet data line by line.
         */
        foreach ($this->getSpreadsheetData() as $line => $row) {
            $isValidationError = false;
            $isError = false;
            $message = '';

            /**
             * We are using DB::transaction while processing a row. If any error occurred we are rolling back and also continue to next line.
             */
            try {
                DB::transaction(function () use (&$total, $line, $row) {
                    $total += 1;
                    $prepareInsert = collect([]);
                    $rules = [];
                    $validationMessages = [];

                    /**
                     * Get fields & validation rules.
                     */
                    foreach (Arr::dot($this->fields) as $key => $value) {
                        $field = $this->formSchemas[$key];

                        $fieldValue = $value;

                        if ($field instanceof ImportField) {
                            $fieldValue = $row[$value];
                            $rules[$key] = $field->getValidationRules();
                            if (count($field->getCustomValidationMessages())) {
                                $validationMessages[$key] = $field->getCustomValidationMessages();
                            }
                        }

                        $prepareInsert[$key] = $fieldValue;
                    }

                    /**
                     * Validate input data.
                     */
                    $validator = $this->validator(data: Arr::undot($prepareInsert), rules: $rules, customMessages: $validationMessages);

                    if ($validator->fails()) {
                        throw new ValidationException($validator);
                        return;
                    }
                    else {
                        $prepareInsert = $validator->validated();
                    }

                    $prepareInsert = $this->doMutateBeforeCreate($prepareInsert);

                    $closure = $this->handleRecordCreation;
                    $model = $closure($prepareInsert);

                    $this->doMutateAfterCreate($model, $prepareInsert);
                });
                $passed += 1;
            }
            catch(ValidationException $error) {
                $failed += 1;
                DB::rollBack();
                $isValidationError = true;
                $isError = true;
                $message = json_encode($error->errors());
            }
            catch(\Exception $e) {
                DB::rollBack();
                $isError = true;
                $message = $e->getMessage();

            }
            $data = array_merge([
                $line,
                $isError ? 'Failed' : 'Pass',
                $isValidationError ? 'Yes' : 'No',
                $message
            ], $row->toArray());

            $this->pushRow($data);
        }

        $this->excelImportLog->status = $this->logStatuses['Completed'];
        $this->excelImportLog->total_no_of_records = $total;
        $this->excelImportLog->no_of_records_passed = $passed;
        $this->excelImportLog->no_of_records_failed = $failed;
        $this->excelImportLog->save();
    }

    /**
     * Dump data to output file.
     *
     * @param array $row
     * @param null|string $outputFile
     * @return void
     */
    protected function pushRow($row, $outputFile=null): void
    {
        Storage::disk($this->lazyDisk)->append($outputFile ?? $this->outputFile, implode(',', $row));
    }

    /**
     * validator
     *
     * @param array $data
     * @param array $rules
     * @param array $customMessages
     * @return \Illuminate\Validation\Validator
     */
    public function validator($data, $rules, $customMessages)
    {
        return Validator::make($data, $rules, $customMessages);
    }

    /**
     * output disk
     *
     * @param string $disk
     * @return self
     */
    public function outputDisk($disk): static
    {
        $this->lazyDisk = $disk;

        return $this;
    }

    /**
     * Set output file
     *
     * @param string $outputFile
     * @return self
     */
    public function outputFile($outputFile): static
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    /**
     * set auth user
     *
     * @param null|Model $authUser
     * @return self
     */
    public function authUser($authUser): static
    {
        $this->authUser = $authUser;

        return $this;
    }

    /**
     * Set Import Log Model
     *
     * @param Model $excelImportLog
     * @return self
     */
    public function importLog($excelImportLog): static
    {
        $this->excelImportLog = $excelImportLog;

        return $this;
    }

    /**
     * Set Log Statuses
     *
     * @param array $statuses
     * @return self
     */
    public function logStatuses($statuses): static
    {
        $this->logStatuses = $statuses;

        return $this;
    }
}
