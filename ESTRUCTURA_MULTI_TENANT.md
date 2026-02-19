# Estructura Multi-Tenant: BD por Centro

## BD CENTRAL (db_clinica)
Tablas compartidas entre todos los centros:

- `centros_medicos` - Los centros médicos
- `tenants` - Registro de tenants
- `domains` - Dominios de tenants
- `users` - Usuarios del sistema
- `personas` - Personas (pueden estar en varios centros)
- `nacionalidades` - Catálogo compartido
- `especialidades` - Catálogo compartido
- `medicos` - Médicos (con centro_id para asignación)
- `especialidad_medicos` - Relación médico-especialidad
- `centros_medicos_medicos` - Relación centro-médico
- `roles` - Roles de usuario
- `permissions` - Permisos
- `role_has_permissions`
- `model_has_roles`
- `model_has_permissions`
- `cache`, `jobs`, `migrations`

## BD TENANT (centro_1, centro_2, etc.)
Tablas aisladas por centro (SIN centro_id):

### Pacientes y Atención
- `pacientes` - Pacientes del centro
- `citas` - Citas
- `consultas` - Consultas
- `recetas` - Recetas médicas
- `examenes` - Exámenes médicos
- `enfermedades` - Catálogo de enfermedades
- `enfermedades_pacientes` - Historial enfermedades

### Facturación y Contabilidad
- `facturas`
- `factura_detalles`
- `pagos_facturas`
- `cuentas_por_cobrar`
- `servicios` - Servicios del centro
- `impuestos` - Impuestos del centro
- `descuentos` - Descuentos del centro
- `tipo_pagos` - Tipos de pago
- `recetarios` - Recetarios CAI
- `cai_autorizaciones` - Autorizaciones CAI
- `cai_correlativos` - Correlativos CAI
- `factura_disenos` - Diseños de factura

### Nómina
- `contratos_medicos`
- `nominas`
- `detalle_nominas`
- `cargos_medicos`

### Sistema
- `migrations` - Migraciones ejecutadas en el tenant

## Cambios Necesarios

1. **Eliminar `centro_id` de tablas tenant** y sus FK a `centros_medicos`
2. **Eliminar referencias FK** entre BD tenant y BD central
3. **Mantener `centro_id`** en tablas de BD central que lo necesiten
4. **Actualizar modelos** para no usar TenantScoped en tablas tenant
5. **Corregir Observer** para ejecutar migraciones correctamente
