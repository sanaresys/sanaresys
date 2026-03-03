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
     * Obtener el centro médico del tenant actual
     */
    protected function getCentroFromTenant()
    {
        $tenant = tenancy()->tenant;
        
        if (!$tenant || !$tenant->centro_id) {
            return null;
        }
        
        return Centros_Medico::on('mysql')->find($tenant->centro_id);
    }

    /**
     * Pantalla de bienvenida
     */
    public function welcome()
    {
        $centro = $this->getCentroFromTenant();
        
        if (!$centro) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

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
        $centro = $this->getCentroFromTenant();

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

        $centro = $this->getCentroFromTenant();

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        $centro->update([
            'nombre_centro' => $validated['nombre_centro'],
            'rtn' => $validated['rtn'],
            'direccion' => $validated['direccion'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'] ?? null,
            'onboarding_current_step' => 2,
        ]);

        return redirect()->route('onboarding.step-2');
    }

    /**
     * Paso 2: Configuración CAI (Fiscal)
     */
    public function stepTwo()
    {
        $centro = $this->getCentroFromTenant();

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

        $centro = $this->getCentroFromTenant();

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        try {
            // Asegurar que el tenant existe
            $tenant = \App\Models\Tenant::where('centro_id', $centro->id)->first();
            
            if (!$tenant) {
                Log::error('Tenant no encontrado para centro', ['centro_id' => $centro->id]);
                throw new \Exception('No se encontró el tenant. Por favor contacta al soporte.');
            }

            // Inicializar el contexto del tenant ANTES de iniciar transacción
            tenancy()->initialize($tenant);

            if (!tenancy()->initialized) {
                throw new \Exception('No se pudo inicializar el tenant');
            }

            // Ahora la transacción será en la BD del tenant
            DB::beginTransaction();

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

            DB::commit();
            tenancy()->end();

            // Actualizar progreso en base de datos central
            $centro->update([
                'onboarding_current_step' => 2,
                'onboarding_skipped_cai' => false,
            ]);

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
        $centro = $this->getCentroFromTenant();

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
        $centro = $this->getCentroFromTenant();

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

        $centro = $this->getCentroFromTenant();

        if (!$centro) {
            return back()->withErrors(['error' => 'Centro médico no encontrado.']);
        }

        try {
            // Asegurar que el tenant existe
            $tenant = \App\Models\Tenant::where('centro_id', $centro->id)->first();
            
            if (!$tenant) {
                Log::error('Tenant no encontrado para centro', ['centro_id' => $centro->id]);
                throw new \Exception('No se encontró el tenant. Por favor contacta al soporte.');
            }

            // Inicializar tenant ANTES de la transacción
            tenancy()->initialize($tenant);

            if (!tenancy()->initialized) {
                throw new \Exception('No se pudo inicializar el tenant');
            }

            // Transacción en la BD del tenant
            DB::beginTransaction();

            // Crear servicios en la BD del tenant
            foreach ($validated['servicios'] as $servicioData) {
                Servicio::create([
                    'nombre' => $servicioData['nombre'],
                    'precio_unitario' => $servicioData['precio'],
                    'descripcion' => $servicioData['descripcion'] ?? null,
                ]);
            }

            DB::commit();
            tenancy()->end();

            // Actualizar progreso en BD central
            $centro->update([
                'onboarding_current_step' => 3,
            ]);

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
        $centro = $this->getCentroFromTenant();

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
        $centro = $this->getCentroFromTenant();

        if (!$centro) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $centro->update([
            'onboarding_completed_at' => now(),
            'onboarding_current_step' => 5,
        ]);

        Log::info('Onboarding completado', [
            'centro_id' => $centro->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', '¡Felicidades! Tu centro médico está configurado y listo para usar.');
    }
}
