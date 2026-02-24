# Multi-Tenancy Domain-Only (Sanare)

Fecha de actualizacion: 23-Feb-2026

## 1. Estado actual

La plataforma opera en modo **domain-only**:

- Cada clinica tenant se resuelve **solo por host** (tabla `domains`).
- El acceso de `root` a una clinica se hace **solo por impersonacion firmada**.
- No existe cambio de tenant por sesion ni por query params.

Legacy (`tenancy_mode=legacy`) se considera deuda tecnica operativa y debe purgarse con comando dedicado.

## 2. Flujo root -> tenant (unico flujo valido)

Endpoint central:

- `POST /portal/root/tenant/{centro}/entrar`

Reglas:

- Solo acepta centros con `tenancy_mode=domain`.
- Requiere tenant existente para el centro.
- Requiere dominio primario (`primary_domain`) configurado.
- Si falla validacion, responde `422` (sin fallback).

Resultado:

- Genera token de impersonacion tenant.
- Redirige a `http(s)://{domain}/tenant/impersonate/{token}`.
- Inicia sesion en el tenant como usuario `administrador` (fallback: primer usuario tenant).

Implementacion:

- `app/Http/Controllers/RootPortalController.php`
- `routes/central.php`
- `app/Http/Controllers/TenantImpersonationController.php`

## 3. Selector de centros en topbar (root)

El selector ya no usa `?switch_centro` ni sesion central:

- Lista solo centros `tenancy_mode=domain`.
- Cada opcion envia `POST` con CSRF a `portal.root.enter-tenant`.
- Visibilidad restringida a `root`.

Implementacion:

- `app/Livewire/CentroSelector.php`
- `resources/views/livewire/centro-selector.blade.php`
- `resources/views/filament/components/centro-selector-topbar.blade.php`

## 4. Resolucion de tenant y middleware

Resolucion tenant:

- `app/Http/Middleware/ResolveTenantByHostOrLegacyCentro.php`

Comportamiento:

- Inicializa tenant por host (`domains`).
- Host no central y sin tenant valido -> `404`.
- No escribe `current_centro_id` ni usa sesion para cambiar contexto.

Alias y stack:

- `bootstrap/app.php`: aliases tenancy activos (`tenant.resolve`, `tenant.canonical`, etc.).
- `app/Providers/Filament/AdminPanelProvider.php`: sin middleware legacy de cambio por sesion.

## 5. Parametros legacy removidos

Los siguientes query params **no gobiernan tenancy**:

- `switch_centro`
- `centro_id`

El tenant activo depende del host y del flujo de impersonacion.

## 6. Purga de legacy

Comando:

- `php artisan tenancy:purge-legacy`
- `php artisan tenancy:purge-legacy --force`

Comportamiento:

- Sin `--force`: dry-run (resumen de centros legacy, tenants legacy y filas candidatas por tabla central con `centro_id`).
- Con `--force`:
1. Borra datos centrales relacionados por `centro_id` (ordenados por dependencias FK entre tablas candidatas).
2. Elimina tenants legacy con `$tenant->delete()` (dispara pipeline de borrado de DB tenant).
3. Elimina centros legacy (`forceDelete`).
4. Verifica que no queden centros legacy ni schemas legacy detectados.

Implementacion:

- `app/Console/Commands/PurgeLegacyTenancyCommand.php`

## 7. Verificaciones recomendadas

Rutas/middlewares:

```bash
php artisan route:list --path=portal/root -vv
php artisan route:list --path=admin/login -vv
```

Busqueda de residuos legacy:

```bash
rg "current_centro_id|switch_centro|tenant.switcher|centro.switch" app bootstrap routes resources
rg "TenantScoped|CentroScope|HasCentroScope" app
```

Purga:

```bash
php artisan tenancy:purge-legacy
php artisan tenancy:purge-legacy --force
```

## 8. Requisitos de entorno (local)

Variables recomendadas:

```env
APP_URL=http://sanaresys.localhost
TENANCY_BASE_DOMAIN=sanaresys.localhost
TENANCY_CENTRAL_DOMAINS=sanaresys.localhost,localhost,127.0.0.1
TENANCY_TENANT_SCHEME=http
```

Notas:

- El dominio central se usa para autenticacion root y portal.
- Los tenants deben abrirse por subdominio valido del base domain.
