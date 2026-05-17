# Analisis integral del sistema Sanaresys

Fecha: 2026-03-23
Objetivo: consolidar conocimiento funcional y tecnico del sistema antes de aplicar cambios.

## 1) Stack y arquitectura base

- Framework: Laravel 12.19.3 (PHP 8.2)
- Admin panel: Filament v3
- Multitenancy: stancl/tenancy v3.9
- Permisos: spatie/laravel-permission
- PDF: barryvdh/laravel-dompdf
- Frontend build: Vite + Tailwind

### Modelo de tenancy

- Modo operativo principal: domain-only
- BD central (conexion mysql): centros, tenants, flujos globales
- BD tenant por clinica: datos operativos clinicos/administrativos
- Resolucion tenant por host (dominio/subdominio) con middleware dedicado

## 2) Capa de enrutamiento

### Rutas centrales

- Archivo de rutas central: routes/central.php
- Cargadas por: App\Providers\CentralRoutesServiceProvider
- Proteccion de dominio central: middleware central.domain

Flujos centrales identificados:
- Registro de clinica (solicitud, espera, reenvio, verificacion, exito)
- Portal root para entrar a tenants via impersonacion

### Rutas tenant

- Archivo: routes/tenant.php
- Cargadas por: App\Providers\TenancyServiceProvider
- Incluye endpoint de impersonacion tenant

### Rutas web generales

- Archivo: routes/web.php
- Incluye onboarding y endpoints de impresion/PDF

## 3) Flujo de negocio principal (alta de clinica)

Controlador: ClinicRegistrationController

1. Usuario registra clinica en dominio central
2. Se genera solicitud pendiente de verificacion por correo
3. Verificacion firmada del enlace
4. Provision del tenant:
   - crea/actualiza tenant
   - configura dominio primario
   - inicializa tenant
   - siembra baseline de ACL/catalogos
   - crea usuario admin del tenant
5. Se emite token de impersonacion y redirige al tenant
6. Tenant continua con onboarding por pasos

## 4) Flujo root -> tenant

Controladores: RootPortalController + TenantImpersonationController

- Root selecciona centro en portal central
- Se valida modo domain y dominio primario
- Se genera token de impersonacion
- Redireccion a dominio del tenant con token
- Inicio de sesion en tenant como usuario objetivo

## 5) Onboarding del tenant

Controlador: OnboardingController

Pasos:
- Paso 1: datos del centro
- Paso 2: CAI (opcional)
- Paso 3: servicios iniciales
- Finalizacion: marca onboarding_completed_at

Caracteristicas:
- Inicializacion tenant explicita para operaciones tenant en CAI/servicios
- Actualizacion de progreso en central

## 6) Modulos funcionales detectados

- Pacientes
- Medicos
- Consultas
- Citas
- Recetas y recetarios
- Examenes
- Facturacion
- Pagos y cuentas por cobrar
- Contabilidad medica (contratos, nomina, liquidaciones/pagos honorarios)
- Roles y permisos
- Dashboard/widgets operativos

## 7) Servicios de dominio clave

- TenantIdentityService: slug, validaciones, dominio primario
- TenantProvisioningService: provision tenant + admin + baseline
- TenantIdentityRenameService: cambio de identidad tenant al renombrar centro
- FacturaPagoService: registra pagos y recalcula estado/saldo
- ComisionMedicoService: calcula comision por periodo

## 8) Hallazgos tecnicos importantes (riesgo)

### Criticos

1. Duplicidad de clase/modelo PagosFactura en dos archivos:
   - app/Models/PagosFactura.php
   - app/Models/pagos_factura.php
   Riesgo: conflicto/autoload ambiguo en entornos sensibles a case.

2. Metodo mal nombrado en Persona:
   - protected static function bootedd()
   Riesgo: callbacks de limpieza de fotografia no se ejecutan.

3. Duplicidad de ruta exacta de receta:
   - /receta/{receta}/imprimir definida dos veces con nombres distintos
   Riesgo: mantenimiento/confusion de resolucion y nombre de ruta.

### Altos

4. Inconsistencias legacy vs domain-only en varias piezas (referencias centro_id en modelos/recursos).

5. FacturaController presenta señales de deuda tecnica:
   - mezcla de estructuras de datos duplicadas
   - claves repetidas dentro de arreglos
   - codigo aparentemente no alcanzable
   Esto puede provocar comportamiento inesperado en PDF/preview.

6. Factura incluye relacion self-referential factura() dentro del propio modelo, probablemente residual.

### Medios

7. Evidencia de texto con codificacion irregular (caracteres corruptos) en comentarios/cadenas.

8. Conjunto amplio de scripts de diagnostico/fix fuera de pipelines formales.
   Ventaja: soporte operativo.
   Riesgo: drift entre scripts y codigo productivo.

## 9) Estado operativo observado

- La app arranca y lista rutas correctamente.
- Se observaron 139 rutas registradas (incluyendo Filament + central + tenant).
- Inventario de migraciones:
  - central: 43 archivos
  - tenant: 38 archivos

## 10) Recomendaciones para la siguiente fase (cuando iniciemos cambios)

1. Fase de saneamiento estructural
- Unificar PagosFactura en un solo archivo.
- Corregir bootedd -> booted en Persona.
- Eliminar ruta duplicada de receta.
- Revisar y simplificar FacturaController para dejar un flujo unico y consistente.

2. Fase de coherencia tenancy
- Completar depuracion de referencias legacy centro_id donde ya no aplica.
- Definir contrato claro: que queda en central y que queda solo en tenant.

3. Fase de endurecimiento funcional
- Reforzar pruebas feature del flujo registro->provision->impersonacion->onboarding.
- Agregar chequeos de regresion para facturacion/PDF y pagos.

---

Este documento resume el estado actual del sistema y sirve como base para ejecutar cambios de forma controlada en la siguiente etapa.
