<?php

namespace Upixels\FilamentLazyImport\Actions;

use Closure;
use Exception;
use Filament\Forms\ComponentContainer;
use Konnco\FilamentImport\Import;
use Upixels\FilamentLazyImport\Jobs\ProcessFile;
use Upixels\FilamentLazyImport\Models\ExcelImportLog;
use Filament\Notifications\Notification;
use Konnco\FilamentImport\Actions\ImportAction as KonncoImportAction;

class LazyImportAction extends KonncoImportAction
{
    /**
     * Handle record creation
     *
     * @var null|string
     */
    protected null|string $handleLazyRecordCreation = null;

    /**
     * Mutate before create
     *
     * @var bool|string
     */
    protected bool|string $mutateBeforeCreateLazy = false;

    /**
     * Mutate after create
     *
     * @var bool|string
     */
    protected bool|string $mutateAfterCreateLazy = false;

    /**
     * Excel import log
     *
     * @var Model
     */
    protected string $logModel = ExcelImportLog::class;

    /**
     * notification handler
     *
     * @var bool|string
     */
    protected bool|string $notificationHandler = true;

    /**
     * Get Default Name
     */
    public static function getDefaultName(): ?string
    {
        return 'lazy_import';
    }

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('filament-import::actions.import'));

        $this->setInitialForm();

        $this->button();

        $this->groupedIcon('heroicon-s-plus');

        $this->action(function (ComponentContainer $form): void {
            if(!class_exists($this->handleLazyRecordCreation) || !method_exists((string)$this->handleLazyRecordCreation, 'handleRecordCreation')) {
                Notification::make()
                    ->danger()
                    ->title(trans('filament-lazy-import::actions.import_schedule_failed_title'))
                    ->body(trans('filament-lazy-import::actions.handle_record_creation_missing'))
                    ->persistent()
                    ->send();
                return;
            }
            $model = $form->getModel();

            $this->process(function (array $data) use ($model) {
                ProcessFile::dispatch(auth()->user(), $model, $this->logModel, $data, $this->fields, $this->mutateBeforeCreateLazy, $this->mutateAfterCreateLazy, $this->handleLazyRecordCreation, $this->notificationHandler);
                return;
            });

            Notification::make()
                ->success()
                ->title(trans('filament-lazy-import::actions.import_scheduled_title'))
                ->body(trans('filament-lazy-import::actions.import_succeeded'))
                ->persistent()
                ->send();
        });
    }

    /**
     * handle record creation
     *
     * @param Closure|string $class
     * @return static
     */
    public function handleRecordCreation(Closure|string $class): static
    {
        $this->handleLazyRecordCreation = $class;

        return $this;
    }

    /**
     * mutate before cretae
     *
     * @param bool|string|Closure $class
     * @return static
     */
    public function mutateBeforeCreate(bool|string|Closure $class): static
    {
        $this->mutateBeforeCreateLazy = $class;

        return $this;
    }

    /**
     * mutate after cretae
     *
     * @param bool|string|Closure $class
     * @return static
     */
    public function mutateAfterCreate(bool|string|Closure $class): static
    {
        $this->mutateAfterCreateLazy = $class;

        return $this;
    }

    /**
     * log model
     *
     * @param string $class
     */
    public function logModel(string $class): static
    {
        if(class_exists($class)) {
            $this->logModel = $class;
        }

        return $this;
    }

    /**
     * notification handler
     *
     * @param bool|string $class
     * @return static
     */
    public function notificationHandler(bool|string $class): static
    {
        $this->notificationHandler = $class;

        return $this;
    }
}
