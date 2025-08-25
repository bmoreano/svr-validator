<?php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedDomainForRole implements ValidationRule
{
    protected string $role;
    protected array $domainMap;

    /**
     * Define los dominios permitidos para roles específicos.
     */
    public function __construct(string $role)
    {
        $this->role = $role;
        $this->domainMap = [
            'validador' => 'caces.gob.ec',
            // Puedes añadir más roles y dominios aquí en el futuro
            // 'otro_rol' => 'otrodominio.com',
        ];
    }

    /**
     * Ejecuta la regla de validación.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si el rol no está en nuestro mapa, no aplicamos la restricción.
        if (!array_key_exists($this->role, $this->domainMap)) {
            return;
        }

        $requiredDomain = $this->domainMap[$this->role];
        $emailDomain = substr($value, strpos($value, '@') + 1);

        if ($emailDomain !== $requiredDomain) {
            $fail("Para el rol '{$this->role}', el correo debe pertenecer al dominio '@{$requiredDomain}'.");
        }
    }
}