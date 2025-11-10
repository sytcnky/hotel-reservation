<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class IconPicker extends Field
{
    protected string $view = 'filament.forms.components.icon-picker';

    // single variant: outline | solid | bold | straight | thin | all
    protected string $variant = 'outline';

    protected int $gridColumns = 6;
    protected bool $withLabels = false;

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function gridColumns(int $columns): static
    {
        $this->gridColumns = $columns;
        return $this;
    }

    public function withLabels(bool $with = true): static
    {
        $this->withLabels = $with;
        return $this;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getGridColumns(): int
    {
        return $this->gridColumns;
    }

    public function getWithLabels(): bool
    {
        return $this->withLabels;
    }
}
