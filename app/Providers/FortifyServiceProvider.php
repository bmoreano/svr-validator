<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Validation\ValidationException;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LogoutResponseContract::class,
            CustomLogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
        /*/ --- LÓGICA DE AUTENTICACIÓN PERSONALIZADA ---
        Fortify::authenticateUsing(function (Request $request) {
            // 1. Encontrar al usuario por su email.
            $user = User::where('email', $request->email)->first();

            // 2. Verificar que el usuario exista Y que la contraseña sea correcta.
            if ($user && Hash::check($request->password, $user->password)) {

                // 3. ¡AQUÍ ESTÁ LA NUEVA REGLA! Verificar el rol del usuario.
                // Si el rol del usuario es 'validador' (y solo 'validador'),
                // impedimos el inicio de sesión.
                if ($user->role === 'validador') {
                    // Lanzamos el mismo error que si la contraseña fuera incorrecta.
                    // Esto evita dar información a un posible atacante de por qué falló el login.
                    throw ValidationException::withMessages([
                        'email' => [trans('auth.failed')],
                    ]);
                }

                // 4. Si todas las comprobaciones pasan, devolvemos el usuario.
                // Fortify se encargará de iniciar la sesión.
                return $user;
            }

            // Si el usuario no existe o la contraseña es incorrecta, Fortify manejará el error.
            return null;
        });*/
    }
}
