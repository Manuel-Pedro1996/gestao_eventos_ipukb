<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lista de Presenças - {{ $evento->titulo }}</title>
    <style>
        body { font-family: sans-serif; padding: 40px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { bg-color: #f4f4f4; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Confirmar Impressão</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer;">Voltar</button>
    </div>

    <div class="header">
        <h3>Lista Oficial de Presenças</h3>
        <p><strong>Evento:</strong> {{ $evento->titulo }}</p>
        <p><strong>Data do Evento:</strong> {{ $evento->data_evento->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome do Participante</th>
                <th>E-mail</th>
                <th>Hora de Check-in</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presencas as $index => $p)
                <tr>
                    <td>{{ $p->inscricao->participante->name }}</td>
                    <td>{{ $p->inscricao->participante->email }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->data_checkin)->format('H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        <p>__________________________________________</p>
        <p>Assinatura do Responsável</p>
    </div>
</body>
</html>