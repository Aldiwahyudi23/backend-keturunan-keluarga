<?php

namespace App\Filament\Widgets;

use App\Models\Marriage;
use App\Models\ParentChildRelation;
use App\Models\Person;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Total Person
        $totalPeople = Person::count();

        // Total Person Male
        $totalMale = Person::where('gender', 'male')->count();

        // Total Person Female
        $totalFemale = Person::where('gender', 'female')->count();

        // Total Pasangan (Marriage yang masih aktif/belum cerai)
        $totalMarriages = Marriage::whereNull('divorce_date')->count();

        // Total Anak (relasi parent-child yang unik)
        $totalChildren = ParentChildRelation::distinct('child_id')->count('child_id');

        // Person yang belum memiliki pasangan
        $peopleWithoutSpouse = Person::whereDoesntHave('husbandMarriages', function ($query) {
            $query->whereNull('divorce_date');
        })->whereDoesntHave('wifeMarriages', function ($query) {
            $query->whereNull('divorce_date');
        })->count();

        return [
            Stat::make('Total Person', $totalPeople)
                ->description('Total seluruh data person')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 3, 5, 2, 8, 4, 6])
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors',
                ])
                ->url(route('filament.admin.resources.people.index'))
                ->icon('heroicon-o-user-group'),

            Stat::make('Laki-laki', $totalMale)
                ->description('Jumlah laki-laki')
                ->descriptionIcon('heroicon-o-user')
                ->color('info')
                ->icon('heroicon-o-user')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors',
                ])
                ->url(route('filament.admin.resources.people.index', ['tableFilters[gender][value]' => 'male'])),

            Stat::make('Perempuan', $totalFemale)
                ->description('Jumlah perempuan')
                ->descriptionIcon('heroicon-o-user')
                ->color('danger')
                ->icon('heroicon-o-user')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors',
                ])
                ->url(route('filament.admin.resources.people.index', ['tableFilters[gender][value]' => 'female'])),

            Stat::make('Pasangan', $totalMarriages)
                ->description('Total pasangan menikah')
                ->descriptionIcon('heroicon-o-heart')
                ->color('success')
                ->chart([2, 4, 6, 8, 5, 3, 7])
                ->icon('heroicon-o-heart'),

            Stat::make('Anak', $totalChildren)
                ->description('Total anak terdaftar')
                ->descriptionIcon('heroicon-o-user')
                ->color('warning')
                ->icon('heroicon-o-user'),

            Stat::make('Belum Berpasangan', $peopleWithoutSpouse)
                ->description('Person tanpa pasangan')
                ->descriptionIcon('heroicon-o-user-minus')
                ->color('gray')
                ->icon('heroicon-o-user-minus'),
        ];
    }
}
