<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $salary->teacher->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .header-logo {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 15px;
            justify-content: center;
        }

        .header-logo img {
            height: 70px;
            width: auto;
            object-fit: contain;
        }

        .header-info {
            font-size: 13px;
            color: #333;
            line-height: 1.4;
            text-align: left;
        }

        .header-info h2 {
            font-size: 20px;
            margin: 0;
            color: #333;
            font-weight: bold;
        }

        .header-info p {
            margin: 4px 0;
            color: #666;
            font-size: 12px;
        }

        .slip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }

        .slip-info .left,
        .slip-info .right {
            flex: 1;
        }

        .slip-info .right {
            text-align: right;
        }

        .info-row {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-label {
            color: #666;
            font-weight: 600;
            display: inline-block;
            width: 150px;
        }

        .info-value {
            color: #333;
        }

        .salary-table {
            width: 100%;
            margin: 30px 0;
            border-collapse: collapse;
        }

        .salary-table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        .salary-table td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        .salary-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: 600;
        }

        .total-row td {
            padding: 15px 12px;
        }

        .amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-box p {
            font-size: 12px;
            color: #666;
            margin-bottom: 60px;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 20mm;
            }
        }

        .print-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="print-btn no-print" style="display: block; margin-left: auto; margin-right: 0;" onclick="window.print()">🖨️ Cetak / Print</button>

        <div class="header">
            {{-- KOP SURAT --}}
            {{-- <div class="header-logo">
                @if($schoolProfile?->logo_path && is_string($schoolProfile->logo_path))
                    <img src="{{ asset('storage/' . $schoolProfile->logo_path) }}" alt="Logo Sekolah">
                @else
                    <div style="font-size: 50px; display: flex; align-items: center; justify-content: center; height: 70px; width: 70px; background: #f0f0f0; border-radius: 8px;">
                        🏫
                    </div>
                @endif
                <div class="header-info">
                    <h2>{{ $schoolProfile?->name ?? 'Nama Sekolah' }}</h2>
                    @if($schoolProfile?->npsn)
                        <p>NPSN: {{ $schoolProfile->npsn }}</p>
                    @endif
                    @if($schoolProfile?->address)
                        <p>{{ $schoolProfile->address }}</p>
                    @endif
                    @if($schoolProfile?->phone || $schoolProfile?->email)
                        <p>
                            @if($schoolProfile->phone) {{ $schoolProfile->phone }} @endif
                            @if($schoolProfile->email) | {{ $schoolProfile->email }} @endif
                        </p>
                    @endif
                </div>
            </div> --}}
            <div style="margin-top: 20px;">
                <h1>SLIP GAJI</h1>
                <p>Bulan {{ $salary->getMonthName() }} Tahun {{ $salary->year }}</p>
            </div>
        </div>

        <div class="slip-info">
            <div class="left">
                <div class="info-row">
                    <span class="info-label">Nama Guru</span>
                    <span class="info-value">: {{ $salary->teacher->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIP</span>
                    <span class="info-value">: {{ $salary->teacher->nip }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">: {{ $salary->teacher->email ?? '-' }}</span>
                </div>
            </div>
            <div class="right">
                <div class="info-row">
                    <span class="info-label">Periode</span>
                    <span class="info-value">: {{ $salary->getMonthName() }} {{ $salary->year }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Cetak</span>
                    <span class="info-value">: {{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <table class="salary-table">
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th style="text-align: right;">Jam</th>
                    <th style="text-align: right;">Tarif/Jam</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Jam Hadir</td>
                    <td class="amount">{{ $salary->attended_hours }}</td>
                    <td class="amount">Rp {{ number_format(7500, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($salary->attended_hours * 7500, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Jam Tidak Hadir</td>
                    <td class="amount">{{ $salary->absent_hours }}</td>
                    <td class="amount">Rp {{ number_format(3500, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($salary->absent_hours * 3500, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3">GAJI POKOK</td>
                    <td class="amount">Rp {{ number_format(($salary->attended_hours * 7500) + ($salary->absent_hours * 3500), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2">Tunjangan Jabatan ({{ $salary->teacher->position ?? '-' }})</td>
                    <td colspan="2" class="amount">
                        @php
                            $positionAllowance = 0;
                            if ($salary->teacher->position) {
                                $positionSalary = \App\Models\PositionSalary::where('position', $salary->teacher->position)
                                    ->where('is_active', true)
                                    ->first();
                                $positionAllowance = $positionSalary ? $positionSalary->salary_adjustment : 0;
                            }
                        @endphp
                        Rp {{ number_format($positionAllowance, 0, ',', '.') }}
                    </td>
                </tr>
                @if($salary->additional_amount > 0)
                    <tr>
                        <td colspan="2">Tambahan Gaji{{ $salary->additional_notes ? ': ' . $salary->additional_notes : '' }}</td>
                        <td colspan="2" class="amount">Rp {{ number_format($salary->additional_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3">TOTAL SEBELUM POTONGAN</td>
                    <td class="amount"><strong>Rp {{ number_format($salary->total_amount + $salary->additional_amount, 0, ',', '.') }}</strong></td>
                </tr>
                @if($salary->deductions_amount > 0)
                    <tr style="background-color: #ffe6e6;">
                        <td colspan="2">Potongan{{ $salary->deductions_notes ? ': ' . $salary->deductions_notes : '' }}</td>
                        <td colspan="2" class="amount" style="color: #d32f2f;">Rp {{ number_format($salary->deductions_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-row" style="background-color: #e3f2fd;">
                    <td colspan="3"><strong>GAJI BERSIH YANG DITERIMA</strong></td>
                    <td class="amount"><strong style="font-size: 1.1em; color: #1565c0;">Rp {{ number_format(($salary->total_amount + $salary->additional_amount) - $salary->deductions_amount, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div style="margin: 20px 0; padding: 15px; background-color: #f0f0f0; border-radius: 5px;">
            <p style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 8px;">📋 RINCIAN PERHITUNGAN GAJI:</p>
            <table style="width: 100%; font-size: 13px; line-height: 1.6;">
                <tr>
                    <td style="color: #666;">1. Gaji Hadir</td>
                    <td style="color: #666;">{{ $salary->attended_hours }} jam × Rp 7.500</td>
                    <td style="text-align: right; color: #333; font-weight: 500;">= Rp {{ number_format($salary->attended_hours * 7500, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="color: #666;">2. Honor Tidak Hadir</td>
                    <td style="color: #666;">{{ $salary->absent_hours }} jam × Rp 3.500</td>
                    <td style="text-align: right; color: #333; font-weight: 500;">= Rp {{ number_format($salary->absent_hours * 3500, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="color: #666;">3. Tunjangan Jabatan</td>
                    <td style="color: #666;">({{ $salary->teacher->position ?? '-' }})</td>
                    <td style="text-align: right; color: #333; font-weight: 500;">
                        = Rp {{ number_format(
                            $salary->teacher->position
                                ? (\App\Models\PositionSalary::where('position', $salary->teacher->position)->where('is_active', true)->first()?->salary_adjustment ?? 0)
                                : 0,
                            0, ',', '.'
                        ) }}
                    </td>
                </tr>
                @if($salary->additional_amount > 0)
                    <tr>
                        <td style="color: #666;">4. Tambahan Gaji</td>
                        <td style="color: #666;">{{ $salary->additional_notes ?? 'Tambahan' }}</td>
                        <td style="text-align: right; color: #333; font-weight: 500;">+ Rp {{ number_format($salary->additional_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr style="border-top: 2px solid #999; font-weight: 600;">
                    <td colspan="2" style="padding-top: 8px;">SUBTOTAL (Sebelum Potongan)</td>
                    <td style="text-align: right; padding-top: 8px; color: #333;">Rp {{ number_format($salary->total_amount + $salary->additional_amount, 0, ',', '.') }}</td>
                </tr>
                @if($salary->deductions_amount > 0)
                    <tr>
                        <td style="color: #d32f2f;">5. Potongan</td>
                        <td style="color: #d32f2f;">{{ $salary->deductions_notes ?? 'Potongan' }}</td>
                        <td style="text-align: right; color: #d32f2f; font-weight: 500;">- Rp {{ number_format($salary->deductions_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr style="border-top: 2px solid #333; font-weight: 600; background-color: #e3f2fd;">
                    <td colspan="2" style="padding-top: 8px;">GAJI BERSIH YANG DITERIMA</td>
                    <td style="text-align: right; padding-top: 8px; color: #1565c0; font-size: 1.1em;">Rp {{ number_format(($salary->total_amount + $salary->additional_amount) - $salary->deductions_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
            <p style="font-size: 13px; color: #666; margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                <strong>📊 Tingkat Kehadiran:</strong> {{ $salary->getAttendancePercentage() }}%
                ({{ $salary->attended_hours }} hadir dari {{ $salary->total_scheduled_hours }} jam terjadwal)
            </p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p>Penerima,</p>
                <div class="line">{{ $salary->teacher->name }}</div>
            </div>
            <div class="signature-box">
                <p>Bendahara,</p>
                <div class="line">___________________</div>
            </div>
        </div>

        <div class="footer">
            <p>Dokumen ini digenerate otomatis pada {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
