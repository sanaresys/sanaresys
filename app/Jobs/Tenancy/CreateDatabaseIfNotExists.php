<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;

class CreateDatabaseIfNotExists implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var TenantWithDatabase|Model */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(DatabaseManager $databaseManager): void
    {
        event(new CreatingDatabase($this->tenant));

        if ($this->tenant->getInternal('create_database') === false) {
            return;
        }

        $this->tenant->database()->makeCredentials();

        $databaseName = $this->tenant->database()->getName();
        $manager = $this->tenant->database()->manager();

        if ($manager->databaseExists($databaseName)) {
            Log::info('Base de datos tenant ya existe, se omite creacion.', [
                'tenant_id' => $this->tenant->getTenantKey(),
                'database' => $databaseName,
            ]);

            return;
        }

        $databaseManager->ensureTenantCanBeCreated($this->tenant);
        $manager->createDatabase($this->tenant);

        event(new DatabaseCreated($this->tenant));
    }
}
