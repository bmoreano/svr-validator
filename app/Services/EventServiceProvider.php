<?php
namespace App\Providers;

use App\Models\Question; // Importar el modelo
use App\Observers\QuestionObserver; // Importar el observer
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Le decimos a Laravel que use QuestionObserver para el modelo Question.
        //Question::observe(QuestionObserver::class);
    }
}