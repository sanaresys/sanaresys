<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Factura, Pagos_Factura, CuentasPorCobrar, TipoPago, Centros_Medico, User, Pacientes, Persona};

class SimpleFacturacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_database_connection()
    {
        $this->assertTrue(true);
        
        // Test básico de creación de usuario
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com'
        ]);
    }
}
