<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de ventas</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1c1917;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .page {
            width: 100%;
            padding: 14px 18px;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #78350f;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-cell {
            width: 70px;
            vertical-align: middle;
        }

        .logo-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid #d6d3d1;
            background: #fff;
            text-align: center;
        }

        .logo-circle img {
            width: 56px;
            height: 56px;
            object-fit: cover;
        }

        .title-cell {
            vertical-align: middle;
        }

        .report-title {
            font-size: 24px;
            color: #78350f;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
        }

        .report-subtitle {
            margin: 4px 0 0;
            color: #57534e;
            font-size: 10px;
        }

        .meta-cell {
            width: 280px;
            text-align: right;
            vertical-align: middle;
            font-size: 9px;
            color: #57534e;
        }

        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #78350f;
            color: #fff;
            padding: 7px 9px;
            font-weight: bold;
            font-size: 11px;
            border-radius: 4px 4px 0 0;
        }

        .box {
            border: 1px solid #e7e5e4;
            border-top: none;
            padding: 9px;
            border-radius: 0 0 4px 4px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 20%;
            border: 1px solid #e7e5e4;
            padding: 8px;
            vertical-align: top;
        }

        .metric-label {
            font-size: 8px;
            color: #78716c;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 15px;
            font-weight: bold;
            color: #1c1917;
        }

        .metric-help {
            font-size: 8px;
            color: #78716c;
            margin-top: 3px;
        }

        .green { color: #15803d; }
        .red { color: #dc2626; }
        .blue { color: #1d4ed8; }
        .purple { color: #7e22ce; }
        .amber { color: #b45309; }

        .two-columns {
            width: 100%;
            border-collapse: collapse;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .data-table th {
            background: #f5f5f4;
            color: #44403c;
            text-transform: uppercase;
            font-size: 8px;
            padding: 6px;
            border: 1px solid #e7e5e4;
            text-align: left;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #e7e5e4;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-purple {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .bar-row {
            margin-bottom: 7px;
        }

        .bar-label {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .bar-track {
            height: 12px;
            background: #f5f5f4;
            border: 1px solid #e7e5e4;
            border-radius: 8px;
            overflow: hidden;
        }

        .bar-fill {
            height: 12px;
            background: #b45309;
        }

        .bar-fill-green { background: #16a34a; }
        .bar-fill-blue { background: #2563eb; }
        .bar-fill-purple { background: #7c3aed; }
        .bar-fill-red { background: #dc2626; }

        .page-break {
            page-break-before: always;
        }

        .small-muted {
            color: #78716c;
            font-size: 8px;
        }

        .footer {
            position: fixed;
            bottom: 8px;
            left: 18px;
            right: 18px;
            border-top: 1px solid #e7e5e4;
            padding-top: 4px;
            font-size: 8px;
            color: #78716c;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="footer">
    Cafetería Ignoto · Reporte generado por {{ $generatedBy }} · {{ $generatedAt }}
</div>

<div class="page">

    {{-- ENCABEZADO --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoBase64)
                        <div class="logo-circle">
                            <img src="{{ $logoBase64 }}" alt="Logo">
                        </div>
                    @endif
                </td>

                <td class="title-cell">
                    <h1 class="report-title">Reporte de ventas</h1>
                    <p class="report-subtitle">
                        Cafetería Ignoto · Periodo analizado: <strong>{{ $periodLabel }}</strong>
                    </p>
                </td>

                <td class="meta-cell">
                    <div><strong>Generado por:</strong> {{ $generatedBy }}</div>
                    <div><strong>Fecha de generación:</strong> {{ $generatedAt }}</div>
                    <div><strong>Periodo:</strong> {{ ucfirst($period) }}</div>
                    <div><strong>Agrupación:</strong> {{ ucfirst($groupBy) }}</div>
                    <div><strong>Método:</strong> {{ ucfirst($paymentFilter) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- INTERPRETACIÓN --}}
    <div class="section">
        <div class="section-title">Interpretación ejecutiva</div>
        <div class="box">
            {{ $executiveSummary }}
        </div>
    </div>

    {{-- RESUMEN --}}
    <div class="section">
        <div class="section-title">Resumen ejecutivo</div>
        <div class="box">
            <table class="summary-table">
                <tr>
                    <td>
                        <div class="metric-label">Ventas operadas</div>
                        <div class="metric-value amber">${{ number_format($totalOperatedSales, 2) }}</div>
                        <div class="metric-help">Efectivo + tarjeta + puntos</div>
                    </td>

                    <td>
                        <div class="metric-label">Ingresos reales</div>
                        <div class="metric-value green">${{ number_format($realIncome, 2) }}</div>
                        <div class="metric-help">Efectivo + tarjeta</div>
                    </td>

                    <td>
                        <div class="metric-label">Gastos activos</div>
                        <div class="metric-value red">${{ number_format($expensesTotal, 2) }}</div>
                        <div class="metric-help">{{ $expensesCount }} gasto(s)</div>
                    </td>

                    <td>
                        <div class="metric-label">Utilidad operativa</div>
                        <div class="metric-value blue">${{ number_format($operatingProfit, 2) }}</div>
                        <div class="metric-help">Ingresos - gastos</div>
                    </td>

                    <td>
                        <div class="metric-label">Ticket promedio</div>
                        <div class="metric-value">${{ number_format($averageTicket, 2) }}</div>
                        <div class="metric-help">{{ $completedOrdersCount }} completado(s)</div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="metric-label">Ventas efectivo</div>
                        <div class="metric-value green">${{ number_format($salesCash, 2) }}</div>
                        <div class="metric-help">{{ $cashOrdersCount }} ticket(s)</div>
                    </td>

                    <td>
                        <div class="metric-label">Ventas tarjeta</div>
                        <div class="metric-value blue">${{ number_format($salesCard, 2) }}</div>
                        <div class="metric-help">{{ $cardOrdersCount }} ticket(s)</div>
                    </td>

                    <td>
                        <div class="metric-label">Ventas puntos</div>
                        <div class="metric-value purple">${{ number_format($salesPoints, 2) }}</div>
                        <div class="metric-help">{{ $pointsOrdersCount }} ticket(s)</div>
                    </td>

                    <td>
                        <div class="metric-label">Tickets cancelados</div>
                        <div class="metric-value red">{{ $cancelledOrdersCount }}</div>
                        <div class="metric-help">${{ number_format($cancelledOrdersAmount, 2) }}</div>
                    </td>

                    <td>
                        <div class="metric-label">Puntos VIP</div>
                        <div class="metric-value purple">{{ number_format($pointsEarned) }}</div>
                        <div class="metric-help">{{ number_format($pointsUsed) }} usados</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ANÁLISIS --}}
    <table class="two-columns">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Ventas por periodo</div>
                    <div class="box">
                        @forelse($salesByPeriod as $row)
                            @php
                                $maxSales = max(1, collect($salesByPeriod)->max('sales'));
                                $width = $maxSales > 0 ? (($row['sales'] / $maxSales) * 100) : 0;
                            @endphp

                            <div class="bar-row">
                                <div class="bar-label">
                                    <strong>{{ $row['label'] }}</strong>
                                    · ${{ number_format($row['sales'], 2) }}
                                    · {{ $row['orders'] }} ticket(s)
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="small-muted">No hay ventas en el periodo.</p>
                        @endforelse
                    </div>
                </div>
            </td>

            <td>
                <div class="section">
                    <div class="section-title">Métodos de pago</div>
                    <div class="box">
                        @foreach($paymentBreakdown as $payment)
                            <div class="bar-row">
                                <div class="bar-label">
                                    <strong>{{ $payment['label'] }}</strong>
                                    · ${{ number_format($payment['amount'], 2) }}
                                    · {{ $payment['count'] }} ticket(s)
                                    · {{ number_format($payment['percentage'], 1) }}%
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill
                                        {{ $payment['key'] === 'efectivo' ? 'bar-fill-green' : '' }}
                                        {{ $payment['key'] === 'tarjeta' ? 'bar-fill-blue' : '' }}
                                        {{ $payment['key'] === 'puntos' ? 'bar-fill-purple' : '' }}"
                                        style="width: {{ $payment['percentage'] }}%;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- PRODUCTOS Y GASTOS --}}
    <table class="two-columns">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Productos más vendidos</div>
                    <div class="box">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Unidades</th>
                                    <th class="text-right">Ingreso</th>
                                    <th class="text-right">Part.</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($topProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">{{ $product->total_sold }}</td>
                                        <td class="text-right">${{ number_format($product->total_revenue, 2) }}</td>
                                        <td class="text-right">{{ number_format($product->percentage, 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center small-muted">Sin productos vendidos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>

            <td>
                <div class="section">
                    <div class="section-title">Gastos por categoría</div>
                    <div class="box">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th class="text-center">Gastos</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-right">Part.</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($expensesByCategory as $expenseCategory)
                                    <tr>
                                        <td>{{ $expenseCategory['category'] }}</td>
                                        <td class="text-center">{{ $expenseCategory['count'] }}</td>
                                        <td class="text-right">${{ number_format($expenseCategory['amount'], 2) }}</td>
                                        <td class="text-right">{{ number_format($expenseCategory['percentage'], 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center small-muted">Sin gastos activos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DETALLE DE TICKETS --}}
    <div class="section page-break">
        <div class="section-title">Detalle de tickets</div>
        <div class="box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Fecha</th>
                        <th>Cajero</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $order->created_at->format('d/m/Y h:i A') }}</td>
                            <td>{{ $order->user->name ?? 'Caja' }}</td>
                            <td>{{ $order->customer->name ?? 'Público general' }}</td>
                            <td>{{ strtoupper($order->payment_method) }}</td>
                            <td>
                                @if($order->status === 'completado')
                                    <span class="badge badge-green">Completado</span>
                                @else
                                    <span class="badge badge-red">Cancelado</span>
                                @endif
                            </td>
                            <td class="text-right">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center small-muted">No se encontraron tickets.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CANCELACIONES --}}
    <div class="section">
        <div class="section-title">Cancelaciones</div>
        <div class="box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Fecha</th>
                        <th>Cajero</th>
                        <th>Canceló</th>
                        <th>Acción</th>
                        <th>Motivo</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cancelledOrders as $order)
                        <tr>
                            <td>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $order->created_at->format('d/m/Y h:i A') }}</td>
                            <td>{{ $order->user->name ?? 'Caja' }}</td>
                            <td>{{ $order->canceller->name ?? 'No registrado' }}</td>
                            <td>{{ $order->cancellation_action ?? 'Sin acción' }}</td>
                            <td>{{ $order->cancellation_reason ?? 'Sin motivo' }}</td>
                            <td class="text-right">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center small-muted">No hubo cancelaciones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- GASTOS --}}
    <div class="section">
        <div class="section-title">Gastos del periodo</div>
        <div class="box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Registró</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->created_at->format('d/m/Y h:i A') }}</td>
                            <td>{{ $expense->user->name ?? 'Usuario' }}</td>
                            <td>{{ $expense->category ?? 'Sin categoría' }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ strtoupper($expense->status) }}</td>
                            <td class="text-right">${{ number_format($expense->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center small-muted">No hubo gastos en este periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>