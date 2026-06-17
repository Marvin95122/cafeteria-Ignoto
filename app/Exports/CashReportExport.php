<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CashReportExport implements WithMultipleSheets
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new SalesReportArraySheet(
                'Resumen',
                ['Concepto', 'Valor', 'Detalle'],
                $this->summaryRows()
            ),

            new SalesReportArraySheet(
                'Caja por periodo',
                ['Periodo', 'Efectivo esperado', 'Efectivo contado', 'Diferencia', 'Cortes'],
                $this->cashByPeriodRows()
            ),

            new SalesReportArraySheet(
                'Cortes',
                [
                    'Corte',
                    'Estado',
                    'Apertura',
                    'Cierre',
                    'Abrió',
                    'Cerró',
                    'Fondo inicial',
                    'Ventas efectivo',
                    'Ventas tarjeta',
                    'Ventas puntos',
                    'Gastos activos',
                    'Efectivo esperado',
                    'Efectivo contado',
                    'Diferencia',
                    'Tickets completados',
                    'Tickets cancelados',
                    'Gastos activos',
                    'Gastos cancelados',
                    'Correcciones',
                    'Notas'
                ],
                $this->cutRows()
            ),

            new SalesReportArraySheet(
                'Mayores diferencias',
                ['Corte', 'Fecha apertura', 'Efectivo esperado', 'Efectivo contado', 'Diferencia', 'Abrió', 'Cerró'],
                $this->topDifferenceRows()
            ),

            new SalesReportArraySheet(
                'Gastos por categoría',
                ['Categoría', 'Monto', 'Cantidad de gastos', 'Participación'],
                $this->expensesCategoryRows()
            ),

            new SalesReportArraySheet(
                'Correcciones',
                ['Fecha', 'Corte', 'Administrador', 'Campo', 'Valor anterior', 'Valor nuevo', 'Motivo'],
                $this->adjustmentRows()
            ),
        ];
    }

    private function money(float|int|null $value): string
    {
        return '$' . number_format((float) $value, 2);
    }

    private function percent(float|int|null $value): string
    {
        return number_format((float) $value, 1) . '%';
    }

    private function summaryRows(): array
    {
        return [
            ['Reporte', 'Reporte de caja', 'Archivo generado desde Cafetería Ignoto'],
            ['Periodo analizado', $this->data['periodLabel'], 'Rango consultado'],
            ['Generado por', $this->data['generatedBy'] ?? 'Sistema', 'Usuario que exportó el reporte'],
            ['Fecha de generación', $this->data['generatedAt'] ?? now()->format('d/m/Y H:i'), 'Fecha y hora de exportación'],

            ['Cortes registrados', $this->data['totalCuts'], $this->data['closedCuts'] . ' cerrados · ' . $this->data['openCuts'] . ' abiertos'],
            ['Efectivo esperado', $this->money($this->data['expectedTotal']), 'Fondo + ventas en efectivo - gastos activos'],
            ['Efectivo contado', $this->money($this->data['actualTotal']), 'Suma de efectivo contado en cierres'],
            ['Diferencia acumulada', $this->money($this->data['differenceTotal']), 'Efectivo contado - efectivo esperado'],

            ['Ventas en efectivo', $this->money($this->data['salesCashTotal']), 'Ventas completadas pagadas en efectivo'],
            ['Ventas con tarjeta', $this->money($this->data['salesCardTotal']), 'Ventas completadas pagadas con tarjeta'],
            ['Ventas con puntos', $this->money($this->data['salesPointsTotal']), 'Valor canjeado con puntos VIP'],

            ['Gastos activos', $this->money($this->data['expensesTotal']), 'Gastos activos asociados a los cortes'],
            ['Flujo de efectivo', $this->money($this->data['cashFlow']), 'Ventas efectivo - gastos activos'],

            ['Tickets completados', $this->data['completedOrdersCount'], 'Tickets completados dentro de los cortes'],
            ['Tickets cancelados', $this->data['cancelledOrdersCount'], 'Tickets cancelados dentro de los cortes'],

            ['Cortes con sobrante', $this->data['cutsWithPositiveDifference'], 'Diferencia mayor a cero'],
            ['Cortes con faltante', $this->data['cutsWithNegativeDifference'], 'Diferencia menor a cero'],
            ['Cortes sin diferencia', $this->data['cutsWithoutDifference'], 'Diferencia igual a cero'],

            ['Correcciones administrativas', $this->data['adjustmentsCount'], 'Cambios auditados en cortes'],
            ['Interpretación', $this->data['executiveSummary'], 'Resumen ejecutivo automático'],
        ];
    }

    private function cashByPeriodRows(): array
    {
        return collect($this->data['cashByPeriod'])
            ->map(fn ($row) => [
                $row['label'],
                $this->money($row['expected']),
                $this->money($row['actual']),
                $this->money($row['difference']),
                $row['cuts'],
            ])
            ->values()
            ->all();
    }

    private function cutRows(): array
    {
        return collect($this->data['cutRows'])
            ->map(fn ($row) => [
                '#' . $row['id'],
                strtoupper($row['status']),
                $row['opened_at'] ? $row['opened_at']->format('d/m/Y h:i A') : '',
                $row['closed_at'] ? $row['closed_at']->format('d/m/Y h:i A') : 'Caja abierta',
                $row['opened_by'],
                $row['closed_by'],
                $this->money($row['opening_amount']),
                $this->money($row['sales_cash']),
                $this->money($row['sales_card']),
                $this->money($row['sales_points']),
                $this->money($row['expenses_total']),
                $this->money($row['expected_cash']),
                $row['actual_cash'] !== null ? $this->money($row['actual_cash']) : 'Pendiente',
                $this->money($row['difference']),
                $row['completed_orders_count'],
                $row['cancelled_orders_count'],
                $row['active_expenses_count'],
                $row['cancelled_expenses_count'],
                $row['adjustments_count'],
                $row['notes'] ?? '',
            ])
            ->values()
            ->all();
    }

    private function topDifferenceRows(): array
    {
        return collect($this->data['topDifferences'])
            ->map(fn ($row) => [
                '#' . $row['id'],
                $row['opened_at'] ? $row['opened_at']->format('d/m/Y h:i A') : '',
                $this->money($row['expected_cash']),
                $row['actual_cash'] !== null ? $this->money($row['actual_cash']) : 'Pendiente',
                $this->money($row['difference']),
                $row['opened_by'],
                $row['closed_by'],
            ])
            ->values()
            ->all();
    }

    private function expensesCategoryRows(): array
    {
        return collect($this->data['expensesByCategory'])
            ->map(fn ($row) => [
                $row['category'],
                $this->money($row['amount']),
                $row['count'],
                $this->percent($row['percentage']),
            ])
            ->values()
            ->all();
    }

    private function adjustmentRows(): array
    {
        return collect($this->data['recentAdjustments'])
            ->map(fn ($adjustment) => [
                $adjustment->created_at->format('d/m/Y h:i A'),
                '#' . $adjustment->cash_register_id,
                $adjustment->user->name ?? 'Usuario no disponible',
                $adjustment->field_name,
                $adjustment->old_value !== null && $adjustment->old_value !== '' ? $adjustment->old_value : 'Sin dato',
                $adjustment->new_value !== null && $adjustment->new_value !== '' ? $adjustment->new_value : 'Sin dato',
                $adjustment->reason,
            ])
            ->values()
            ->all();
    }
}