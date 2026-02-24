<?php

namespace App\Filament\Pages;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class CustomLogin extends BaseLogin
{
    public function getTitle(): string
    {
        return 'Iniciar sesion en Sanare';
    }

    public function getHeading(): string
    {
        return 'Iniciar sesion';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        // Fallback defensivo: si por cualquier motivo no se inicializó tenancy en este request
        // (ej. request Livewire desalineado), inicializamos por host antes de autenticar.
        $this->initializeTenantFromHostIfNeeded();

        try {
            $response = parent::authenticate();
        } catch (ValidationException $e) {
            if ($this->tryTenantManualLoginFallback()) {
                session()->regenerate();

                return app(LoginResponse::class);
            }

            Log::warning('Fallo de autenticacion en login de Filament.', [
                'host' => request()->getHost(),
                'tenancy_initialized' => tenancy()->initialized,
                'tenant_id' => tenancy()->tenant?->id,
                'db_default' => config('database.default'),
                'email' => data_get($this->data, 'email'),
            ]);

            throw $e;
        }

        $user = Filament::auth()->user();
        if (! $user) {
            return $response;
        }

        if ($user->hasRole('root')) {
            return $response;
        }

        // If tenant is already initialized, user is on tenant host and login is valid.
        if (tenancy()->initialized) {
            return $response;
        }

        $host = strtolower((string) request()->getHost());
        if (! in_array($host, $this->centralDomains(), true)) {
            return $response;
        }

        $centroId = $user->centro_id;
        if (! $centroId) {
            return $response;
        }

        $centro = Centros_Medico::on('mysql')->select(['id', 'tenancy_mode'])->find($centroId);
        if (! $centro || ($centro->tenancy_mode ?? 'legacy') !== 'domain') {
            return $response;
        }

        $tenant = Tenant::where('centro_id', $centroId)->first();
        $domain = $tenant?->getPrimaryDomain();

        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $message = $domain
            ? "Este usuario debe iniciar sesion en {$domain}/admin."
            : 'Este usuario debe iniciar sesion en el subdominio de su clinica.';

        throw ValidationException::withMessages([
            'data.email' => $message,
        ]);
    }

    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('email')
            ->label('Correo electronico')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): TextInput
    {
        return TextInput::make('password')
            ->label('Contrasena')
            ->password()
            ->required()
            ->autocomplete('current-password')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Checkbox
    {
        return Checkbox::make('remember')
            ->label('Recordarme');
    }

    protected function centralDomains(): array
    {
        return array_values(array_filter(array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            (array) config('tenancy.central_domains', [])
        )));
    }

    protected function initializeTenantFromHostIfNeeded(): void
    {
        if (tenancy()->initialized) {
            return;
        }

        $host = strtolower((string) request()->getHost());
        if (in_array($host, $this->centralDomains(), true)) {
            return;
        }

        $tenant = Tenant::query()
            ->whereHas('domains', fn ($q) => $q->where('domain', $host))
            ->first();

        if ($tenant) {
            tenancy()->initialize($tenant);
        }
    }

    protected function tryTenantManualLoginFallback(): bool
    {
        if (! tenancy()->initialized) {
            return false;
        }

        $email = (string) data_get($this->data, 'email', '');
        $password = (string) data_get($this->data, 'password', '');
        $remember = (bool) data_get($this->data, 'remember', false);

        if ($email === '' || $password === '') {
            return false;
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return false;
        }

        try {
            if (! Hash::check($password, (string) $user->password)) {
                return false;
            }
        } catch (\Throwable $hashError) {
            return false;
        }

        Filament::auth()->login($user, $remember);

        return true;
    }
}
