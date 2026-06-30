<?php

namespace App\Filament\Widgets;

use App\Models\Person;
use App\Models\Marriage;
use App\Models\ParentChildRelation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GenderChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Gender';

    protected int | string | array $columnSpan = '1/2';

    protected function getData(): array
    {
        $male = Person::where('gender', 'male')->count();
        $female = Person::where('gender', 'female')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Person',
                    'data' => [$male, $female],
                    'backgroundColor' => ['#3b82f6', '#ef4444'],
                    'borderColor' => ['#2563eb', '#dc2626'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Laki-laki', 'Perempuan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}