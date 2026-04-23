<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Los comandos Artisan de tu aplicación.
     *
     * Aquí registras comandos personalizados como GenerateNextMonthSlots.
     */
    protected $commands = [
        \App\Console\Commands\GenerateNextMonthSlots::class,
    ];

    /**
     * Define la programación de comandos.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar cada día 1 de mes a las 00:10
        $schedule->command('slots:generate-next-month')->monthlyOn(1, '00:10');
    }

    /**
     * Registra los comandos para la aplicación.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
