<?php

namespace App\Http\Controllers;

use App\Models\Centros_Medico;
use App\Services\TenantIdentityService;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClinicRegistrationController extends Controller
{
    public function create()
    {
        return view('registro-clinica');
    }

    public function store(
        Request $request,
        TenantIdentityService $identityService,
        TenantProvisioningService $provisioningService
    ): RedirectResponse {
        $validated = $request->validate([
            'nombre_centro' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'rtn' => ['required', 'string', 'max:100', 'unique:centros_medicos,rtn'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($provisioningService->emailExistsInAnyTenant($validated['owner_email'])) {
            throw ValidationException::withMessages([
                'owner_email' => 'El correo ya esta en uso en otro tenant.',
            ]);
        }

        $slug = $identityService->generateSlug($validated['nombre_centro']);
        $identityService->validateSlugAvailable($slug);

        $centro = Centros_Medico::create([
            'nombre_centro' => $validated['nombre_centro'],
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'rtn' => $validated['rtn'],
            'slug' => $slug,
            'tenancy_mode' => 'domain',
        ]);

        try {
            $result = $provisioningService->provisionNewCenter($centro, [
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['password'],
            ]);

            $token = tenancy()->impersonate(
                tenant: $result->tenant,
                userId: (string) $result->adminUserId,
                redirectUrl: '/admin',
                authGuard: 'web'
            );

            $scheme = (string) config('tenancy.tenant_scheme', 'https');
            $target = "{$scheme}://{$result->primaryDomain}/tenant/impersonate/{$token->token}";

            Log::info('Onboarding de clinica completado y salto por impersonacion generado.', [
                'centro_id' => $centro->id,
                'tenant_id' => $result->tenant->id,
                'domain' => $result->primaryDomain,
                'database' => $result->databaseName,
                'admin_user_id' => $result->adminUserId,
            ]);

            return redirect()->away($target);
        } catch (\Throwable $e) {
            Log::error('Error en onboarding de clinica.', [
                'centro_id' => $centro->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            try {
                $centro->delete();
            } catch (\Throwable $cleanupError) {
                Log::error('Error limpiando centro tras fallo de onboarding.', [
                    'centro_id' => $centro->id,
                    'error' => $cleanupError->getMessage(),
                ]);
            }

            throw ValidationException::withMessages([
                'nombre_centro' => 'No se pudo completar el onboarding. Intente nuevamente.',
            ]);
        }
    }
}
