<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
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
                'Ventas por periodo',
                ['Periodo', 'Ventas', 'Tickets'],
                $this->salesByPeriodRows()
            ),

            new SalesReportArraySheet(
                'Métodos de pago',
                ['Método', 'Monto', 'Tickets', 'Participación'],
                $this->paymentRows()
            ),

            new SalesReportArraySheet(
                'Productos vendidos',
                ['Producto', 'Unidades vendidas', 'Ingreso generado', 'Participación'],
                $this->productRows()
            ),

            new SalesReportArraySheet(
                'Tickets',
                ['Ticket', 'Fecha', 'Cajero', 'Cliente', 'Método', 'Estado', 'Total', 'Efectivo recibido', 'Cambio', 'Productos'],
                $this->ticketRows()
            ),

            new SalesReportArraySheet(
                'Cancelaciones',
                ['Ticket', 'Fecha', 'Cajero', 'Canceló', 'Total', 'Acción', 'Motivo'],
                $this->cancellationRows()
            ),

            new SalesReportArraySheet(
                'Gastos',
                ['Fecha', 'Registró', 'Categoría', 'Descripción', 'Estado', 'Monto', 'Canceló', 'Motivo cancelación'],
                $this->expenseRows()
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
            ['Reporte', 'Reporte de ventas', 'Archivo generado desde Cafetería Ignoto'],
            ['Periodo analizado', $this->data['periodLabel'], 'Rango consultado'],
            ['Generado por', $this->data['generatedBy'] ?? 'Sistema', 'Usuario que exportó el reporte'],
            ['Fecha de generación', $this->data['generatedAt'] ?? now()->format('d/m/Y H:i'), 'Fecha y hora de exportación'],

            ['Ventas operadas', $this->money($this->data['totalOperatedSales']), 'Efectivo + tarjeta + puntos'],
            ['Ingresos reales', $this->money($this->data['realIncome']), 'Efectivo + tarjeta'],
            ['Ventas en efectivo', $this->money($this->data['salesCash']), $this->data['cashOrdersCount'] . ' ticket(s)'],
            ['Ventas con tarjeta', $this->money($this->data['salesCard']), $this->data['cardOrdersCount'] . ' ticket(s)'],
            ['Ventas con puntos', $this->money($this->data['salesPoints']), $this->data['pointsOrdersCount'] . ' ticket(s)'],

            ['Gastos activos', $this->money($this->data['expensesTotal']), $this->data['expensesCount'] . ' gasto(s) activo(s)'],
            ['Utilidad operativa', $this->money($this->data['operatingProfit']), 'Ingresos reales - gastos activos'],

            ['Tickets completados', $this->data['completedOrdersCount'], 'Ventas activas del periodo'],
            ['Tickets cancelados', $this->data['cancelledOrdersCount'], $this->money($this->data['cancelledOrdersAmount']) . ' cancelados'],
            ['Ticket promedio', $this->money($this->data['averageTicket']), 'Promedio sobre tickets completados'],

            ['Efectivo recibido', $this->money($this->data['cashReceived']), 'Suma del efectivo recibido'],
            ['Cambio entregado', $this->money($this->data['cashChange']), 'Suma del cambio entregado'],
            ['Puntos ganados', $this->data['pointsEarned'], 'Puntos otorgados a clientes VIP'],
            ['Puntos usados', $this->data['pointsUsed'], 'Puntos canjeados por clientes VIP'],

            ['Interpretación', $this->data['executiveSummary'], 'Resumen ejecutivo automático'],
        ];
    }

    private function salesByPeriodRows(): array
    {
        return collect($this->data['salesByPeriod'])
            ->map(fn ($row) => [
                $row['label'],
                $this->money($row['sales']),
                $row['orders'],
            ])
            ->values()
            ->all();
    }

    private function paymentRows(): array
    {
        return collect($this->data['paymentBreakdown'])
            ->map(fn ($payment) => [
                $payment['label'],
                $this->money($payment['amount']),
                $payment['count'],
                $this->percent($payment['percentage']),
            ])
            ->values()
            ->all();
    }

    private function productRows(): array
    {
        return collect($this->data['topProducts'])
            ->map(fn ($product) => [
                $product->name,
                $product->total_sold,
                $this->money($product->total_revenue),
                $this->percent($product->percentage),
            ])
            ->values()
            ->all();
    }

    private function ticketRows(): array
    {
        return collect($this->data['orders'])
            ->map(function ($order) {
                $products = $order->items
                    ->map(fn ($item) => $item->quantity . 'x ' . ($item->product->name ?? 'Producto no disponible'))
                    ->implode('; ');

                return [
                    '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    $order->created_at->format('d/m/Y h:i A'),
                    $order->user->name ?? 'Caja',
                    $order->customer->name ?? 'Público general',
                    strtoupper($order->payment_method),
                    strtoupper($order->status),
                    $this->money($order->total),
                    $order->payment_method === 'efectivo' ? $this->money($order->cash_received ?? $order->total) : '',
                    $order->payment_method === 'efectivo' ? $this->money($order->cash_change ?? 0) : '',
                    $products,
                ];
            })
            ->values()
            ->all();
    }

    private function cancellationRows(): array
    {
        return collect($this->data['cancelledOrders'])
            ->map(fn ($order) => [
                '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                $order->created_at->format('d/m/Y h:i A'),
                $order->user->name ?? 'Caja',
                $order->canceller->name ?? 'No registrado',
                $this->money($order->total),
                $order->cancellation_action ?? 'Sin acción',
                $order->cancellation_reason ?? 'Sin motivo',
            ])
            ->values()
            ->all();
    }

    private function expenseRows(): array
    {
        return collect($this->data['expenses'])
            ->map(fn ($expense) => [
                $expense->created_at->format('d/m/Y h:i A'),
                $expense->user->name ?? 'Usuario',
                $expense->category ?? 'Sin categoría',
                $expense->description,
                strtoupper($expense->status),
                $this->money($expense->amount),
                $expense->canceller->name ?? '',
                $expense->cancellation_reason ?? '',
            ])
            ->values()
            ->all();
    }
}