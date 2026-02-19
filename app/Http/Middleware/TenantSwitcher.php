<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        $centroId = $request->get('centro_id') ?? session('current_centro_id');
        
        if ($centroId && auth()->check()) {
            $user = auth()->user();
            
            if (!$user->canAccessCentro($centroId)) {
                abort(403, 'No tiene permiso para acceder a este centro médico');
            }
            
            $tenant = Tenant::where('centro_id', $centroId)->first();
            
            if (!$tenant) {
                abort(404, 'Centro médico no encontrado');
            }
            
            try {
                // Inicializar tenant con Stancl
                tenancy()->initialize($tenant);
                session(['current_centro_id' => $centroId]);
                
                Log::info("Tenant inicializado", [
                    'user_id' => $user->id,
                    'centro_id' => $centroId,
                    'tenant_id' => $tenant->id,
                    'database' => $tenant->database()->getName()
                ]);
                
            } catch (\Exception $e) {
                Log::error("Error inicializando tenant: " . $e->getMessage());
                abort(500, 'Error al cambiar de centro médico');
            }
            
        } else if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->centro_id && !$user->hasRole('root')) {
                $tenant = Tenant::where('centro_id', $user->centro_id)->first();
                
                if ($tenant) {
                    tenancy()->initialize($tenant);
                    session(['current_centro_id' => $user->centro_id]);
                }
            }
        }

        return $next($request);
    }
}
