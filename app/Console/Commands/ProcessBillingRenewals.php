<?php

namespace App\Console\Commands;

use App\Services\Billing\BillingRenewalService;
use Illuminate\Console\Command;

class ProcessBillingRenewals extends Command
{
    protected $signature = 'billing:process-renewals';

    protected $description = 'Procesa renovaciones internas, gracia, suspension y recordatorios de billing.';

    public function __construct(
        protected BillingRenewalService $renewalService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $stats = $this->renewalService->processDaily();

        foreach ($stats as $key => $value) {
            $this->line(sprintf('%s: %d', $key, $value));
        }

        return self::SUCCESS;
    }
}
