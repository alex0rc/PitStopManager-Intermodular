<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e74c3c; padding-bottom: 20px; }
        .header img { width: 56px; height: 56px; border-radius: 10px; margin-bottom: 10px; }
        .header h1 { color: #e74c3c; margin: 0; font-size: 24px; }
        .header p { color: #666; margin: 5px 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 8px 0; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 180px; color: #555; }
        .amount { font-size: 22px; font-weight: bold; color: #e74c3c; text-align: center; margin: 20px 0; padding: 15px; background: #fdf2f2; border-radius: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 15px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-succeeded { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo.png') }}" alt="PitStop Manager" width="56" height="56">
        <h1>PitStop Manager</h1>
        <p>Comprobante de Pago</p>
    </div>

    <table class="info-table">
        <tr><td class="label">Nº Comprobante:</td><td>#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        <tr><td class="label">Fecha:</td><td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">Cliente:</td><td>{{ $user->name }}</td></tr>
        <tr><td class="label">Email:</td><td>{{ $user->email }}</td></tr>
        <tr><td class="label">Plan:</td><td>{{ $plan->name }}</td></tr>
        <tr><td class="label">Duración:</td><td>{{ $plan->duration_days }} días</td></tr>
        <tr><td class="label">Estado:</td><td><span class="status status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td></tr>
    </table>

    <div class="amount">
        Total: {{ number_format($payment->amount, 2) }} {{ strtoupper($payment->currency) }}
    </div>

    <div class="footer">
        <p>PitStop Manager - Plataforma de gestión de campeonatos de karting</p>
        <p>Este documento es un comprobante de pago generado automáticamente.</p>
    </div>
</body>
</html>
