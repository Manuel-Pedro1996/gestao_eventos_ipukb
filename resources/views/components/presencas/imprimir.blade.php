<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Nominal de Presenças - {{ $evento->titulo }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 20mm 15mm;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        /* Botões de controle de tela */
        .no-print {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-primary { background-color: #2563eb; color: #ffffff; }
        .btn-secondary { background-color: #64748b; color: #ffffff; }

        /* Cabeçalho com Logótipos */
        .header-container {
            width: 100%;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-container td {
            vertical-align: middle;
        }

        .logo-img {
            max-height: 65px;
            max-width: 120px;
            object-fit: contain;
        }

        .header-title {
            text-align: center;
        }

        .header-title h1 {
            font-size: 16pt;
            margin: 0 0 4px 0;
            color: #0f172a;
            text-transform: uppercase;
        }

        .meta-grid {
            width: 100%;
            margin-top: 10px;
            font-size: 10pt;
        }

        .meta-grid td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* Tabela Principal */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }

        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 10pt;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Rodapé e Assinatura */
        .footer {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-container {
            width: 100%;
            margin-top: 50px;
        }

        .signature-box {
            width: 250px;
            float: right;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #0f172a;
            margin-bottom: 5px;
        }

        .document-info {
            font-size: 8pt;
            color: #64748b;
            margin-top: 20px;
            text-align: left;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Lista</button>
        <button onclick="window.history.back()" class="btn btn-secondary">Voltar</button>
    </div>

  {{-- ESTRUTURA DO CABEÇALHO COM OS DOIS ÍCONES/LOGÓTIPOS --}}
<table class="header-container">
    <tr>
        <!-- Ícone/Logótipo Esquerdo (Sistema) -->
        <td style="width: 20%; text-align: left;">
            <img src="{{ asset('img/ti.png') }}" alt="Logótipo Sistema" class="logo-img">
        </td>

        <!-- Título Central -->
        <td style="width: 60%;" class="header-title">
            <h1>Lista Oficial de Presenças</h1>
        </td>

        <!-- Ícone/Logótipo Direito (Evento) -->
        <td style="width: 20%; text-align: right;">
            @if (!empty($evento->foto))
                @if (\Illuminate\Support\Str::startsWith($evento->foto, ['http://', 'https://']))
                    <img src="{{ $evento->foto }}" alt="Logótipo Evento" class="logo-img">
                @else
                    <img src="{{ asset('storage/' . ltrim($evento->foto, '/')) }}" alt="Logótipo Evento" class="logo-img">
                @endif
            @else
                <img src="{{ asset('img/ti.png') }}" alt="Logótipo Evento" class="logo-img" style="opacity: 0.3;">
            @endif
        </td>
    </tr>
</table>

    {{-- METADADOS DO EVENTO --}}
<table class="meta-grid">
    <tr>
        <td style="width: 50%; text-align: left;">
            <strong>Evento:</strong> {{ $evento->titulo }}
        </td>
        <td style="width: 50%; text-align: right;">
            <strong>Data:</strong> {{ $evento->data_evento ? \Carbon\Carbon::parse($evento->data_evento)->format('d/m/Y H:i') : 'N/D' }}
        </td>
    </tr>
    <tr>
        <td style="width: 50%; text-align: left;">
            <strong>Local:</strong> {{ $evento->local ?? 'Não especificado' }}
        </td>
        <td style="width: 50%; text-align: right;">
            <strong>Total de Presentes:</strong> {{ count($presencas) }}
        </td>
    </tr>
</table>

    {{-- TABELA DE PRESENÇAS --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 45%;">Nome do Participante</th>
                <th style="width: 30%;">E-mail</th>
                <th style="width: 20%; text-align: center;">Hora de Check-in</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presencas as $index => $p)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td>{{ $p->inscricao->participante->name ?? 'N/A' }}</td>
                    <td>{{ $p->inscricao->participante->email ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        {{ $p->data_checkin ? \Carbon\Carbon::parse($p->data_checkin)->format('H:i:s') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">
                        Nenhuma presença registrada para este evento até o momento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- RODAPÉ DE ASSINATURA --}}
    <div class="footer">
        <div class="signature-container">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="font-size: 9pt; font-weight: bold;">Assinatura do Responsável</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="document-info">
            Documento gerado automaticamente pelo sistema em {{ now()->format('d/m/Y \à\s H:i:s') }}.
        </div>
    </div>

</body>
</html>