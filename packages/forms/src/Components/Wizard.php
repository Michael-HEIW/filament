<?php

namespace Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Wizard\Step;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Illuminate\Support\Collection;
use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Wizard extends Component
{
    use HasExtraAlpineAttributes;

    protected string $view = 'forms::components.wizard';

    public static function make(): static
    {
        $static = app(static::class);
        $static->setUp();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'wizard::nextStep' => [
                function (Wizard $component, string $statePath, string $currentStep): void {
                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    $component->getChildComponentContainer()->getComponents()[$currentStep]->getChildComponentContainer()->validate();

                    $livewire->dispatchBrowserEvent('next-wizard-step', [
                        'statePath' => $statePath,
                    ]);
                },
            ],
        ]);
    }

    public function steps(array $steps): static
    {
        $this->childComponents($steps);

        return $this;
    }

    public function getConfig(): array
    {
        return collect($this->getChildComponentContainer()->getComponents())
            ->filter(static fn (Step $step): bool => ! $step->isHidden())
            ->mapWithKeys(static fn (Step $step): array => [$step->getId() => $step->getLabel()])
            ->toArray();
    }
}