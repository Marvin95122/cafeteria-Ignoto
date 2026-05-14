<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $order->id }}</title>
    <style>
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .ticket {
            width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .logo {
            max-width: 100px;
            margin: 0 auto 10px auto;
            display: block;
            filter: grayscale(100%) contrast(120%); 
        }
        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 2px 0;
            vertical-align: top;
        }
        .item-name {
            font-size: 11px;
            display: block;
        }
        .extras {
            font-size: 10px;
            padding-left: 10px;
        }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="ticket">
    
    {{-- LOGO DE LA CAFETERÍA --}}
    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo" onerror="this.style.display='none'">

    <div class="text-center">
        <h2 style="margin: 0; font-size: 16px;">CAFETERÍA IGNOTO</h2>
        <p style="margin: 2px 0;">Calle Ignoto aun por saber #9512</p>
        <p style="margin: 2px 0;">Tel: 951-270-7097, Correo: kk@gmail.com</p>
        <p style="margin: 2px 0;">Correo: kk@gmail.com</p>
    </div>

    <div class="divider"></div>

    <table style="font-size: 11px;">
        <tr>
            <td><span class="bold">Ticket:</span> #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td class="text-right"><span class="bold">Fecha:</span> {{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><span class="bold">Cajero:</span> {{ $order->user->name ?? 'Caja 1' }}</td>
            <td class="text-right"><span class="bold">Pago:</span> {{ strtoupper($order->payment_method) }}</td>
        </tr>
        {{-- Si la orden tiene un cliente VIP asociado, lo mostramos --}}
        @if($order->customer)
        <tr>
            <td colspan="2" style="padding-top: 4px;">
                <span class="bold">Cliente VIP:</span> {{ $order->customer->name }}
            </td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    {{-- PRODUCTOS --}}
    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000; margin-bottom: 5px;">
                <th class="text-left" style="width: 15%; padding-bottom: 5px;">Cant</th>
                <th class="text-left" style="width: 60%; padding-bottom: 5px;">Descripción</th>
                <th class="text-right" style="width: 25%; padding-bottom: 5px;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td class="text-center" style="padding-top: 5px;">
                        <span class="bold">{{ $item->quantity }}</span>
                    </td>
                    <td style="padding-top: 5px;">
                        <span class="item-name bold">{{ $item->product->name ?? 'Producto Eliminado' }}</span>
                        
                        <div class="extras" style="margin-top: 3px; color: #333;">
                            {{-- Muestra el precio base unitario --}}
                            Precio Base: ${{ number_format($item->unit_price, 2) }}<br>
                            
                            {{-- Lista los extras con su precio si es que tiene --}}
                            @if(!empty($item->extras))
                                @foreach($item->extras as $extra)
                                    + {{ $extra['name'] }} (${{ number_format($extra['price'], 2) }})<br>
                                @endforeach
                            @endif
                        </div>
                    </td>
                    {{-- Total de la línea (Base + Extras) * Cantidad --}}
                    <td class="text-right bold" style="vertical-align: bottom; padding-top: 5px;">
                        ${{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- TOTALES --}}
    <table>
        <tr style="font-size: 14px;">
            <td class="bold">TOTAL A PAGAR:</td>
            <td class="text-right bold">${{ number_format($order->total, 2) }}</td>
        </tr>
        @if($order->payment_method == 'efectivo')
            <tr>
                <td>Efectivo Recibido:</td>
                <td class="text-right">${{ number_format($received, 2) }}</td>
            </tr>
            <tr>
                <td>Cambio:</td>
                <td class="text-right">${{ number_format($change, 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <div class="text-center">
        <p class="bold" style="margin-bottom: 5px;">¡GRACIAS POR SU COMPRA!</p>
        <p style="font-size: 10px; margin: 0;">Vuelva pronto</p>
    </div>

    {{-- Botones de accion --}}
    <div class="text-center no-print" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Imprimir Ticket
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; background: #ddd; color: #333; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Cerrar
        </button>
    </div>

</div>

{{-- Abre el diálogo de impresión automáticamente --}}
<script>
    window.onload = function() {
        window.print();
        // Opcional: Cerrar la ventana después de imprimir
        // window.onafterprint = function() { window.close(); }
    }
</script>

</body>
</html>