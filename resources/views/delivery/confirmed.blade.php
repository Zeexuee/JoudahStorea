<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 32px;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .success { background: #dcfce7; color: #166534; }
        .info { background: #dbeafe; color: #1d4ed8; }
        .error { background: #fee2e2; color: #b91c1c; }
        h1 { margin: 0 0 12px; font-size: 28px; }
        p { line-height: 1.6; margin: 0 0 12px; }
        .meta {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            @php
                $level = $result['level'] ?? 'info';
                $message = $result['message'] ?? 'Status pesanan telah diproses.';
            @endphp

            <div class="badge {{ $level }}">
                {{ strtoupper($level) }}
            </div>

            <h1>Konfirmasi Pesanan</h1>
            <p>{{ $message }}</p>

            <div class="meta">
                <div><strong>Nomor Pesanan:</strong> {{ $order->order_number }}</div>
                <div><strong>Status:</strong> {{ $order->status }}</div>
                <div><strong>Waktu:</strong> {{ now()->format('d M Y H:i') }}</div>
            </div>

            @auth
                <a class="btn" href="{{ route('orders.show', $order) }}">Lihat Detail Pesanan</a>
            @endauth
        </div>
    </div>
</body>
</html>