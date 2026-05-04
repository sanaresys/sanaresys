<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_modules', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_modules', 'price_annual')) {
                $table->decimal('price_annual', 10, 2)->nullable()->after('price_monthly');
            }
        });

        DB::table('billing_modules')
            ->whereNull('price_annual')
            ->update([
                'price_annual' => DB::raw('ROUND(price_monthly * 12, 2)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('billing_modules', function (Blueprint $table) {
            if (Schema::hasColumn('billing_modules', 'price_annual')) {
                $table->dropColumn('price_annual');
            }
        });
    }
};
