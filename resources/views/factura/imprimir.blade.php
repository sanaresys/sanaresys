<table style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
    <thead>
        <tr style="background: {{ $config->color_primario }}; color: #fff;">
            <th style="padding: 8px; border: 1px solid #e5e7eb;">DESCRIPCIÓN DEL SERVICIO</th>
            <th style="padding: 8px; border: 1px solid #e5e7eb;">CANTIDAD</th>
            <th style="padding: 8px; border: 1px solid #e5e7eb;">PRECIO UNIT.</th>
            <th style="padding: 8px; border: 1px solid #e5e7eb;">DESCUENTO</th>
            <th style="padding: 8px; border: 1px solid #e5e7eb;">IMPUESTO</th>
            <th style="padding: 8px; border: 1px solid #e5e7eb;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detalles as $detalle)
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">
                    <strong>{{ $detalle->nombre }}</strong><br>
                    <small style="color: #888;">{{ $detalle->descripcion }}</small>
                </td>

                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: center;">
                    {{ $detalle->cantidad }}
                </td>

                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">
                    L. {{ number_format($detalle->precio_unitario ?? 0, 2) }}
                </td>

                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">
                    {{ $detalle->descuento_monto > 0 ? '- L. ' . number_format($detalle->descuento_monto, 2) : '-' }}
                </td>

                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">
                    L. {{ number_format($detalle->impuesto_monto ?? 0, 2) }}
                </td>

                <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right; font-weight: bold; color: green;">
                    L. {{ number_format($detalle->total ?? 0, 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
