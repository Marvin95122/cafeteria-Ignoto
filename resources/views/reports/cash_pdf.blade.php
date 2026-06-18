<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de caja</title>

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
            width: 285px;
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

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
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
            background: #2563eb;
        }

        .bar-fill-green { background: #16a34a; }
        .bar-fill-red { background: #dc2626; }
        .bar-fill-amber { background: #b45309; }

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
                    <h1 class="report-title">Reporte de caja</h1>
                    <p class="report-subtitle">
                        Cafetería Ignoto · Periodo analizado:
                        <strong>{{ $periodLabel }}</strong>
                    </p>
                </td>

                <td class="meta-cell">
                    <div><strong>Generado por:</strong> {{ $generatedBy }}</div>
                    <div><strong>Fecha de generación:</strong> {{ $generatedAt }}</div>
                    <div><strong>Periodo:</strong> {{ ucfirst($period) }}</div>
                    <div><strong>Agrupación:</strong> {{ ucfirst($groupBy) }}</div>
                    <div><strong>Estado:</strong> {{ ucfirst($statusFilter) }}</div>
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
        <div class="section-title">Resumen ejecutivo de caja</div>
        <div class="box">
            <table class="summary-table">
                <tr>
                    <td>
                        <div class="metric-label">Cortes registrados</div>
                        <div class="metric-value amber">{{ $totalCuts }}</div>
                        <div class="metric-help">{{ $closedCuts }} cerrados · {{ $openCuts }} abiertos</div>
                    </td>

                    <td>
                        <div class="metric-label">Efectivo esperado</div>
                        <div class="metric-value blue">${{ number_format($expectedTotal, 2) }}</div>
                        <div class="metric-help">Fondo + efectivo - gastos</div>
                    </td>

                    <td>
                        <div class="metric-label">Efectivo contado</div>
                        <div class="metric-value green">${{ number_format($actualTotal, 2) }}</div>
                        <div class="metric-help">Suma capturada al cierre</div>
                    </td>

                    <td>
                        <div class="metric-label">Diferencia acumulada</div>
                        <div class="metric-value {{ $differenceTotal < 0 ? 'red' : 'green' }}">
                            ${{ number_format($differenceTotal, 2) }}
                        </div>
                        <div class="metric-help">Contado - esperado</div>
                    </td>

                    <td>
                        <div class="metric-label">Correcciones</div>
                        <div class="metric-value purple">{{ $adjustmentsCount }}</div>
                        <div class="metric-help">Ajustes auditados</div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="metric-label">Ventas efectivo</div>
                        <div class="metric-value green">${{ number_format($salesCashTotal, 2) }}</div>
                        <div class="metric-help">Ventas completadas</div>
                    </td>

                    <td>
                        <div class="metric-label">Ventas tarjeta</div>
                        <div class="metric-value blue">${{ number_format($salesCardTotal, 2) }}</div>
                        <div class="metric-help">No afectan efectivo físico</div>
                    </td>

                    <td>
                        <div class="metric-label">Ventas puntos</div>
                        <div class="metric-value purple">${{ number_format($salesPointsTotal, 2) }}</div>
                        <div class="metric-help">Valor canjeado</div>
                    </td>

                    <td>
                        <div class="metric-label">Gastos activos</div>
                        <div class="metric-value red">${{ number_format($expensesTotal, 2) }}</div>
                        <div class="metric-help">Descuentan caja</div>
                    </td>

                    <td>
                        <div class="metric-label">Flujo efectivo</div>
                        <div class="metric-value {{ $cashFlow < 0 ? 'red' : 'blue' }}">
                            ${{ number_format($cashFlow, 2) }}
                        </div>
                        <div class="metric-help">Ventas efectivo - gastos</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ANALISIS --}}
    <table class="two-columns">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Caja por periodo</div>
                    <div class="box">
                        @php
                            $maxExpected = collect($cashByPeriod)->max('expected') ?: 1;
                            $maxActual = collect($cashByPeriod)->max('actual') ?: 1;
                            $maxValue = max($maxExpected, $maxActual, 1);
                        @endphp

                        @forelse($cashByPeriod as $row)
                            @php
                                $expectedWidth = $maxValue > 0 ? (($row['expected'] / $maxValue) * 100) : 0;
                                $actualWidth = $maxValue > 0 ? (($row['actual'] / $maxValue) * 100) : 0;
                            @endphp

                            <div class="bar-row">
                                <div class="bar-label">
                                    <strong>{{ $row['label'] }}</strong>
                                    · Esperado: ${{ number_format($row['expected'], 2) }}
                                    · Contado: ${{ number_format($row['actual'], 2) }}
                                    · Dif: ${{ number_format($row['difference'], 2) }}
                                </div>

                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ $expectedWidth }}%;"></div>
                                </div>

                                <div class="bar-track" style="margin-top: 2px;">
                                    <div class="bar-fill bar-fill-green" style="width: {{ $actualWidth }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="small-muted">No hay información de caja en el periodo.</p>
                        @endforelse

                        <p class="small-muted">
                            Barra azul: efectivo esperado · Barra verde: efectivo contado.
                        </p>
                    </div>
                </div>
            </td>

            <td>
                <div class="section">
                    <div class="section-title">Estado y diferencias</div>
                    <div class="box">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Indicador</th>
                                    <th class="text-center">Cantidad</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Cortes abiertos</td>
                                    <td class="text-center">{{ $openCuts }}</td>
                                    <td>Cajas todavía sin cierre.</td>
                                </tr>

                                <tr>
                                    <td>Cortes cerrados</td>
                                    <td class="text-center">{{ $closedCuts }}</td>
                                    <td>Cajas con efectivo contado registrado.</td>
                                </tr>

                                <tr>
                                    <td>Cortes con sobrante</td>
                                    <td class="text-center">{{ $cutsWithPositiveDifference }}</td>
                                    <td>Diferencia mayor a cero.</td>
                                </tr>

                                <tr>
                                    <td>Cortes con faltante</td>
                                    <td class="text-center">{{ $cutsWithNegativeDifference }}</td>
                                    <td>Diferencia menor a cero.</td>
                                </tr>

                                <tr>
                                    <td>Cortes sin diferencia</td>
                                    <td class="text-center">{{ $cutsWithoutDifference }}</td>
                                    <td>Diferencia igual a cero.</td>
                                </tr>

                                <tr>
                                    <td>Tickets completados</td>
                                    <td class="text-center">{{ $completedOrdersCount }}</td>
                                    <td>Tickets activos dentro de los cortes.</td>
                                </tr>

                                <tr>
                                    <td>Tickets cancelados</td>
                                    <td class="text-center">{{ $cancelledOrdersCount }}</td>
                                    <td>Tickets anulados dentro de los cortes.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- MAYORES DIFERENCIAS Y GASTOS --}}
    <table class="two-columns">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title">Mayores diferencias</div>
                    <div class="box">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Corte</th>
                                    <th>Fecha</th>
                                    <th class="text-right">Esperado</th>
                                    <th class="text-right">Contado</th>
                                    <th class="text-right">Dif.</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($topDifferences as $row)
                                    <tr>
                                        <td>#{{ $row['id'] }}</td>
                                        <td>{{ $row['opened_at']->format('d/m/Y h:i A') }}</td>
                                        <td class="text-right">${{ number_format($row['expected_cash'], 2) }}</td>
                                        <td class="text-right">
                                            {{ $row['actual_cash'] !== null ? '$' . number_format($row['actual_cash'], 2) : 'Pendiente' }}
                                        </td>
                                        <td class="text-right {{ $row['difference'] < 0 ? 'red' : 'green' }}">
                                            ${{ number_format($row['difference'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center small-muted">Sin diferencias registradas.</td>
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
                                @forelse($expensesByCategory as $row)
                                    <tr>
                                        <td>{{ $row['category'] }}</td>
                                        <td class="text-center">{{ $row['count'] }}</td>
                                        <td class="text-right">${{ number_format($row['amount'], 2) }}</td>
                                        <td class="text-right">{{ number_format($row['percentage'], 1) }}%</td>
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

    {{-- CORTES --}}
    <div class="section page-break">
        <div class="section-title">Detalle de cortes</div>
        <div class="box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Corte</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Abrió</th>
                        <th>Cerró</th>
                        <th class="text-right">Esperado</th>
                        <th class="text-right">Contado</th>
                        <th class="text-right">Diferencia</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cutRows as $row)
                        <tr>
                            <td>#{{ $row['id'] }}</td>

                            <td>
                                @if($row['status'] === 'abierta')
                                    <span class="badge badge-blue">Abierta</span>
                                @else
                                    <span class="badge badge-green">Cerrada</span>
                                @endif
                            </td>

                            <td>{{ $row['opened_at']->format('d/m/Y h:i A') }}</td>
                            <td>{{ $row['closed_at'] ? $row['closed_at']->format('d/m/Y h:i A') : 'Caja abierta' }}</td>
                            <td>{{ $row['opened_by'] }}</td>
                            <td>{{ $row['closed_by'] }}</td>
                            <td class="text-right">${{ number_format($row['expected_cash'], 2) }}</td>
                            <td class="text-right">
                                {{ $row['actual_cash'] !== null ? '$' . number_format($row['actual_cash'], 2) : 'Pendiente' }}
                            </td>
                            <td class="text-right {{ $row['difference'] < 0 ? 'red' : 'green' }}">
                                ${{ number_format($row['difference'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center small-muted">No se encontraron cortes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CORRECCIONES --}}
    <div class="section">
        <div class="section-title">Correcciones administrativas</div>
        <div class="box">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Corte</th>
                        <th>Administrador</th>
                        <th>Campo</th>
                        <th>Valor anterior</th>
                        <th>Valor nuevo</th>
                        <th>Motivo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentAdjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->created_at->format('d/m/Y h:i A') }}</td>
                            <td>#{{ $adjustment->cash_register_id }}</td>
                            <td>{{ $adjustment->user->name ?? 'Usuario no disponible' }}</td>
                            <td>{{ $adjustment->field_name }}</td>
                            <td>{{ $adjustment->old_value !== null && $adjustment->old_value !== '' ? $adjustment->old_value : 'Sin dato' }}</td>
                            <td>{{ $adjustment->new_value !== null && $adjustment->new_value !== '' ? $adjustment->new_value : 'Sin dato' }}</td>
                            <td>{{ $adjustment->reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center small-muted">No hay correcciones administrativas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
