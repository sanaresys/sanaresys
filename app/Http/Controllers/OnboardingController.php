<?php

namespace App\Http\Controllers;

use App\Models\Centros_Medico;
use App\Models\CAIAutorizaciones;
use App\Models\Servicio;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OnboardingController extends Controller
{
    public function __construct(
        protected TenantProvisioningService $provisioningService
    ) {}

    /**
     * Pantalla de bienvenida
     */
    public function welcome()
    {
        $user = auth()->user();
        
        if (!$user || !$user->centro_id) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $centro = Centros_Medico::find($user->centro_id);

        // Si ya completó, redirigir al dashboard
        if ($centro && $centro->onboarding_completed_at) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return view('onboarding.welcome', compact('centro'));
    }

    /**
     * Paso 1: Datos del centro médico
     */
    public function stepOne()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        return view('onboarding.step-1', compact('centro'));
    }

    /**
     * Guardar datos del centro médico
     */
    public function saveStepOne(Request $request)
    {
        $validated = $request->validate([
            'nombre_centro' => 'required|string|max:255',
            'rtn' => 'required|string|max:20',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        $centro->update([
            'nombre_centro' => $validated['nombre_centro'],
            'rtn' => $validated['rtn'],
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'onboarding_current_step' => 2,
        ]);

        return redirect()->route('onboarding.step-2');
    }

    /**
     * Paso 2: Configuración CAI (Fiscal)
     */
    public function stepTwo()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        // Verificar que completó el paso anterior
        if ($centro->onboarding_current_step < 1) {
            return redirect()->route('onboarding.step-1');
        }

        return view('onboarding.step-2', compact('centro'));
    }

    /**
     * Guardar configuración CAI
     */
    public function saveStepTwo(Request $request)
    {
        $validated = $request->validate([
            'cai_codigo' => 'required|string|max:50',
            'rango_inicial' => 'required|integer|min:1',
            'rango_final' => 'required|integer|gt:rango_inicial',
            'fecha_limite' => 'required|date|after_or_equal:today',
        ]);

        $user = auth()->user();
        $centro = Centros_Medico::on('mysql')->find($user->centro_id);

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        try {
            DB::beginTransaction();

            // Asegurar que el tenant existe y está creado
            $tenant = \App\Models\Tenant::where('centro_id', $centro->id)->first();
            
            if (!$tenant) {
                // Si no existe tenant, crearlo usando el servicio de provisioning
                Log::info("Creando tenant para centro {$centro->id} durante onboarding");
                
                // Crear el tenant
                $tenant = \App\Models\Tenant::create([
                    'id' => 'centro_' . $centro->id,
                    'centro_id' => $centro->id,
                ]);
                
                // Crear base de datos del tenant
                $tenant->createDatabase();
                
                // Ejecutar migraciones en el tenant
                $tenant->run(function () {
                    artisan()->call('tenants:migrate', ['--tenants' => [tenant()->id]]);
                });
            }

            // Inicializar el contexto del tenant
            tenancy()->initialize($tenant);

            // Verificar que estamos en el contexto correcto
            if (!tenancy()->initialized) {
                throw new \Exception('No se pudo inicializar el tenant');
            }

            // Crear autorización CAI en la BD del tenant
            $cai = CAIAutorizaciones::create([
                'rtn' => $centro->rtn ?? '',
                'cai_codigo' => $validated['cai_codigo'],
                'rango_inicial' => $validated['rango_inicial'],
                'rango_final' => $validated['rango_final'],
                'numero_actual' => $validated['rango_inicial'],
                'cantidad' => ($validated['rango_final'] - $validated['rango_inicial']) + 1,
                'fecha_limite' => $validated['fecha_limite'],
                'estado' => 'ACTIVA',
            ]);

            tenancy()->end();

            // Actualizar progreso en base de datos central
            $centro->update([
                'onboarding_current_step' => 2,
                'onboarding_skipped_cai' => false,
            ]);

            DB::commit();

            return redirect()->route('onboarding.step-3')
                ->with('success', 'CAI configurado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (tenancy()->initialized) {
                tenancy()->end();
            }
            
            Log::error('Error guardando CAI en onboarding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'centro_id' => $centro->id,
                'tenant_id' => $tenant->id ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al guardar la configuración CAI: ' . $e->getMessage()]);
        }
    }

    /**
     * Saltar configuración CAI (opcional)
     */
    public function skipCai()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        $centro->update([
            'onboarding_current_step' => 3,
            'onboarding_skipped_cai' => true,
        ]);

        return redirect()->route('onboarding.step-3')
            ->with('warning', 'Has omitido la configuración CAI. Podrás configurarla después desde el panel de administración.');
    }

    /**
     * Paso 3: Servicios básicos
     */
    public function stepThree()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        // Verificar que completó el paso anterior
        if ($centro->onboarding_current_step < 2) {
            return redirect()->route('onboarding.step-2');
        }

        return view('onboarding.step-3', compact('centro'));
    }

    /**
     * Guardar servicios iniciales
     */
    public function saveStepThree(Request $request)
    {
        $validated = $request->validate([
            'servicios' => 'required|array|min:1',
            'servicios.*.nombre' => 'required|string|max:255',
            'servicios.*.precio' => 'required|numeric|min:0',
            'servicios.*.descripcion' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $centro = Centros_Medico::on('mysql')->find($user->centro_id);

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        try {
            DB::beginTransaction();

            // Asegurar que el tenant existe
            $tenant = \App\Models\Tenant::where('centro_id', $centro->id)->first();
            
            if (!$tenant) {
                // Crear tenant si fue omitido el CAI
                Log::info("Creando tenant para centro {$centro->id} en paso de servicios");
                
                $tenant = \App\Models\Tenant::create([
                    'id' => 'centro_' . $centro->id,
                    'centro_id' => $centro->id,
                ]);
                
                $tenant->createDatabase();
                
                $tenant->run(function () {
                    artisan()->call('tenants:migrate', ['--tenants' => [tenant()->id]]);
                });
            }

            // Inicializar tenant
            tenancy()->initialize($tenant);

            if (!tenancy()->initialized) {
                throw new \Exception('No se pudo inicializar el tenant');
            }

            // Crear servicios en la BD del tenant
            foreach ($validated['servicios'] as $servicioData) {
                Servicio::create([
                    'nombre' => $servicioData['nombre'],
                    'precio_unitario' => $servicioData['precio'], // Mapear 'precio' a 'precio_unitario'
                    'descripcion' => $servicioData['descripcion'] ?? null,
                ]);
            }

            tenancy()->end();

            // Actualizar progreso
            $centro->update([
                'onboarding_current_step' => 3,
            ]);

            DB::commit();

            return redirect()->route('onboarding.complete')
                ->with('success', 'Servicios creados correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (tenancy()->initialized) {
                tenancy()->end();
            }
            
            Log::error('Error guardando servicios en onboarding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'centro_id' => $centro->id,
                'tenant_id' => $tenant->id ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al guardar los servicios: ' . $e->getMessage()]);
        }
    }

    /**
     * Completar onboarding
     */
    public function complete()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        // Verificar que completó todos los pasos
        if ($centro->onboarding_current_step < 3) {
            return redirect()->route('onboarding.step-3');
        }

        return view('onboarding.completed', compact('centro'));
    }

    /**
     * Marcar onboarding como completado
     */
    public function markCompleted()
    {
        $user = auth()->user();
        $centro = Centros_Medico::find($user->centro_id);

        if (!$centro) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $centro->update([
            'onboarding_completed_at' => now(),
            'onboarding_current_step' => 5,
        ]);

        Log::info('Onboarding completado', [
            'centro_id' => $centro->id,
            'user_id' => $user->id,
        ]);

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', '¡Felicidades! Tu centro médico está configurado y listo para usar.');
    }
}
