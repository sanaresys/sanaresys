# Sistema Multi-Tenant - Guía de Uso

## 📋 Resumen

Sistema multi-tenant implementado con **base de datos por centro médico** usando Stancl/Tenancy v3.x para Laravel.

## 🏗️ Arquitectura

### Base de Datos Central (`db_clinica`)
- **44 tablas** con las migraciones centrales
- Contiene:
  - `centros_medicos`: Catálogo de centros médicos
  - `tenants`: Registro de tenants activos
  - `users`, `personas`, `nacionalidades`, etc. (datos compartidos opcionales)

### Bases de Datos Tenant (`centro_1`, `centro_2`, ...)
- **41 tablas** cada una
- Una BD completa por cada centro médico
- Contiene duplicado de todas las tablas excepto `centros_medicos` y `tenants`
- **Sin FK a centros_medicos** (aislamiento completo)

## 🔄 Flujo de Creación de Tenant

1. Se crea un `Centros_Medico` en la aplicación
2. El `CentroMedicoObserver` detecta la creación
3. Crea un registro en la tabla `tenants` con ID `centro_X`
4. El `TenancyServiceProvider` ejecuta el JobPipeline:
   - **CreateDatabase**: Crea la BD `centro_X`
   - **MigrateDatabase**: Ejecuta las 37 migraciones tenant

Todo esto ocurre **automáticamente** sin intervención manual.

## 📁 Estructura de Archivos

```
database/migrations/           (39 migraciones centrales)
├── centros_medicos_table.php
├── tenants_table.php
└── ... (tablas compartidas)

database/migrations/tenant/    (37 migraciones tenant)
├── users_table.php
├── personas_table.php
├── medicos_table.php
├── pacientes_table.php
├── citas_table.php
└── ... (todas excepto centros_medicos y tenants)
```

## 💻 Uso en el Código

### Consultar Datos de un Tenant Específico

```php
use App\Models\Tenant;

// Inicializar un tenant
$tenant = Tenant::find('centro_1');
tenancy()->initialize($tenant);

// Ahora todas las consultas van a la BD centro_1
$usuarios = \DB::connection('tenant')->table('users')->get();
$medicos = \DB::connection('tenant')->table('medicos')->get();

// Finalizar tenancy
tenancy()->end();
```

### Panel Admin: Consultar Todos los Tenants

```php
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

$tenants = Tenant::all();
$todasLasCitas = [];

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);
    
    $citas = DB::connection('tenant')
        ->table('citas')
        ->join('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
        ->join('medicos', 'citas.medico_id', '=', 'medicos.id')
        ->select('citas.*', 'pacientes.persona_id', 'medicos.numero_colegiacion')
        ->get();
    
    // Agregar centro_id para identificar de qué centro vienen
    $citas = $citas->map(function($cita) use ($tenant) {
        $cita->centro_id = $tenant->centro_id;
        $cita->centro_nombre = $tenant->id;
        return $cita;
    });
    
    $todasLasCitas = array_merge($todasLasCitas, $citas->toArray());
    
    tenancy()->end();
}

// Ahora $todasLasCitas tiene las citas de todos los centros
return view('admin.dashboard', ['citas' => $todasLasCitas]);
```

### Crear Nuevo Centro (Automático)

```php
use App\Models\Centros_Medico;

// Simplemente crea el centro
$centro = Centros_Medico::create([
    'nombre_centro' => 'Nueva Clínica',
    'direccion' => 'Dirección...',
    'telefono' => '2222-3333',
    'email' => 'info@nuevaclinica.hn',
    'rtn' => '08019876543219',
]);

// El tenant se crea automáticamente
// La BD se crea automáticamente
// Las migraciones se ejecutan automáticamente
```

## 🔧 Comandos Útiles

### Migrar Sistema Completo
```bash
php clean_tenant_databases.php  # Limpiar BDs tenant existentes
php artisan migrate:fresh --seed # Migrar central + crear tenants automáticos
```

### Crear Tenant Manualmente
```bash
php artisan tenants:migrate --tenants=centro_1
```

### Verificar Estado
```bash
php verificar_estructura_multitenancy.php
php resumen_sistema_multitenancy.php
```

## ✅ Características Implementadas

- ✅ **Aislamiento completo**: Cada centro tiene su propia BD
- ✅ **Creación automática**: Al crear centro, se crea tenant
- ✅ **Sin FK cruzadas**: No hay FK entre tenant y central
- ✅ **37 migraciones tenant**: Esquema completo duplicado
- ✅ **Panel admin funcional**: Puede consultar todos los tenants
- ✅ **Datos segregados**: Imposibilidad de acceso cruzado accidental

## 🎯 Ventajas de Esta Arquitectura

1. **Seguridad**: Datos completamente aislados
2. **Performance**: No hay filtrado por centro_id, la BD ya está filtrada
3. **Escalabilidad**: Cada centro puede crecer independientemente
4. **Backups selectivos**: Puedes respaldar centros individuales
5. **Regulación**: Cumple con requisitos de segregación de datos

## ⚠️ Consideraciones

1. **Agregación de datos**: Requiere iterar sobre todos los tenants
2. **Migraciones**: Hay que ejecutarlas en central Y tenant
3. **Datos compartidos**: Nacionalidades, especialidades deben duplicarse
4. **Recursos**: Más BDs = más uso de recursos del servidor

## 📊 Estado Actual

- **3 centros médicos** registrados
- **3 tenants activos** (centro_1, centro_2, centro_3)
- **44 tablas** en BD central
- **41 tablas** por cada tenant
- **Datos de prueba** insertados en todos los tenants
