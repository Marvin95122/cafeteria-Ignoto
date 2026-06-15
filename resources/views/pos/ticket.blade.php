<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Courier New", Courier, monospace;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .ticket-wrapper {
            width: 100%;
            min-height: 100vh;
            padding: 16px 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .ticket {
            width: 300px;
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
        .xs { font-size: 9px; }
        .muted { color: #555; }

        .logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            overflow: hidden;
            background: #fff;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 7px auto;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            filter: grayscale(100%) contrast(115%);
        }

        .brand-title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: .5px;
        }

        .brand-info {
            margin: 2px 0;
            font-size: 10px;
            line-height: 1.25;
        }

        .divider {
            border-bottom: 1px dashed #111;
            margin: 8px 0;
        }

        .solid-divider {
            border-bottom: 1px solid #111;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 2px 0;
            vertical-align: top;
        }

        .ticket-meta td {
            font-size: 10px;
            line-height: 1.35;
        }

        .items-table th {
            font-size: 10px;
            text-transform: uppercase;
            padding-bottom: 4px;
            border-bottom: 1px solid #111;
        }

        .item-row td {
            padding-top: 6px;
        }

        .item-name {
            font-size: 11px;
            font-weight: bold;
            display: block;
            line-height: 1.2;
        }

        .item-detail {
            font-size: 9px;
            color: #444;
            line-height: 1.25;
            margin-top: 2px;
        }

        .extra-line {
            display: block;
            padding-left: 6px;
        }

        .totals td {
            padding: 2px 0;
            font-size: 11px;
        }

        .total-row td {
            font-size: 14px;
            font-weight: bold;
            padding-top: 5px;
        }

        .vip-box {
            border: 1px dashed #111;
            padding: 5px;
            margin-top: 6px;
            font-size: 10px;
        }

        .reprint-box {
        border: 1px solid #111;
        padding: 4px;
        margin: 6px 0;
        text-align: center;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: .5px;
    }

        .thanks {
            margin: 6px 0 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .actions {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .actions button {
            border: none;
            border-radius: 6px;
            padding: 9px 12px;
            font-size: 12px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .btn-print {
            background: #111;
            color: #fff;
        }

        .btn-close {
            background: #ddd;
            color: #333;
        }

        @media print {
            body {
                background: #fff;
            }

            .ticket-wrapper {
                padding: 0;
                display: block;
                min-height: auto;
            }

            .ticket {
                width: 72mm;
                margin: 0 auto;
                padding: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: 80mm auto;
                margin: 4mm;
            }
        }
    </style>
</head>

<body>
<div class="ticket-wrapper">
    <div class="ticket">

        {{-- LOGO --}}
        <div class="logo-circle">
            <img src="{{ asset('img/logo-cafeteria.png') }}"
                alt="Logo Ignoto Café"
                onerror="this.src='{{ asset('images/logo-cafeteria.png') }}'">
        </div>

        {{-- ENCABEZADO --}}
        <div class="text-center">
            <h2 class="brand-title">IGNOTO CAFÉ</h2>
            <p class="brand-info">Sistema de venta y control interno</p>
            <p class="brand-info">Oaxaca de Juárez, Oaxaca</p>
            <p class="brand-info">Tel: 951 270 7097</p>
        </div>

        <div class="divider"></div>

        @if($isReprint)
            <div class="reprint-box">
                REIMPRESIÓN DE TICKET
            </div>
        @endif

        {{-- DATOS DEL TICKET --}}
        <table class="ticket-meta">
            <tr>
                <td>
                    <span class="bold">Ticket:</span>
                    #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                </td>
                <td class="text-right">
                    <span class="bold">Fecha:</span>
                    {{ $order->created_at->format('d/m/Y') }}
                </td>
            </tr>

            <tr>
                <td>
                    <span class="bold">Hora:</span>
                    {{ $order->created_at->format('h:i A') }}
                </td>
                <td class="text-right">
                    <span class="bold">Estado:</span>
                    {{ strtoupper($order->status) }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="bold">Cajero:</span>
                    {{ $order->user->name ?? 'Caja' }}
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="bold">Método de pago:</span>
                    {{ strtoupper($order->payment_method) }}
                </td>
            </tr>

            @if($order->customer)
                <tr>
                    <td colspan="2">
                        <span class="bold">Cliente VIP:</span>
                        {{ $order->customer->name }}
                    </td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        {{-- PRODUCTOS --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 13%;">Cant</th>
                    <th class="text-left" style="width: 57%;">Producto</th>
                    <th class="text-right" style="width: 30%;">Importe</th>
                </tr>
            </thead>

            <tbody>
                @foreach($order->items as $item)
                    @php
                        $extrasTotal = collect($item->extras ?? [])->sum('price');
                        $unitTotal = $item->unit_price + $extrasTotal;
                    @endphp

                    <tr class="item-row">
                        <td class="text-center bold">
                            {{ $item->quantity }}
                        </td>

                        <td>
                            <span class="item-name">
                                {{ $item->product->name ?? 'Producto no disponible' }}
                            </span>

                            <div class="item-detail">
                                Base: ${{ number_format($item->unit_price, 2) }}

                                @if(!empty($item->extras))
                                    @foreach($item->extras as $extra)
                                        <span class="extra-line">
                                            + {{ $extra['name'] ?? 'Extra' }}
                                            ${{ number_format($extra['price'] ?? 0, 2) }}
                                        </span>
                                    @endforeach
                                @endif

                                <span class="extra-line">
                                    Unitario: ${{ number_format($unitTotal, 2) }}
                                </span>
                            </div>
                        </td>

                        <td class="text-right bold">
                            ${{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        {{-- TOTALES --}}
        <table class="totals">
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="text-right">
                    ${{ number_format($order->total, 2) }}
                </td>
            </tr>

            @if($order->payment_method === 'efectivo')
                <tr>
                    <td>Efectivo recibido:</td>
                    <td class="text-right">
                        ${{ number_format($received, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Cambio:</td>
                    <td class="text-right">
                        ${{ number_format($change, 2) }}
                    </td>
                </tr>
            @endif

            @if($order->payment_method === 'tarjeta')
                <tr>
                    <td>Pago con tarjeta:</td>
                    <td class="text-right">
                        ${{ number_format($order->total, 2) }}
                    </td>
                </tr>
            @endif

            @if($order->payment_method === 'puntos')
                <tr>
                    <td>Puntos usados:</td>
                    <td class="text-right">
                        {{ number_format($pointsUsed) }} pts
                    </td>
                </tr>
            @endif
        </table>

        {{-- VIP --}}
        @if($order->customer)
            <div class="vip-box">
                <div class="bold text-center">PROGRAMA VIP IGNOTO</div>

                @if($pointsEarned > 0)
                    <div>
                        Puntos ganados en esta compra:
                        <span class="bold">{{ number_format($pointsEarned) }} pts</span>
                    </div>
                @endif

                @if($pointsUsed > 0)
                    <div>
                        Pago realizado con puntos:
                        <span class="bold">{{ number_format($pointsUsed) }} pts</span>
                    </div>
                @endif

                <div>
                    Saldo actual aproximado:
                    <span class="bold">{{ number_format($order->customer->points) }} pts</span>
                </div>
            </div>
        @endif

        <div class="divider"></div>

        {{-- PIE --}}
        <div class="text-center">
            <p class="thanks">¡GRACIAS POR SU COMPRA!</p>
            <p class="small muted">Conserve este comprobante.</p>
            <p class="xs muted">Vuelva pronto a Ignoto Café.</p>
        </div>

        {{-- BOTONES --}}
        <div class="actions no-print">
            <button onclick="window.print()" class="btn-print">
                🖨️ Imprimir
            </button>

            <button onclick="window.close()" class="btn-close">
                Cerrar
            </button>
        </div>

    </div>
</div>

<script>
    window.onload = function () {
        setTimeout(function () {
            window.print();
        }, 300);
    };
</script>

</body>
</html>