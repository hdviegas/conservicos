<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Compliance</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7e22ce; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #7e22ce; margin: 0 0 4px; }
        .header p { margin: 2px 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #7e22ce; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) td { background-color: #faf5ff; }
        .badge { padding: 1px 6px; border-radius: 4px; font-size: 9px; }
        .badge-expired { background: #fee2e2; color: #991b1b; }
        .badge-15d { background: #ffedd5; color: #9a3412; }
        .badge-30d { background: #fef9c3; color: #713f12; }
        .badge-valid { background: #dcfce7; color: #166534; }
        .footer { margin-top: 20px; text-align: right; color: #9ca3af; font-size: 9px; }
        .summary { margin-bottom: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 12px; }
        .summary span { margin-right: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Exames e Treinamentos</h1>
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        @if($company)
            <p>Empresa: {{ $company }}</p>
        @endif
    </div>

    <div class="summary">
        <span><strong>Total de Itens:</strong> {{ count($items) }}</span>
        <span><strong>Vencidos:</strong> {{ collect($items)->filter(fn($i) => $i['status']->value === 'expired')->count() }}</span>
        <span><strong>Vencendo 15d:</strong> {{ collect($items)->filter(fn($i) => $i['status']->value === 'expiring_15d')->count() }}</span>
        <span><strong>Vencendo 30d:</strong> {{ collect($items)->filter(fn($i) => $i['status']->value === 'expiring_30d')->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Cargo</th>
                <th>Empresa</th>
                <th>Tipo</th>
                <th>Item</th>
                <th>Realização</th>
                <th>Vencimento</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $status = $item['status'];
                    $badgeClass = match($status->value ?? '') {
                        'expired'      => 'badge-expired',
                        'expiring_15d' => 'badge-15d',
                        'expiring_30d' => 'badge-30d',
                        default        => 'badge-valid',
                    };
                @endphp
                <tr>
                    <td>{{ $item['employee']->name ?? '' }}</td>
                    <td>{{ $item['employee']->position->name ?? '' }}</td>
                    <td>{{ $item['employee']->company->name ?? '' }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['item_name'] }}</td>
                    <td>{{ $item['performed_date'] ? \Carbon\Carbon::parse($item['performed_date'])->format('d/m/Y') : '' }}</td>
                    <td>{{ $item['expiration_date'] ? \Carbon\Carbon::parse($item['expiration_date'])->format('d/m/Y') : '' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $status instanceof \App\Enums\ComplianceStatus ? $status->label() : $status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#6b7280; padding: 20px;">
                        Nenhum item de compliance pendente encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        CONSERVICOS &mdash; Relatório gerado automaticamente
    </div>
</body>
</html>
