# Billing interno con PayPal manual

Fecha de actualizacion: 24-Mar-2026

## Resumen

El billing ya no usa PayPal Subscriptions como fuente de verdad operativa.

Ahora el estado real vive en la base de datos local y PayPal se usa para:

1. Crear ordenes manuales.
2. Capturar pagos.
3. Recibir webhooks de conciliacion.
4. Tener respaldo y auditoria externa.

No se implementa vault, metodo guardado ni cobro silencioso automatico en esta fase.

## Flujo de onboarding

1. El usuario se registra.
2. Verifica su correo.
3. Entra a `GET /registro-clinica/{publicId}/billing`.
4. Ve el resumen del plan, el monto y el consentimiento.
5. El frontend crea la orden con `POST /registro-clinica/{publicId}/billing/order`.
6. PayPal procesa el checkout embebido.
7. La captura se confirma con `POST /registro-clinica/{publicId}/billing/capture`.
8. La factura se marca pagada.
9. Se provisiona la clinica.
10. Se crea `billing_tenant_subscriptions` y el tenant queda `active`.

## Flujo de billing tenant

Pantalla principal:

1. `GET /billing`

Acciones:

1. Pagar factura abierta:
   - `POST /billing/invoices/{invoice}/order`
   - `POST /billing/invoices/{invoice}/capture`
2. Programar cancelacion al final del periodo:
   - `POST /billing/cancel-at-period-end`
3. Reanudar renovacion:
   - `POST /billing/resume-renewal`
4. Reactivacion de tenant suspendido:
   - `POST /billing/reactivate`

## Fuente de verdad local

### Estado del plan base

Tabla principal: `billing_tenant_subscriptions`

Campos clave:

1. `status`
2. `plan_code`
3. `billing_interval`
4. `anchor_at`
5. `current_period_starts_at`
6. `current_period_ends_at`
7. `next_charge_at`
8. `grace_until`
9. `cancel_at_period_end`
10. `dunning_attempts`

Estados usados por el sistema:

1. `active`
2. `past_due`
3. `grace`
4. `suspended`
5. `canceled`

### Facturas y cobros

Tablas:

1. `billing_invoices`
2. `billing_invoice_items`
3. `billing_charge_attempts`

Cada factura puede incluir:

1. Plan base.
2. Renovacion de modulos.
3. Prorrateos de modulos.
4. Reemplazos por refund o reverso.

### Auditoria y notificaciones

Tablas:

1. `billing_audits`
2. `billing_notification_logs`
3. `notifications` en tenant DB

## Dunning y suspension

Comando diario:

1. `billing:process-renewals`

Scheduler:

1. Usa `billing.engine.process_time`
2. Usa `billing.engine.timezone`

Reglas actuales:

1. El dia exacto del vencimiento se abre o reutiliza una factura.
2. El tenant pasa a `past_due`.
3. Empieza el periodo de gracia local.
4. Se envian recordatorios deduplicados.
5. Cada ejecucion diaria registra avance de dunning una sola vez por dia.
6. Al terminar la gracia el tenant pasa a `suspended`.
7. Si paga durante `past_due` o `grace`, vuelve a `active`.

Acceso al panel:

1. `active`: entra.
2. `past_due`: entra.
3. `grace`: entra.
4. `suspended`: no entra, va a billing/reactivacion.
5. `canceled`: no entra.

## Modulos y prorrateo

Los modulos ya no dependen de una orden aislada por fuera del motor local.

Ahora:

1. Se alinean al `anchor_at` del tenant.
2. Soportan `monthly` y `annual`.
3. Si se agregan a mitad de ciclo, se genera una factura de prorrateo.
4. En el siguiente ciclo entran al cobro completo.
5. Su estado se guarda en `billing_module_subscriptions`.

Rutas:

1. `POST /billing/modules/{module}/subscribe`
2. `POST /billing/modules/{module}/cancel-at-period-end`

## Webhooks

Endpoint:

1. `POST /webhooks/paypal`

Eventos soportados para el motor nuevo:

1. `PAYMENT.CAPTURE.COMPLETED`
2. `PAYMENT.CAPTURE.REFUNDED`

Comportamiento:

1. Idempotencia con `billing_webhook_events`.
2. Si el browser no alcanza a confirmar la captura, el webhook puede cerrar la factura.
3. Si llega refund o reverso, la factura se marca `refunded`, se crea deuda de reemplazo y se audita.

El webhook sigue manteniendo compatibilidad con el flujo legacy mientras existan suscripciones viejas.

## Root

Vista:

1. `GET /portal/root`

Acciones:

1. Entrar al tenant.
2. Aplicar override de snapshot.
3. Marcar una factura como pagada manualmente.
4. Extender vigencia.
5. Cambiar estado del tenant.
6. Programar o revertir cancelacion al final del periodo.

Todas estas acciones registran auditoria local.

## Archivos principales

Servicios:

1. `app/Services/Billing/BillingInvoiceService.php`
2. `app/Services/Billing/BillingRenewalService.php`
3. `app/Services/Billing/BillingAdminService.php`
4. `app/Services/Billing/BillingStateService.php`
5. `app/Services/Billing/BillingNotificationService.php`

Controladores:

1. `app/Http/Controllers/ClinicRegistrationController.php`
2. `app/Http/Controllers/TenantBillingController.php`
3. `app/Http/Controllers/TenantModuleBillingController.php`
4. `app/Http/Controllers/PayPalWebhookController.php`
5. `app/Http/Controllers/RootPortalController.php`

Vistas:

1. `resources/views/registro-clinica-billing.blade.php`
2. `resources/views/tenant-billing.blade.php`
3. `resources/views/root-portal.blade.php`

## Limitaciones de esta fase

1. No hay metodo de pago guardado.
2. No hay auto-charge silencioso.
3. No hay vault ni tokenizacion PayPal.
4. La renovacion se gestiona con facturas manuales y dunning local.
5. `billing_subscriptions` y el flujo legacy siguen existiendo solo por compatibilidad y lectura historica.
