<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .filters {
            background-color: #f3f4f6;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .filters h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #374151;
        }
        .filters p {
            margin: 4px 0;
            font-size: 12px;
            color: #6b7280;
        }
        .filters strong {
            color: #1f2937;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary-card .label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin: 4px 0;
        }
        .summary-card .unit {
            font-size: 10px;
            color: #9ca3af;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table thead {
            background-color: #f3f4f6;
        }
        .data-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        .data-table th.text-right {
            text-align: right;
        }
        .data-table th.text-center {
            text-align: center;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .data-table td.text-right {
            text-align: right;
        }
        .data-table td.text-center {
            text-align: center;
        }
        .data-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .data-table tbody tr td:first-child {
            font-weight: 600;
            color: #1f2937;
        }
        .data-table tbody tr td:nth-child(3) {
            font-weight: 600;
            color: #1f2937;
        }
        .data-table tfoot {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .data-table tfoot td {
            padding: 10px 8px;
            border-top: 2px solid #9ca3af;
            border-bottom: 2px solid #9ca3af;
            font-size: 11px;
            color: #1f2937;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }
        .no-data p {
            font-size: 14px;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Production Summary Report</h1>
        <p>Crop Production Analysis by Municipality and Type</p>
    </div>

    <!-- Applied Filters -->
    @if($request->anyFilled(['year', 'municipality', 'crop']))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if($request->filled('year'))
            <p><strong>Year:</strong> {{ $request->get('year') }}</p>
        @endif
        @if($request->filled('municipality'))
            <p><strong>Municipality:</strong> {{ $request->get('municipality') }}</p>
        @endif
        @if($request->filled('crop'))
            <p><strong>Crop:</strong> {{ $request->get('crop') }}</p>
        @endif
    </div>
    @endif

    <!-- Summary Cards -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card">
                <div class="label">Total Production</div>
                <div class="value">{{ number_format($totals['production'], 2) }}</div>
                <div class="unit">Metric Tons</div>
            </td>
            <td class="summary-card">
                <div class="label">Total Area</div>
                <div class="value">{{ number_format($totals['area'], 2) }}</div>
                <div class="unit">Hectares</div>
            </td>
            <td class="summary-card">
                <div class="label">Avg Productivity</div>
                <div class="value">{{ number_format($totals['avg_productivity'], 2) }}</div>
                <div class="unit">MT/Ha</div>
            </td>
            <td class="summary-card">
                <div class="label">Total Records</div>
                <div class="value">{{ number_format($totals['records']) }}</div>
                <div class="unit">Data Points</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    @if($data->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Municipality</th>
                    <th>Crop</th>
                    <th class="text-right">Production (MT)</th>
                    <th class="text-right">Area (Ha)</th>
                    <th class="text-right">Productivity (MT/Ha)</th>
                    <th class="text-center">Records</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row->municipality }}</td>
                        <td>{{ $row->crop }}</td>
                        <td class="text-right">{{ number_format($row->total_production, 2) }}</td>
                        <td class="text-right">{{ number_format($row->total_area, 2) }}</td>
                        <td class="text-right">{{ number_format($row->avg_productivity, 2) }}</td>
                        <td class="text-center">{{ number_format($row->record_count) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td class="text-right">{{ number_format($totals['production'], 2) }}</td>
                    <td class="text-right">{{ number_format($totals['area'], 2) }}</td>
                    <td class="text-right">{{ number_format($totals['avg_productivity'], 2) }}</td>
                    <td class="text-center">{{ number_format($totals['records']) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <p><strong>No data found</strong></p>
            <p>Try adjusting your filters</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
