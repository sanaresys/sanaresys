<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Support\Htmlable;
use Closure;

class CustomTextArea extends Entry
{
    protected string $view = 'filament.infolists.components.custom-text-area';

    protected Htmlable|Closure|string|null $customPlaceholder = null;

    public function placeholder(string $placeholder): static
    {
        $this->customPlaceholder = $placeholder;
        return $this;
    }

    public function getContent(): ?string
    {
        $state = $this->getState();
        return $state ? (string) $state : null;
    }

    public function getCustomPlaceholder(): ?string
    {
        return $this->evaluate($this->customPlaceholder ?? '');
    }
}
