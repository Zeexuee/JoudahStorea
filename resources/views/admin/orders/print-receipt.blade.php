<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengiriman - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #f5f5f5;
        }

        .print-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Header */
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .receipt-header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .receipt-header p {
            font-size: 12px;
            color: #666;
        }

        /* Content Sections */
        .receipt-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            background-color: #2563eb;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .section-content {
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        /* Resi/Tracking Section - Highlighted */
        .resi-box {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .resi-box .label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .resi-box .number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .resi-box .courier {
            font-size: 14px;
            opacity: 0.95;
        }

        /* Order Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 12px;
            background-color: white;
            border-left: 4px solid #2563eb;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }

        /* Customer & Shipping Info */
        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .address-box {
            padding: 15px;
            background-color: white;
            border: 1px dashed #d1d5db;
            border-radius: 4px;
        }

        .address-box h3 {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .address-box p {
            font-size: 13px;
            line-height: 1.6;
            color: #333;
        }

        /* Order Items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background-color: #2563eb;
            color: white;
        }

        .items-table th {
            padding: 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #2563eb;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Total */
        .total-section {
            text-align: right;
            margin-bottom: 25px;
            padding-top: 15px;
            border-top: 2px solid #d1d5db;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .total-row .label {
            width: 150px;
            text-align: right;
            margin-right: 10px;
            color: #666;
        }

        .total-row .value {
            width: 120px;
            text-align: right;
            font-weight: bold;
        }

        .final-total {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #2563eb;
        }

        .final-total .label {
            width: 150px;
            text-align: right;
            margin-right: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .final-total .value {
            width: 120px;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            color: #2563eb;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #666;
        }

        .print-info {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 15px;
        }

        /* Print Styles */
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }

            .print-container {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
                padding: 20px;
            }

            .no-print {
                display: none;
            }

            a {
                color: #2563eb;
                text-decoration: underline;
            }
        }

        /* Print Button */
        .print-button-container {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .print-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .print-btn:hover {
            background-color: #1e40af;
        }

        .back-btn {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #4b5563;
        }

        /* Estimated Delivery Box */
        .estimated-box {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .estimated-box .label {
            font-size: 11px;
            color: #047857;
            text-transform: uppercase;
            font-weight: bold;
        }

        .estimated-box .date {
            font-size: 16px;
            font-weight: bold;
            color: #065f46;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Print Controls (Hide on Print) -->
        <div class="print-button-container no-print">
            <button class="print-btn" onclick="window.print()">🖨️ Cetak/Print</button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="back-btn">← Kembali</a>
        </div>

        <!-- Header -->
        <div class="receipt-header">
            <h1>📦 BUKTI PENGIRIMAN</h1>
            <p>Shipping Receipt / Resi Pengiriman</p>
            <p style="margin-top: 8px; font-size: 11px;">{{ now()->format('d F Y, H:i:s') }}</p>
        </div>

        <!-- Resi Box (Prominent) -->
        <div class="resi-box">
            <div class="label">📍 Nomor Resi / Tracking Number</div>
            <div class="number">{{ $order->shipping->tracking_number }}</div>
            <div class="courier">
                🚚 {{ $order->shipping->courier_name ?? 'Kurir' }}
                @if($order->shipping->service)
                    - {{ $order->shipping->service }}
                @endif
            </div>
        </div>

        <!-- Estimated Delivery -->
        @if($order->shipping->estimated_delivery)
            <div class="estimated-box">
                <div class="label">📅 Estimasi Tiba / Estimated Delivery</div>
                <div class="date">{{ $order->shipping->estimated_delivery->format('d F Y') }}</div>
            </div>
        @endif

        <!-- Order Information -->
        <div class="receipt-section">
            <div class="section-title">Informasi Pesanan</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nomor Pesanan</div>
                    <div class="info-value">{{ $order->order_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Pesanan</div>
                    <div class="info-value">{{ $order->created_at->format('d F Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status Pesanan</div>
                    <div class="info-value">
                        @php
                            $statuses = [
                                'pending' => 'Menunggu Pembayaran',
                                'processing' => 'Sudah Dibayar',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Terima',
                                'cancelled' => 'Dibatalkan'
                            ];
                        @endphp
                        {{ $statuses[$order->status] ?? $order->status }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status Pembayaran</div>
                    <div class="info-value">
                        @if($order->payment?->status === 'completed')
                            ✓ Lunas
                        @else
                            Menunggu
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="receipt-section">
            <div class="section-title">Alamat Pengiriman</div>
            <div class="address-section">
                <div class="address-box">
                    <h3>📋 Pengirim / From</h3>
                    <p>
                        <strong>{{ config('app.name', 'Joudah Store') }}</strong><br>
                        <em>Gudang Pusat</em>
                    </p>
                </div>
                <div class="address-box">
                    <h3>📮 Penerima / To</h3>
                    <p>
                        <strong>{{ $order->shipping_name }}</strong><br>
                        {{ $order->shipping_phone }}<br>
                        {{ $order->shipping_address }}<br>
                        @if($order->shipping_city)
                            {{ $order->shipping_city }}, {{ $order->shipping_province }}<br>
                        @endif
                        {{ $order->shipping_postal_code }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="receipt-section">
            <div class="section-title">Detail Barang</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Produk</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 20%; text-align: right;">Harga</th>
                        <th style="width: 20%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product->name ?? 'Produk Tidak Ditemukan' }}</strong><br>
                                <small style="color: #999;">SKU: {{ $item->product->sku ?? '-' }}</small>
                            </td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="receipt-section">
            <div class="section-title">Detail Biaya</div>
            <div class="total-section">
                @php
                    // Calculate subtotal from items
                    $itemsTotal = $order->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                @endphp
                <div class="total-row">
                    <span class="label">Subtotal Barang</span>
                    <span class="value">Rp{{ number_format($itemsTotal, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span class="label">Ongkos Kirim</span>
                    <span class="value">Rp{{ number_format($order->shipping->cost ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="final-total">
                    <span class="label">TOTAL</span>
                    <span class="value">Rp{{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Shipping Notes -->
        @if($order->shipping->notes)
            <div class="receipt-section">
                <div class="section-title">Catatan Pengiriman</div>
                <div class="section-content">
                    {{ $order->shipping->notes }}
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            <p>{{ config('app.name', 'Joudah Store') }} - Sistem Manajemen Pesanan</p>
            <p>📞 Hubungi Customer Service untuk bantuan</p>
            <div class="print-info no-print">
                <p>Tanggal cetak: {{ now()->format('d F Y, H:i:s') }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print on page load (optional - comment out if you want manual print)
        // window.print();
    </script>
</body>
</html>
