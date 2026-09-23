<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permintaan Kontak Baru</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #1f2937;
            color: #ffffff;
            padding: 20px 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .body {
            padding: 24px;
        }
        .row {
            margin-bottom: 16px;
        }
        .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .value {
            font-size: 15px;
            color: #111827;
        }
        .footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Permintaan Kontak Baru</h1>
        </div>

        <div class="body">
            <div class="row">
                <div class="label">Nama</div>
                <div class="value">{{ $inquiry->name }}</div>
            </div>

            <div class="row">
                <div class="label">Email</div>
                <div class="value">{{ $inquiry->email }}</div>
            </div>

            @if ($inquiry->company)
                <div class="row">
                    <div class="label">Perusahaan</div>
                    <div class="value">{{ $inquiry->company }}</div>
                </div>
            @endif

            @if ($inquiry->phone)
                <div class="row">
                    <div class="label">Telepon</div>
                    <div class="value">{{ $inquiry->phone }}</div>
                </div>
            @endif

            @if ($inquiry->project_type)
                <div class="row">
                    <div class="label">Tipe Proyek</div>
                    <div class="value">{{ $inquiry->project_type }}</div>
                </div>
            @endif

            @if ($inquiry->timeline)
                <div class="row">
                    <div class="label">Timeline</div>
                    <div class="value">{{ $inquiry->timeline }}</div>
                </div>
            @endif

            <div class="row">
                <div class="label">Pesan</div>
                <div class="value">{{ $inquiry->message }}</div>
            </div>
        </div>

        <div class="footer">
            Dikirim {{ $inquiry->created_at?->format('d M Y H:i') }} melalui formulir kontak Senja.
        </div>
    </div>
</body>
</html>