# Pagos PayPal y Estado de Suscripcion por Clinica

Fecha de actualizacion: 07-Mar-2026

## 1. Resumen general de los cambios

Se implemento un modulo de billing por suscripcion con PayPal para el alta y operacion de clinicas tenant en modo `domain`.

Objetivo principal:

1. Obligar pago de plan antes de aprovisionar tenant.
2. Sincronizar estado de suscripcion de PayPal hacia base de datos central.
3. Bloquear acceso administrativo cuando la suscripcion de la clinica este inactiva.
4. Dar al usuario root herramientas de monitoreo y override manual con auditoria.
5. Incluir un comando de reset controlado para limpiar clinicas/tenants/billing.

Funcionalidades nuevas:

1. Registro con seleccion de plan (`monthly` / `annual`) y cobro recurrente por PayPal.
2. Estado intermedio de registro `pending_payment`.
3. Webhook PayPal idempotente con validacion de firma.
4. Snapshot de billing por clinica (`billing_status`, plan, renovacion, override).
5. Reactivacion self-service desde tenant inactivo.
6. Vista root central de estado de suscripciones y acciones de override.

Tambien se corrigio un problema previo en Filament:

1. `app/Filament/Resources/FacturaDetalles/FacturaDetallesResource.php` fue corregido para declarar una clase `Resource` valida, eliminando el conflicto de clase duplicada.

## 2. Cambios en la arquitectura del sistema

Se agrego una capa de servicios de billing en `app/Services/Billing` para separar responsabilidades:

1. Catalogo de planes.
2. Integracion HTTP con PayPal.
3. Persistencia y normalizacion de suscripciones.
4. Proyeccion de estado de facturacion sobre `centros_medicos`.
5. Aprovisionamiento condicionado a pago activo.

Componentes arquitectonicos agregados:

1. Servicios de billing:
   - `BillingPlanService`
   - `PayPalService`
   - `BillingSubscriptionService`
   - `BillingStateService`
   - `RegistrationProvisioningService`
2. Controladores nuevos:
   - `PayPalWebhookController`
   - `TenantBillingController`
3. Middleware nuevo:
   - `EnsureTenantSubscriptionActive`
4. Modelos nuevos:
   - `BillingSubscription`
   - `BillingWebhookEvent`
   - `BillingOverrideAudit`
5. Configuracion nueva:
   - `config/billing.php`
6. Comando de mantenimiento:
   - `tenancy:reset-clinics`

Cambios de acoplamiento:

1. `ClinicRegistrationController` ahora depende de servicios de billing y separa flujo de verificacion vs pago vs provision.
2. `RootPortalController` consume `BillingStateService` para aplicar overrides.
3. Filament panel incluye middleware `tenant.subscription.active`.

## 3. Nuevos archivos creados

### 3.1 Servicios

1. `app/Services/Billing/BillingPlanService.php`
   - Proposito: resolver planes y validar `plan_code`.
   - Funcionalidad: obtiene plan por codigo, plan por defecto y PayPal Plan ID configurado.
   - Integracion: usado en registro central y reactivacion tenant.

2. `app/Services/Billing/PayPalService.php`
   - Proposito: encapsular llamadas a API PayPal.
   - Funcionalidad: crear suscripcion, consultar suscripcion, validar firma webhook, normalizar estado.
   - Integracion: usado por controladores de registro, webhook y reactivacion.

3. `app/Services/Billing/BillingSubscriptionService.php`
   - Proposito: sincronizar datos de suscripcion con BD local.
   - Funcionalidad: upsert por `paypal_subscription_id`, mapeo de periodos y estado.
   - Integracion: actualiza `billing_subscriptions` y snapshot de centro via `BillingStateService`.

4. `app/Services/Billing/BillingStateService.php`
   - Proposito: calcular estado efectivo de facturacion por centro.
   - Funcionalidad: sync de snapshot, refresh por ultima suscripcion, aplicacion de override y auditoria.
   - Integracion: usado por sync de suscripciones y portal root.

5. `app/Services/Billing/RegistrationProvisioningService.php`
   - Proposito: aprovisionar tenant solo con pago activo.
   - Funcionalidad: crea centro, tenant, admin, token de impersonacion y vincula suscripcion.
   - Integracion: invocado por retorno PayPal y webhook.

### 3.2 Controladores y middleware

6. `app/Http/Controllers/PayPalWebhookController.php`
   - Proposito: procesar webhooks de PayPal.
   - Funcionalidad: idempotencia por `event_id`, validacion de firma, sync de estado.
   - Integracion: endpoint `POST /webhooks/paypal`.

7. `app/Http/Controllers/TenantBillingController.php`
   - Proposito: reactivacion para tenant inactivo.
   - Funcionalidad: pantalla de bloqueo, inicio checkout, retorno y cancelacion.
   - Integracion: rutas `tenant.billing.*`.

8. `app/Http/Middleware/EnsureTenantSubscriptionActive.php`
   - Proposito: bloquear rutas administrativas de tenant si suscripcion esta inactiva.
   - Funcionalidad: redirige a `tenant.billing.inactive` o login.
   - Integracion: alias `tenant.subscription.active` en `bootstrap/app.php`.

### 3.3 Modelos

9. `app/Models/BillingSubscription.php`
   - Proposito: representar suscripcion sincronizada desde proveedor.
   - Funcionalidad: casts de fechas/monto/meta, relaciones con centro y solicitud.
   - Integracion: fuente para snapshot billing en `centros_medicos`.

10. `app/Models/BillingWebhookEvent.php`
    - Proposito: almacenar eventos webhook y su estado de procesamiento.
    - Funcionalidad: tracking de `processed`, `failed`, `ignored`.
    - Integracion: usado por `PayPalWebhookController` para idempotencia.

11. `app/Models/BillingOverrideAudit.php`
    - Proposito: auditar cambios manuales de override root.
    - Funcionalidad: guarda override anterior/nuevo, motivo y metadata.
    - Integracion: usado por `BillingStateService`.

### 3.4 Configuracion, migraciones, vistas y tests

12. `config/billing.php`
13. `database/migrations/2026_03_07_000001_add_billing_fields_to_clinic_registration_requests_table.php`
14. `database/migrations/2026_03_07_000002_create_billing_subscriptions_table.php`
15. `database/migrations/2026_03_07_000003_create_billing_webhook_events_table.php`
16. `database/migrations/2026_03_07_000004_create_billing_override_audits_table.php`
17. `database/migrations/2026_03_07_000005_add_billing_snapshot_fields_to_centros_medicos_table.php`
18. `resources/views/tenant-billing-inactive.blade.php`
19. `app/Console/Commands/ResetClinicsCommand.php`
20. `tests/Feature/PayPalWebhookControllerTest.php`

## 4. Archivos modificados

### 4.1 Configuracion y rutas

1. `.env.example`
   - Cambio: variables `PAYPAL_MODE`, `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`, `PAYPAL_PLAN_MONTHLY_ID`, `PAYPAL_PLAN_ANNUAL_ID`.
   - Problema que resuelve: estandarizar setup de entorno.
   - Impacto: facilita despliegue/instalacion.

2. `config/services.php`
   - Cambio: seccion `paypal`.
   - Problema que resuelve: centralizar credenciales PayPal.
   - Impacto: `PayPalService` obtiene config desde servicio oficial de Laravel.

3. `routes/central.php`
   - Cambio: rutas de inicio pago, retorno, cancelacion, webhook y override root.
   - Problema que resuelve: faltaban endpoints de flujo de cobro.
   - Impacto: onboarding completo con pasarela.

4. `routes/tenant.php`
   - Cambio: rutas de reactivacion billing en tenant.
   - Problema que resuelve: no habia autoservicio de reactivacion.
   - Impacto: tenants inactivos pueden reactivar sin soporte manual.

### 4.2 Backend y control de acceso

5. `app/Http/Controllers/ClinicRegistrationController.php`
   - Cambio: flujo dividido en registro/verificacion/pago/provision.
   - Problema que resuelve: provisionamiento sin pago previo.
   - Impacto: alta de clinica condicionada por suscripcion activa.

6. `app/Http/Controllers/RootPortalController.php`
   - Cambio: carga de datos billing y metodo `setBillingOverride`.
   - Problema que resuelve: falta de control manual root.
   - Impacto: root puede forzar estado con motivo auditado.

7. `app/Http/Responses/LoginResponse.php`
   - Cambio: redirigir a `tenant.billing.inactive` si centro inactivo.
   - Problema que resuelve: acceso a admin sin suscripcion vigente.
   - Impacto: enforcement temprano en login tenant.

8. `app/Providers/Filament/AdminPanelProvider.php`
   - Cambio: middleware `tenant.subscription.active` en stack panel.
   - Problema que resuelve: enforcement incompleto dentro del panel.
   - Impacto: acceso administrativo consistente con estado billing.

9. `bootstrap/app.php`
   - Cambio: registro alias `tenant.subscription.active` y excepcion CSRF para `webhooks/paypal`.
   - Problema que resuelve: webhooks externos no envian CSRF token.
   - Impacto: webhook funcional y middleware disponible.

### 4.3 Modelos

10. `app/Models/ClinicRegistrationRequest.php`
    - Cambio: nuevos campos billing, estado `pending_payment`, relacion con suscripciones.
    - Problema que resuelve: modelo no representaba ciclo de pago.
    - Impacto: tracking granular de onboarding + cobro.

11. `app/Models/Centros_Medico.php`
    - Cambio: campos snapshot billing, relacion con `billingSubscriptions`, helper `isBillingActive`.
    - Problema que resuelve: centro no tenia estado operativo de suscripcion.
    - Impacto: consultas rapidas sin joins complejos.

### 4.4 Frontend

12. `resources/views/welcome.blade.php`
13. `resources/views/registro-clinica.blade.php`
14. `resources/views/registro-clinica-waiting.blade.php`
15. `resources/views/root-portal.blade.php`
    - Cambio: UX alineada al nuevo flujo de pago y monitoreo.
    - Impacto: usuario ve estado real, plan, acciones de pago/override.

### 4.5 Testing y correccion tecnica adicional

16. `tests/Feature/ClinicRegistrationEmailVerificationTest.php`
    - Cambio: actualizado al flujo con PayPal y `pending_payment`.
    - Impacto: cobertura de flujo central de onboarding.

17. `app/Filament/Resources/FacturaDetalles/FacturaDetallesResource.php`
    - Cambio: clase corregida a Resource valida.
    - Problema que resuelve: error fatal de clase duplicada y carga de panel.
    - Impacto: estabilidad del panel Filament.

## 5. Cambios en la base de datos

### 5.1 Migraciones agregadas

1. `2026_03_07_000001_add_billing_fields_to_clinic_registration_requests_table`
   - Nuevas columnas:
     - `plan_code`
     - `payment_status` (default `pending`)
     - `paypal_subscription_id` (index)
     - `paypal_plan_id`
     - `payment_approved_at`
   - Proposito: estado de pago y trazabilidad de suscripcion durante onboarding.

2. `2026_03_07_000002_create_billing_subscriptions_table`
   - Nueva tabla `billing_subscriptions`.
   - Relaciona opcionalmente con:
     - `centros_medicos`
     - `clinic_registration_requests`
   - Guarda estado normalizado, estado proveedor, periodos, monto, metadata.

3. `2026_03_07_000003_create_billing_webhook_events_table`
   - Nueva tabla `billing_webhook_events`.
   - Clave unica: `provider + event_id`.
   - Proposito: idempotencia y auditoria de webhooks.

4. `2026_03_07_000004_create_billing_override_audits_table`
   - Nueva tabla `billing_override_audits`.
   - FK a `centros_medicos` y `users`.
   - Proposito: trazabilidad de cambios manuales de estado.

5. `2026_03_07_000005_add_billing_snapshot_fields_to_centros_medicos_table`
   - Nuevas columnas:
     - `billing_status` (default `inactive`)
     - `billing_plan_code`
     - `billing_renews_at`
     - `billing_last_sync_at`
     - `billing_override`
   - Proposito: consulta rapida del estado operativo de clinica.

### 5.2 Relaciones y modelo de datos

1. `centros_medicos` 1:N `billing_subscriptions`.
2. `clinic_registration_requests` 1:N `billing_subscriptions`.
3. `centros_medicos` 1:N `billing_override_audits`.
4. `users` 1:N `billing_override_audits` (usuario que aplico override).
5. `billing_webhook_events` funciona como inbox de eventos externos sin FK directa.

## 6. Cambios en la logica de negocio

### 6.1 Flujo de registro y pago

1. En registro se valida `plan_code` y se asigna `paypal_plan_id`.
2. Estado inicial: `pending_verification`.
3. Al verificar correo: `verified`.
4. Se crea suscripcion en PayPal y pasa a `pending_payment`.
5. Al retorno PayPal se consulta suscripcion y se sincroniza.
6. Si estado normalizado es `active`, se ejecuta provision.
7. Si falla checkout, solicitud pasa a `failed` con `failure_code`.

### 6.2 Provision condicionado

1. `RegistrationProvisioningService` solo provisiona cuando `payment_status=active`.
2. Evita provision duplicada si ya esta `provisioned`.
3. Limpia `password_encrypted` al finalizar.
4. Vincula `billing_subscriptions` al centro creado.

### 6.3 Sincronizacion de estado operativo

1. `BillingSubscriptionService` guarda/actualiza suscripcion local.
2. `BillingStateService` actualiza snapshot en `centros_medicos`.
3. `billing_override` tiene precedencia sobre estado derivado del proveedor.

### 6.4 Reactivacion tenant inactivo

1. Usuario autenticado entra a pantalla de bloqueo.
2. Selecciona plan y lanza checkout de reactivacion.
3. Retorno PayPal sincroniza estado.
4. Si queda activo, recupera acceso a `/admin`.

## 7. Integraciones externas implementadas

Servicio integrado: PayPal Subscriptions API.

### 7.1 Proposito

1. Crear suscripciones recurrentes para alta y reactivacion.
2. Consultar estado real de suscripcion.
3. Verificar autenticidad de webhooks.

### 7.2 Endpoints PayPal consumidos

1. `POST /v1/oauth2/token`
2. `POST /v1/billing/subscriptions`
3. `GET /v1/billing/subscriptions/{id}`
4. `POST /v1/notifications/verify-webhook-signature`

### 7.3 Interaccion del sistema con PayPal

1. Genera token OAuth con `client_id/client_secret`.
2. Crea suscripcion con `plan_id`, `custom_id`, `return_url`, `cancel_url`.
3. Redirige usuario a `approve_url`.
4. Al retorno y en webhook, consulta suscripcion para sincronizar estado local.
5. Registra eventos en `billing_webhook_events` para idempotencia y observabilidad.

## 8. Cambios en rutas y endpoints

### 8.1 Backend central

1. `GET /registro-clinica` (`clinica.registro`)
2. `POST /registro-clinica` (`clinica.registro.store`)
3. `GET /registro-clinica/esperando/{publicId}` (`clinica.registro.waiting`)
4. `POST /registro-clinica/esperando/{publicId}/reenviar` (`clinica.registro.resend`)
5. `GET /registro-clinica/verificar/{publicId}` (`clinica.registro.verify`)
6. `POST /registro-clinica/{publicId}/pago` (`clinica.registro.payment.start`)
7. `GET /registro-clinica/{publicId}/pago/retorno` (`clinica.registro.payment.return`)
8. `GET /registro-clinica/{publicId}/pago/cancelar` (`clinica.registro.payment.cancel`)
9. `POST /webhooks/paypal` (`webhooks.paypal`)
10. `GET /portal/root` (`portal.root`)
11. `POST /portal/root/tenant/{centro}/entrar` (`portal.root.enter-tenant`)
12. `POST /portal/root/tenant/{centro}/billing-override` (`portal.root.billing-override`)

### 8.2 Backend tenant

1. `GET /billing/inactive` (`tenant.billing.inactive`)
2. `POST /billing/reactivate` (`tenant.billing.reactivate`)
3. `GET /billing/reactivate/return` (`tenant.billing.reactivate.return`)
4. `GET /billing/reactivate/cancel` (`tenant.billing.reactivate.cancel`)

## 9. Cambios en el frontend

### 9.1 Landing (`welcome`)

1. Eliminacion de plan gratuito.
2. Botones de planes mensual/anual apuntan a registro con query `plan`.
3. Copy actualizado para indicar disponibilidad de PayPal.

### 9.2 Formulario de registro

1. Muestra plan preseleccionado.
2. Permite cambiar plan en selector.
3. Copy del flujo actualizado: registro -> verificacion -> pago PayPal -> activacion.

### 9.3 Pantalla de seguimiento de registro

1. Separa estado de registro y estado de pago.
2. Muestra plan y `paypal_subscription_id`.
3. Agrega boton `Ir a PayPal` cuando aplica.

### 9.4 Portal root

1. Tabla central con:
   - estado activo/inactivo
   - plan
   - renovacion
   - dias restantes
   - ultimo sync
2. Acciones:
   - entrar al tenant
   - forzar activo
   - forzar inactivo
   - quitar override
   - motivo obligatorio

### 9.5 Pantalla tenant inactivo

1. Resume estado actual y plan.
2. Permite reactivar con checkout PayPal.
3. Mantiene opcion de cierre de sesion.

## 10. Flujo funcional actualizado del sistema

1. Usuario selecciona plan en landing.
2. Completa formulario de registro.
3. Sistema crea solicitud `pending_verification`.
4. Se envia correo con enlace firmado.
5. Usuario verifica correo.
6. Sistema crea suscripcion PayPal y redirige a checkout.
7. Usuario aprueba en PayPal.
8. Retorno PayPal sincroniza estado y, si esta activa, provisiona tenant.
9. Se crea URL de impersonacion y acceso a panel tenant.
10. Webhooks mantienen estado de suscripcion actualizado.
11. Si estado queda inactivo, middleware bloquea `/admin`.
12. Tenant inactivo puede reactivar desde `/billing/inactive`.
13. Root monitorea y aplica overrides desde `/portal/root`.

## 11. Consideraciones tecnicas

### 11.1 Seguridad

1. Webhook PayPal excluido de CSRF, pero validado por firma via API PayPal.
2. Overrides solo disponibles para rol root.
3. Verificacion de correo usa URL firmada y expiracion.

### 11.2 Validaciones

1. `plan_code` obligatorio y valido.
2. Plan ID PayPal requerido para cada plan.
3. Motivo obligatorio para override.
4. Validaciones de RTN y correo para evitar duplicados antes de provision.

### 11.3 Control de errores

1. `payment_checkout_failed` marca solicitud en `failed`.
2. Se guarda `failure_code` y `failure_message`.
3. Webhook guarda error en `billing_webhook_events.error_message`.
4. Se agrego recovery para caso de error transaccional tardio (`no active transaction`) cuando la provision ya quedo aplicada.

### 11.4 Idempotencia y coherencia

1. Webhook idempotente por `provider + event_id`.
2. Sync de suscripcion es upsert por `paypal_subscription_id`.
3. Snapshot del centro se recalcula desde ultima suscripcion y override.

## 12. Posibles impactos o riesgos

1. Dependencia de configuracion:
   - si `PAYPAL_PLAN_*` no pertenece al mismo entorno/cuenta (`sandbox/live`), checkout falla con `RESOURCE_NOT_FOUND`.

2. Dependencia de webhook:
   - cambios posteriores de estado (suspension/cancelacion) dependen de entrega y firma valida del webhook.

3. Riesgo operativo del comando reset:
   - `tenancy:reset-clinics --force` borra datos de clinicas/tenants/billing.
   - usar solo en escenarios controlados.

4. Cobertura y entorno de pruebas:
   - existen pruebas nuevas de billing/webhook, pero la ejecucion local puede fallar si faltan drivers PDO (ejemplo `pdo_sqlite` para tests que usan sqlite).

5. Estados del proveedor simplificados:
   - normalizacion actual colapsa en `active/inactive`; si se requiere analitica avanzada, puede ser necesario mapear mas estados intermedios.
