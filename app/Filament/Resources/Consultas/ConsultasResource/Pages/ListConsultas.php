<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ConsultaResource\Widgets\ConsultaStatsOverview;


class ListConsultas extends ListRecords
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('nueva_consulta')
                ->label('Nueva Consulta')
                ->icon('heroicon-o-plus')
                ->url(fn () => \App\Filament\Resources\PacientesResource::getUrl('index')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(fn () => $this->getResource()::getEloquentQuery()->count()),

            'today' => Tab::make('Hoy')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('created_at', today())
                )
                ->badge(fn () =>
                    $this->getResource()::getEloquentQuery()
                        ->whereDate('created_at', today())
                        ->count()
                ),

            'this_week' => Tab::make('Esta Semana')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])
                )
                ->badge(fn () =>
                    $this->getResource()::getEloquentQuery()
                        ->whereBetween('created_at', [
                            now()->startOfWeek(),
                            now()->endOfWeek()
                        ])
                        ->count()
                ),

            'this_month' => Tab::make('Este Mes')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                )
                ->badge(fn () =>
                    $this->getResource()::getEloquentQuery()
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count()
                ),

            'trashed' => Tab::make('Eliminadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge(fn () =>
                    $this->getResource()::getEloquentQuery()
                        ->onlyTrashed()
                        ->count()
                ),
                
        ];
    }


}
