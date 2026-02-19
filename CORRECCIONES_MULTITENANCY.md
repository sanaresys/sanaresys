# Análisis y Correcciones Sistema Multi-Tenant

## ✅ COMPLETADO

### 1. Estructura de Base de Datos
- ✅ Columna `centro_id` eliminada de 6 tablas tenant:
  - users
  - medicos
  - roles
  - especialidads
  - especialidad_medicos
  - centros_medicos_medicos

### 2. Modelos con Conexión Forzada
- ✅ `Centros_Medico`: Usa conexión 'mysql' (central)
- ✅ `Tenant`: Usa conexión 'mysql' (central)

### 3. Widgets Corregidos
- ✅ `CalendarioCitasWidget`: Filtros por centro_id eliminados
- ✅ `CitasPieChart`: Filtros por centro_id eliminados

### 4. Recursos Corregidos
- ✅ `CitasResource`: Filtros por centro_id eliminados

---

## ⚠️ PENDIENTE DE CORRECCIÓN

### Archivos que Necesitan Eliminación de Filtros `centro_id`

#### 1. **RoleResource.php** (4 referencias)
```php
// Línea 37, 47, 147
->where('centro_id', session('current_centro_id'))

// SOLUCIÓN: Eliminar estos filtros
// Los roles están en la BD del tenant, no necesitan centro_id
```

#### 2. **RecetaResource.php** (3 referencias)
```php
// Líneas 239, 257, 399
->where('centro_id', $centro_id)

// SOLUCIÓN: Eliminar filtros, usar contexto tenant
```

#### 3. **PacientesResource.php** (1 referencia)
```php
// Línea 635
$query->where('centro_id', session('current_centro_id'));

// SOLUCIÓN: Eliminar filtro
```

#### 4. **ExamenesResource.php** (2 referencias)
```php
// Líneas 215, 303
$query->where('centro_id', session('current_centro_id'));

// SOLUCIÓN: Eliminar filtros
```

#### 5. **ExamenesStatsWidget.php** (1 referencia)
```php
// Línea 21
$query->where('centro_id', session('current_centro_id'));

// SOLUCIÓN: Eliminar filtro
```

#### 6. **EnfermedadesPacienteResource.php** (2 referencias)
```php
// Líneas 47, 174
$q->where('centro_id', auth()->user()->centro_id)

// SOLUCIÓN: Eliminar filtros
```

#### 7. **ConsultasResource.php** (3 referencias)
```php
// Líneas 692-701
$query->where('centro_id', $centroActual);
$query->where('centro_id', $user->centro_id);

// SOLUCIÓN: Simplificar lógica
// Root/Admin ven todo del tenant
// Médicos ven solo sus consultas
```

#### 8. **ContratoMedicoResource.php** (3 referencias)
```php
// Líneas 41, 199, 283
->where('centro_id', $centro_id)

// SOLUCIÓN: Eliminar filtros
```

#### 9. **NominaResource Pages** (2 referencias)
```php
// EditNomina.php línea 43
// CreateNomina.php línea 45
$query->where('centro_id', $centroId);

// SOLUCIÓN: Eliminar filtros
```

#### 10. **UserResource.php** (2 referencias)
```php
// Líneas 256, 365
$q->where('centro_id', session('current_centro_id'))

// SOLUCIÓN: Eliminar filtros
// Los usuarios están en la BD del tenant
```

#### 11. **MedicoResource** (Múltiples referencias)
```php
// Líneas 206, 857, 958
->default(fn() => session('current_centro_id'))
$centro_id = session('current_centro_id')

// SOLUCIÓN: Eliminar defaults y asignaciones
// Los médicos están en la BD del tenant
```

#### 12. **CreateCitas.php** (1 referencia)
```php
// Línea 92
$data['centro_id'] = session('current_centro_id');

// SOLUCIÓN: Eliminar asignación
```

---

## 🔧 CAMBIOS EN MODELOS

### Modelos que Necesitan Actualización en `$fillable`

Eliminar `'centro_id'` de los siguientes modelos (si está en la BD tenant):

```php
// app/Models/User.php
protected $fillable = [
    'name',
    'email',
    'password',
    'persona_id',
    // 'centro_id', // ❌ ELIMINAR
];

// app/Models/Medico.php
protected $fillable = [
    'persona_id',
    'numero_colegiacion',
    // 'centro_id', // ❌ ELIMINAR
];

// app/Models/Role.php (Spatie)
// Verificar si tiene centro_id y eliminarlo

// app/Models/Especialidad.php
protected $fillable = [
    'nombre',
    // 'centro_id', // ❌ ELIMINAR
];
```

---

## 🎯 PATRONES DE CORRECCIÓN

### ❌ Antes (Incorrecto)
```php
// Filtrar por centro en contexto tenant
$query->where('centro_id', session('current_centro_id'));

// Asignar centro_id manualmente
$data['centro_id'] = auth()->user()->centro_id;

// Validar por centro
if ($user->centro_id == $centro_id) { ... }
```

### ✅ Después (Correcto)
```php
// Sin filtro - el tenant ya filtra
$query->get();

// Sin asignación - el contexto define el centro
// (nada)

// Validar que esté en el tenant correcto
// El middleware de tenancy ya lo hace
if (tenancy()->initialized) { ... }
```

---

## 📚 WIDGETS ESPECIALES

### CentroSelectorWidget
```php
// Este widget es para Root seleccionar centro
// DEBE PERMANECER pero cambiar funcionalidad:
// 1. Mostrar lista de centros (desde BD central)
// 2. Al seleccionar, inicializar ese tenant
// 3. Redirigir al dashboard del centro
```

### CentroStatsWidget
```php
// Mostrar estadísticas del tenant actual
// NO filtrar por centro_id
// Las stats automáticamente son del tenant
```

---

## 🚀 PRÓXIMOS PASOS

### 1. Actualizar Todos los Recursos Filament
- [ ] Eliminar filtros `where('centro_id')`
- [ ] Eliminar campos `centro_id` de formularios
- [ ] Actualizar `getEloquentQuery()` para confiar en tenant

### 2. Actualizar Widgets
- [ ] Eliminar filtros por `centro_id`
- [ ] Usar contexto tenant automáticamente

### 3. Actualizar Pages
- [ ] Eliminar asignaciones de `centro_id`
- [ ] Eliminar referencias a `session('current_centro_id')`

### 4. Implementar Selector de Centro para Root
- [ ] Crear middleware que inicialice tenant según sesión
- [ ] Actualizar CentroSelectorWidget para cambiar tenant
- [ ] Redirigir correctamente después de cambio

### 5. Testing
- [ ] Probar creación de datos en tenant
- [ ] Verificar aislamiento entre tenants
- [ ] Probar acceso de Root a múltiples centros
- [ ] Verificar que no hay cross-tenant data leaks

---

## 📊 ESTADO ACTUAL

- ✅ BD Central: 44 tablas
- ✅ BD Tenants: 41 tablas cada uno (SIN centro_id)
- ✅ 2 tenants activos
- ⚠️ ~50 referencias a centro_id en código que necesitan corrección
- ⚠️ Middleware de autenticación multi-tenant pendiente

---

## 💡 RECOMENDACIÓN

**Prioridad Alta:**
1. Corregir filtros en recursos principales (Citas, Consultas, Pacientes, Médicos)
2. Eliminar campo centro_id de formularios Filament
3. Implementar selector de centro funcional para Root

**Prioridad Media:**
4. Corregir widgets de estadísticas
5. Actualizar páginas personalizadas

**Prioridad Baja:**
6. Refactorizar código legacy
7. Optimizar queries multi-tenant
