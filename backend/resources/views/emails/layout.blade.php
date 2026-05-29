<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PitStop Manager')</title>
    <style>
        body { margin: 0; padding: 0; background: #f5f6fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; color: #1f2937; }
        .container { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 24px rgba(15,23,42,0.08); }
        .header { background: linear-gradient(135deg, #0b1224 0%, #1a1a2e 50%, #0f172a 100%); padding: 32px 32px 28px; color: #ffffff; }
        .header .brand-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .header .brand-logo { width: 48px; height: 48px; border-radius: 10px; display: block; }
        .header .brand-name { font-size: 13px; font-weight: 700; letter-spacing: 0.06em; color: rgba(255,255,255,0.9); }
        .header .brand-name span { color: #e11d2e; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.01em; }
        .header .accent { color: #e11d2e; }
        .body { padding: 28px 32px 8px; line-height: 1.55; font-size: 15px; }
        .body p { margin: 0 0 14px; }
        .body h2 { font-size: 18px; margin: 0 0 12px; }
        .cta { display: inline-block; background: #e11d2e; color: #ffffff !important; padding: 12px 22px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 14px; }
        .info-card { background: #f5f6fa; border-radius: 10px; padding: 16px 18px; margin: 12px 0; }
        .info-row { display: table; width: 100%; padding: 4px 0; }
        .info-row .label { display: table-cell; color: #6b7280; font-size: 13px; width: 45%; }
        .info-row .value { display: table-cell; font-weight: 600; font-size: 14px; }
        .pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .pill-success { background: rgba(22,163,74,0.12); color: #15803d; }
        .pill-warning { background: rgba(245,158,11,0.15); color: #b45309; }
        .pill-danger  { background: rgba(220,38,38,0.12);  color: #b91c1c; }
        .footer { padding: 20px 32px 28px; color: #9ca3af; font-size: 12px; text-align: center; border-top: 1px solid #eef0f5; margin-top: 12px; }
        .footer a { color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr><td>
            <div class="container">
                <div class="header">
                    <div class="brand-row">
                        <img src="{{ asset('logo.png') }}" alt="PitStop Manager" class="brand-logo" width="48" height="48">
                        <div class="brand-name">Pit<span>Stop</span> Manager</div>
                    </div>
                    <h1>@yield('heading', 'Hola desde la pista')</h1>
                </div>
                <div class="body">
                    @yield('content')
                </div>
                <div class="footer">
                    <p>PitStop <span style="color:#e11d2e;font-weight:700">Manager</span> · Karting amateur, gestionado con estilo.</p>
                    <p style="margin-top:8px">¿Necesitas ayuda? Responde a este correo y te ayudaremos.</p>
                </div>
            </div>
        </td></tr>
    </table>
</body>
</html>
