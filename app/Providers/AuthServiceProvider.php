<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Question;
use App\Models\User;
use App\Policies\QuestionPolicy;
use App\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * Aquí es donde mapeamos nuestros modelos de Eloquent a sus clases
     * de política correspondientes. Cuando Laravel necesite verificar un permiso
     * sobre un modelo, buscará en este array para encontrar la clase correcta.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Question::class => QuestionPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * Este método se llama cuando el proveedor de servicios es registrado.
     * La llamada a `registerPolicies()` es la que lee el array `$policies`
     * y registra formalmente cada política en el sistema de autorización.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}