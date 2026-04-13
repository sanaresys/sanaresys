<?php

namespace Tests\Feature;

use App\Http\Controllers\OnboardingController;
use App\Models\Centros_Medico;
use App\Services\TenantProvisioningService;
use Tests\TestCase;

class OnboardingFiscalGuardTest extends TestCase
{
    public function test_save_step_two_blocks_cai_configuration_when_rtn_is_missing(): void
    {
        $centro = new Centros_Medico([
            'nombre_centro' => 'Clinica Sin RTN',
            'rtn' => null,
            'onboarding_current_step' => 1,
        ]);

        $this->mock(TenantProvisioningService::class, function ($mock): void {
            // Constructor dependency placeholder; no behavior needed in this test.
        });

        $this->partialMock(OnboardingController::class, function ($mock) use ($centro): void {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('getCentroFromTenant')->andReturn($centro);
        });

        $response = $this
            ->from('/onboarding/step-2')
            ->withoutMiddleware()
            ->post(route('onboarding.save-step-2'), [
                'cai_codigo' => 'CAI-DEMO-123',
                'rango_inicial' => 1,
                'rango_final' => 10,
                'fecha_limite' => now()->addDays(10)->toDateString(),
            ]);

        $response->assertRedirect('/onboarding/step-2');
        $response->assertSessionHasErrors('rtn');
    }
}
