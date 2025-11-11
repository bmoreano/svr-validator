<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\QuestionSubmittedForValidation; // COMENTARIO: Importa tu evento
use App\Listeners\RunQuestionValidationChecks; // COMENTARIO: Importa tu listener

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        QuestionSubmittedForValidation::class => [
            RunQuestionValidationChecks::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // COMENTARIO: Mantén esto en false si estás registrando eventos manualmente.
                      // Si lo cambias a true, Laravel buscará automáticamente eventos/listeners
                      // en los directorios configurados, pero podría haber duplicados si ya están aquí.
    }
}