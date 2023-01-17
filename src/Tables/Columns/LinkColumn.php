<?php

namespace Upixels\FilamentLazyImport\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\HasIcon;
use Filament\Tables\Columns\Concerns\HasSize;
use Filament\Tables\Columns\Concerns\HasColor;
use Filament\Tables\Columns\Concerns\HasWeight;
use Filament\Tables\Columns\TextColumn;

class LinkColumn extends TextColumn
{
    /**
     * view
     *
     * @var string
     */
    protected string $view = 'filament-lazy-import::tables.columns.link-column';

    /**
     * Open in New Tab
     *
     * @var bool
     */
    protected bool $openInNewTab = true;

    /**
     * link callback
     *
     * @var Closure
     */
    protected Closure $linkCallback;

    /**
     * Link
     *
     * @param Closure $callback
     * @return static
     */
    public function link(Closure $callback): static
    {
        $this->linkCallback = $callback;

        return $this;
    }

    /**
     * Get Link
     */
    public function getLink()
    {
        $state = $this->getState();
        $record = $this->getRecord();

        return $this->evaluate($this->linkCallback, [
            'state' => $state,
            'record' => $record
        ]);
    }

    /**
     * Open in New Tab
     *
     * @param bool $openInNewTab
     * @return static
     */
    public function openInNewTab(bool $openInNewTab): static
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }

    /**
     * Get Open in New Tab
     *
     * @return bool
     */
    public function getOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }
}
