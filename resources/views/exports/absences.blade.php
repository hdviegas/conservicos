<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Faltas e Atestados</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #b91c1c; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #b91c1c; margin: 0 0 4px; }
        .header p { margin: 2px 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #b91c1c; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) td { background-color: #fef2f2; }
        .badge-sim { background: #dcfce7; color: #166534; padding: 1px 6px; border-radius: 4px; }
        .badge-nao { background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 4px; }
        .footer { margin-top: 20px; text-align: right; color: #9ca3af; font-size: 9px; }
        .summary { margin-bottom: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 12px; }
        .summary span { margin-right: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Faltas e Atestados</h1>
        <p>Período: {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</p>
        @if($company)
            <p>Empresa: {{ $company }}</p>
        @endif
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <span><strong>Total de Registros:</strong> {{ count($absences) }}</span>
        <span><strong>Justificadas:</strong> {{ collect($absences)->where('justified', true)->count() }}</span>
        <span><strong>Injustificadas:</strong> {{ collect($absences)->where('justified', false)->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Funcionário</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Just.</th>
                <th>CID</th>
                <th>Dias</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absences as $absence)
                <tr>
                    <td>{{ $absence->employee->matricula ?? '' }}</td>
                    <td>{{ $absence->employee->name ?? '' }}</td>
                    <td>{{ $absence->date->format('d/m/Y') }}</td>
                    <td>{{ $absence->type instanceof \App\Enums\AbsenceType ? $absence->type->label() : $absence->type }}</td>
                    <td>
                        @if($absence->justified)
                            <span class="badge-sim">Sim</span>
                        @else
                            <span class="badge-nao">Não</span>
                        @endif
                    </td>
                    <td>{{ $absence->cid_code ?? '' }}</td>
                    <td>{{ $absence->days_count }}</td>
                    <td>{{ $absence->notes ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#6b7280; padding: 20px;">
                        Nenhuma falta registrada para este período.
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
