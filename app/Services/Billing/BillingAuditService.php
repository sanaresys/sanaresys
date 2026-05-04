<?php

namespace App\Services\Billing;

use App\Models\BillingAudit;
use App\Models\BillingInvoice;
use App\Models\BillingModuleSubscription;
use App\Models\BillingTenantSubscription;
use App\Models\Centros_Medico;
use App\Models\User;

class BillingAuditService
{
    public function log(
        string $eventType,
        ?Centros_Medico $centro = null,
        ?BillingInvoice $invoice = null,
        ?BillingTenantSubscription $tenantSubscription = null,
        ?BillingModuleSubscription $moduleSubscription = null,
        ?User $actor = null,
        string $actorType = 'system',
        ?string $reason = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $meta = null,
    ): BillingAudit {
        return BillingAudit::query()->create([
            'centro_id' => $centro?->id,
            'billing_invoice_id' => $invoice?->id,
            'billing_tenant_subscription_id' => $tenantSubscription?->id,
            'billing_module_subscription_id' => $moduleSubscription?->id,
            'actor_user_id' => $actor?->id,
            'actor_type' => $actorType,
            'event_type' => $eventType,
            'reason' => $reason,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'meta' => $meta,
        ]);
    }
}
