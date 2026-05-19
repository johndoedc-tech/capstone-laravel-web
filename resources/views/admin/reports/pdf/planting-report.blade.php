<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Planting Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        h2 { margin: 18px 0 8px; font-size: 14px; }
        .muted { color: #6b7280; }
        .summary { display: table; width: 100%; margin-top: 16px; }
        .summary-row { display: table-row; }
        .summary-card { display: table-cell; width: 25%; padding: 10px; border: 1px solid #d1d5db; }
        .label { font-size: 9px; text-transform: uppercase; color: #6b7280; }
        .value { margin-top: 4px; font-size: 16px; font-weight: bold; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f3f4f6; color: #4b5563; font-size: 9px; text-transform: uppercase; text-align: left; padding: 7px; border: 1px solid #e5e7eb; }
        td { vertical-align: top; padding: 7px; border: 1px solid #e5e7eb; }
        .status { font-weight: bold; text-transform: uppercase; }
        .damaged { color: #c2410c; }
        .planted { color: #1d4ed8; }
        .harvested { color: #047857; }
    </style>
</head>
<body>
    <h1>Planting Report</h1>
    <p class="muted">Generated {{ now()->format('M d, Y h:i A') }}</p>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-card">
                <div class="label">Records</div>
                <div class="value">{{ number_format($summary['total_records']) }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Area</div>
                <div class="value">{{ number_format($summary['total_area_ha'], 2) }} ha</div>
            </div>
            <div class="summary-card">
                <div class="label">Adjusted Production</div>
                <div class="value">{{ number_format($summary['adjusted_production_mt'], 2) }} mt</div>
            </div>
            <div class="summary-card">
                <div class="label">Loss</div>
                <div class="value">{{ number_format($summary['loss_production_mt'], 2) }} mt</div>
            </div>
        </div>
    </div>

    <h2>Planting Records</h2>
    <table>
        <thead>
            <tr>
                <th>Farmer</th>
                <th>Crop</th>
                <th>Planting</th>
                <th>Harvest</th>
                <th>Area & Yield</th>
                <th>Farm Type</th>
                <th>Status</th>
                <th>Recorded</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>
                        <strong>{{ $record['farmer_name'] }}</strong><br>
                        {{ $record['farmer_id'] }}<br>
                        {{ $record['municipality'] }}<br>
                        {{ $record['cooperative'] ?: '-' }}
                    </td>
                    <td>
                        <strong>{{ $record['crop'] }}</strong><br>
                        Notes: {{ $record['notes'] }}
                    </td>
                    <td>{{ $record['planting_date']?->format('M d, Y') ?? '-' }}</td>
                    <td>{{ $record['harvest_date']?->format('M d, Y') ?? '-' }}</td>
                    <td>
                        <strong>{{ number_format($record['area_ha'], 2) }} ha</strong><br>
                        Original: {{ number_format($record['original_production_mt'], 2) }} mt<br>
                        Adjusted: {{ number_format($record['adjusted_production_mt'], 2) }} mt
                        @if($record['damage_sqm'] > 0)
                            <br><span class="damaged">Damage: {{ number_format($record['damage_ha'], 2) }} ha, {{ number_format($record['loss_production_mt'], 2) }} mt lost</span>
                        @endif
                    </td>
                    <td>{{ $record['farm_type'] }}<br>{{ $record['seed_type'] }}</td>
                    <td>
                        <span class="status {{ $record['status'] }}">{{ $record['status_label'] }}</span>
                        @if($record['status'] === 'damaged')
                            <br>{{ $record['damage_title'] ?: 'Damage report' }}
                            <br>Date damaged: {{ $record['damage_date']?->format('M d, Y') ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $record['recorded_at']?->format('M d, Y h:i A') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No planting records match the current filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
