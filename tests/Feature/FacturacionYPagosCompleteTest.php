<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\{Factura, Pagos_Factura, CuentasPorCobrar, TipoPago, Centros_Medico, User, Pacientes, Persona, Medico, Consulta, Servicio, FacturaDetalle, Descuento};
use Illuminate\Support\Facades\Auth;

class FacturacionYPagosCompleteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $centro;
    protected $usuario;
    protected $paciente;
    protected $medico;
    protected $consulta;
    protected $servicio;
    protected $tipoPago;
    protected $descuento;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos básicos necesarios para los tests
        $this->setupBasicData();
    }

    private function setupBasicData()
    {
        // Crear centro médico
        $this->centro = Centros_Medico::create([
            'nombre' => 'Centro Test',
            'direccion' => 'Dirección Test',
            'telefono' => '12345678',
            'correo' => 'test@test.com',
        ]);

        // Crear usuario
        $this->usuario = User::create([
            'name' => 'Usuario Test',
            'email' => 'usuario@test.com',
            'password' => bcrypt('password'),
            'centro_id' => $this->centro->id,
        ]);

        // Crear persona para paciente
        $personaPaciente = Persona::create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'identidad' => '0801199012345',
            'sexo' => 'MASCULINO',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '98765432',
        ]);

        // Crear paciente
        $this->paciente = Pacientes::create([
            'persona_id' => $personaPaciente->id,
            'centro_id' => $this->centro->id,
            'codigo_paciente' => 'PAC001',
            'created_by' => $this->usuario->id,
        ]);

        // Crear persona para médico
        $personaMedico = Persona::create([
            'primer_nombre' => 'Dr. Carlos',
            'primer_apellido' => 'López',
            'identidad' => '0801198512345',
            'sexo' => 'MASCULINO',
            'fecha_nacimiento' => '1985-01-01',
            'telefono' => '87654321',
        ]);

        // Crear médico
        $this->medico = Medico::create([
            'persona_id' => $personaMedico->id,
            'centro_id' => $this->centro->id,
            'numero_colegiacion' => 'MED001',
            'especialidad' => 'General',
            'created_by' => $this->usuario->id,
        ]);

        // Crear consulta
        $this->consulta = Consulta::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'centro_id' => $this->centro->id,
            'fecha_consulta' => now(),
            'motivo_consulta' => 'Consulta test',
            'created_by' => $this->usuario->id,
        ]);

        // Crear servicio
        $this->servicio = Servicio::create([
            'nombre' => 'Consulta General',
            'descripcion' => 'Consulta médica general',
            'precio_unitario' => 100.00,
            'centro_id' => $this->centro->id,
            'es_exonerado' => 'NO',
            'created_by' => $this->usuario->id,
        ]);

        // Crear tipos de pago
        $this->tipoPago = TipoPago::create([
            'nombre' => 'Efectivo',
            'descripcion' => 'Pago en efectivo',
            'centro_id' => $this->centro->id,
            'activo' => 'SI',
            'created_by' => $this->usuario->id,
        ]);

        TipoPago::create([
            'nombre' => 'Tarjeta',
            'descripcion' => 'Pago con tarjeta',
            'centro_id' => $this->centro->id,
            'activo' => 'SI',
            'created_by' => $this->usuario->id,
        ]);

        // Crear descuento
        $this->descuento = Descuento::create([
            'nombre' => 'Descuento Test',
            'tipo' => 'PORCENTAJE',
            'valor' => 10.00,
            'aplica_desde' => now()->subDay(),
            'aplica_hasta' => now()->addMonth(),
            'activo' => 'SI',
            'centro_id' => $this->centro->id,
            'created_by' => $this->usuario->id,
        ]);

        // Autenticar usuario para los tests
        Auth::login($this->usuario);
    }

    /**
     * Test: Crear factura con pago completo - verificar que se creen los pagos y la factura quede PAGADA
     */
    public function test_crear_factura_con_pago_completo()
    {
        // Crear detalle de factura
        $detalle = FacturaDetalle::create([
            'consulta_id' => $this->consulta->id,
            'servicio_id' => $this->servicio->id,
            'cantidad' => 1,
            'subtotal' => 100.00,
            'impuesto_monto' => 0.00,
            'descuento_monto' => 10.00, // 10% de descuento
            'total_linea' => 90.00,
            'centro_id' => $this->centro->id,
            'created_by' => $this->usuario->id,
        ]);

        // Crear factura
        $factura = Factura::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'consulta_id' => $this->consulta->id,
            'centro_id' => $this->centro->id,
            'fecha_emision' => now(),
            'subtotal' => 100.00,
            'descuento_total' => 10.00,
            'impuesto_total' => 0.00,
            'total' => 90.00,
            'estado' => 'PENDIENTE',
            'descuento_id' => $this->descuento->id,
            'usa_cai' => false,
            'created_by' => $this->usuario->id,
        ]);

        // Asignar detalle a la factura
        $detalle->update(['factura_id' => $factura->id]);

        // Crear pago completo (90.00)
        $pago = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $this->tipoPago->id,
            'monto_recibido' => 90.00,
            'monto_devolucion' => 0.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        // Refrescar factura para obtener el estado actualizado
        $factura->refresh();

        // Verificaciones
        $this->assertEquals('PAGADA', $factura->estado, 'La factura debe estar en estado PAGADA');
        $this->assertEquals(90.00, $factura->montoPagado(), 'El monto pagado debe ser 90.00');
        $this->assertEquals(0.00, $factura->saldoPendiente(), 'El saldo pendiente debe ser 0.00');

        // Verificar que se creó el pago
        $this->assertDatabaseHas('pagos_facturas', [
            'factura_id' => $factura->id,
            'monto_recibido' => 90.00,
            'tipo_pago_id' => $this->tipoPago->id,
        ]);

        // Verificar que NO se creó cuenta por cobrar (porque está completamente pagada)
        $this->assertDatabaseMissing('cuentas_por_cobrars', [
            'factura_id' => $factura->id,
            'saldo_pendiente' => 0,
        ]);

        echo "\n✅ Test 1 PASADO: Factura con pago completo - Estado PAGADA, sin cuenta por cobrar\n";
    }

    /**
     * Test: Crear factura con pago parcial - verificar que se cree cuenta por cobrar
     */
    public function test_crear_factura_con_pago_parcial()
    {
        // Crear detalle de factura
        $detalle = FacturaDetalle::create([
            'consulta_id' => $this->consulta->id,
            'servicio_id' => $this->servicio->id,
            'cantidad' => 1,
            'subtotal' => 100.00,
            'impuesto_monto' => 0.00,
            'descuento_monto' => 0.00,
            'total_linea' => 100.00,
            'centro_id' => $this->centro->id,
            'created_by' => $this->usuario->id,
        ]);

        // Crear factura
        $factura = Factura::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'consulta_id' => $this->consulta->id,
            'centro_id' => $this->centro->id,
            'fecha_emision' => now(),
            'subtotal' => 100.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 0.00,
            'total' => 100.00,
            'estado' => 'PENDIENTE',
            'usa_cai' => false,
            'created_by' => $this->usuario->id,
        ]);

        // Asignar detalle a la factura
        $detalle->update(['factura_id' => $factura->id]);

        // Crear pago parcial (50.00 de 100.00)
        $pago = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $this->tipoPago->id,
            'monto_recibido' => 50.00,
            'monto_devolucion' => 0.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        // Refrescar factura para obtener el estado actualizado
        $factura->refresh();

        // Verificaciones de la factura
        $this->assertEquals('PARCIAL', $factura->estado, 'La factura debe estar en estado PARCIAL');
        $this->assertEquals(50.00, $factura->montoPagado(), 'El monto pagado debe ser 50.00');
        $this->assertEquals(50.00, $factura->saldoPendiente(), 'El saldo pendiente debe ser 50.00');

        // Verificar que se creó el pago
        $this->assertDatabaseHas('pagos_facturas', [
            'factura_id' => $factura->id,
            'monto_recibido' => 50.00,
            'tipo_pago_id' => $this->tipoPago->id,
        ]);

        // Verificar que se creó cuenta por cobrar
        $this->assertDatabaseHas('cuentas_por_cobrars', [
            'factura_id' => $factura->id,
            'saldo_pendiente' => 50.00,
            'estado_cuentas_por_cobrar' => 'PARCIAL',
        ]);

        $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $factura->id)->first();
        $this->assertNotNull($cuentaPorCobrar, 'Debe existir una cuenta por cobrar');
        $this->assertEquals(50.00, $cuentaPorCobrar->saldo_pendiente, 'El saldo de la cuenta por cobrar debe ser 50.00');

        echo "\n✅ Test 2 PASADO: Factura con pago parcial - Estado PARCIAL, cuenta por cobrar creada\n";
    }

    /**
     * Test: Pagar cuenta por cobrar con múltiples métodos de pago
     */
    public function test_pagar_cuenta_por_cobrar_con_multiples_metodos()
    {
        // Crear factura con pago parcial inicial (reutilizamos la lógica del test anterior)
        $detalle = FacturaDetalle::create([
            'consulta_id' => $this->consulta->id,
            'servicio_id' => $this->servicio->id,
            'cantidad' => 1,
            'subtotal' => 200.00,
            'impuesto_monto' => 0.00,
            'descuento_monto' => 0.00,
            'total_linea' => 200.00,
            'centro_id' => $this->centro->id,
            'created_by' => $this->usuario->id,
        ]);

        $factura = Factura::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'consulta_id' => $this->consulta->id,
            'centro_id' => $this->centro->id,
            'fecha_emision' => now(),
            'subtotal' => 200.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 0.00,
            'total' => 200.00,
            'estado' => 'PENDIENTE',
            'usa_cai' => false,
            'created_by' => $this->usuario->id,
        ]);

        $detalle->update(['factura_id' => $factura->id]);

        // Primer pago parcial en efectivo (80.00)
        $tipoPagoEfectivo = $this->tipoPago;
        $pago1 = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $tipoPagoEfectivo->id,
            'monto_recibido' => 80.00,
            'monto_devolucion' => 0.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();

        // Verificar estado tras primer pago
        $this->assertEquals('PARCIAL', $factura->estado);
        $this->assertEquals(80.00, $factura->montoPagado());
        $this->assertEquals(120.00, $factura->saldoPendiente());

        // Verificar que existe cuenta por cobrar
        $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $factura->id)->first();
        $this->assertNotNull($cuentaPorCobrar);
        $this->assertEquals(120.00, $cuentaPorCobrar->saldo_pendiente);

        // Crear tipo pago tarjeta
        $tipoPagoTarjeta = TipoPago::where('nombre', 'Tarjeta')->first();

        // Segundo pago con tarjeta (70.00)
        $pago2 = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $tipoPagoTarjeta->id,
            'monto_recibido' => 70.00,
            'monto_devolucion' => 0.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();

        // Verificar estado tras segundo pago
        $this->assertEquals('PARCIAL', $factura->estado);
        $this->assertEquals(150.00, $factura->montoPagado());
        $this->assertEquals(50.00, $factura->saldoPendiente());

        // Tercer pago final en efectivo (50.00)
        $pago3 = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $tipoPagoEfectivo->id,
            'monto_recibido' => 50.00,
            'monto_devolucion' => 0.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();
        $cuentaPorCobrar->refresh();

        // Verificaciones finales
        $this->assertEquals('PAGADA', $factura->estado, 'La factura debe estar PAGADA');
        $this->assertEquals(200.00, $factura->montoPagado(), 'El monto total pagado debe ser 200.00');
        $this->assertEquals(0.00, $factura->saldoPendiente(), 'El saldo pendiente debe ser 0.00');

        // Verificar que la cuenta por cobrar se actualizó
        $this->assertEquals(0.00, $cuentaPorCobrar->saldo_pendiente, 'La cuenta por cobrar debe tener saldo 0');
        $this->assertEquals('PAGADA', $cuentaPorCobrar->estado_cuentas_por_cobrar, 'La cuenta debe estar PAGADA');

        // Verificar que se crearon todos los pagos
        $totalPagos = Pagos_Factura::where('factura_id', $factura->id)->count();
        $this->assertEquals(3, $totalPagos, 'Deben existir 3 pagos');

        // Verificar tipos de pago utilizados
        $pagosEfectivo = Pagos_Factura::where('factura_id', $factura->id)
            ->where('tipo_pago_id', $tipoPagoEfectivo->id)
            ->count();
        $this->assertEquals(2, $pagosEfectivo, 'Deben existir 2 pagos en efectivo');

        $pagosTarjeta = Pagos_Factura::where('factura_id', $factura->id)
            ->where('tipo_pago_id', $tipoPagoTarjeta->id)
            ->count();
        $this->assertEquals(1, $pagosTarjeta, 'Debe existir 1 pago con tarjeta');

        echo "\n✅ Test 3 PASADO: Cuenta por cobrar pagada con múltiples métodos - 3 pagos diferentes\n";
    }

    /**
     * Test: Verificar que los observers funcionan correctamente
     */
    public function test_observers_actualizan_estados_correctamente()
    {
        // Crear factura
        $factura = Factura::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'consulta_id' => $this->consulta->id,
            'centro_id' => $this->centro->id,
            'fecha_emision' => now(),
            'subtotal' => 150.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 0.00,
            'total' => 150.00,
            'estado' => 'PENDIENTE',
            'usa_cai' => false,
            'created_by' => $this->usuario->id,
        ]);

        // Estado inicial
        $this->assertEquals('PENDIENTE', $factura->estado);
        $this->assertEquals(0.00, $factura->montoPagado());

        // Crear primer pago - debe activar observer
        $pago1 = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $this->tipoPago->id,
            'monto_recibido' => 100.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();

        // Verificar que el observer actualizó el estado
        $this->assertEquals('PARCIAL', $factura->estado, 'Observer debe actualizar estado a PARCIAL');
        $this->assertEquals(100.00, $factura->montoPagado());

        // Verificar que se creó cuenta por cobrar automáticamente
        $cuentaPorCobrar = CuentasPorCobrar::where('factura_id', $factura->id)->first();
        $this->assertNotNull($cuentaPorCobrar, 'Observer debe crear cuenta por cobrar');
        $this->assertEquals(50.00, $cuentaPorCobrar->saldo_pendiente);

        // Segundo pago completa la factura
        $pago2 = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $this->tipoPago->id,
            'monto_recibido' => 50.00,
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();
        $cuentaPorCobrar->refresh();

        // Verificar que el observer actualizó todo correctamente
        $this->assertEquals('PAGADA', $factura->estado, 'Observer debe actualizar estado a PAGADA');
        $this->assertEquals(150.00, $factura->montoPagado());
        $this->assertEquals('PAGADA', $cuentaPorCobrar->estado_cuentas_por_cobrar);
        $this->assertEquals(0.00, $cuentaPorCobrar->saldo_pendiente);

        echo "\n✅ Test 4 PASADO: Observers funcionan correctamente - Estados automáticos\n";
    }

    /**
     * Test adicional: Verificar casos edge como pagos excesivos
     */
    public function test_pago_excesivo_genera_devolucion()
    {
        $factura = Factura::create([
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'consulta_id' => $this->consulta->id,
            'centro_id' => $this->centro->id,
            'fecha_emision' => now(),
            'subtotal' => 75.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 0.00,
            'total' => 75.00,
            'estado' => 'PENDIENTE',
            'usa_cai' => false,
            'created_by' => $this->usuario->id,
        ]);

        // Pago excesivo (100.00 para factura de 75.00)
        $pago = Pagos_Factura::create([
            'factura_id' => $factura->id,
            'paciente_id' => $this->paciente->id,
            'centro_id' => $this->centro->id,
            'tipo_pago_id' => $this->tipoPago->id,
            'monto_recibido' => 100.00,
            'monto_devolucion' => 25.00, // Devolución calculada
            'fecha_pago' => now(),
            'created_by' => $this->usuario->id,
        ]);

        $factura->refresh();

        $this->assertEquals('PAGADA', $factura->estado);
        $this->assertEquals(100.00, $factura->montoPagado());
        $this->assertEquals(0.00, $factura->saldoPendiente()); // Saldo nunca puede ser negativo

        echo "\n✅ Test 5 PASADO: Pago excesivo manejado correctamente\n";
    }
}
