<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparative Analysis Report</title>
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
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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
        .rank-number {
            font-weight: bold;
            color: #1f2937;
            font-size: 12px;
            margin-right: 8px;
        }
        .data-table tbody tr td:first-child {
            font-weight: 600;
            color: #1f2937;
        }
        .data-table tbody tr td:nth-child(2) {
            font-weight: 600;
            color: #1f2937;
        }
        .progress-bar-container {
            display: inline-block;
            width: 60px;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .progress-bar {
            height: 8px;
            background-color: #3b82f6;
            border-radius: 4px;
        }
        .percentage {
            display: inline-block;
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
        <h1>Comparative Analysis Report</h1>
        <p>Compare Metrics Across Municipalities, Crops, or Years</p>
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

    <!-- Comparison Title -->
    <div class="section-title">Comparison by {{ ucfirst($compareBy) }}</div>

    <!-- Data Table -->
    @if($data->count() > 0)
        @php
            $totalProduction = $data->sum('total_production');
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>{{ ucfirst($compareBy) }}</th>
                    <th class="text-right">Production (MT)</th>
                    <th class="text-right">Area (Ha)</th>
                    <th class="text-right">Productivity (MT/Ha)</th>
                    <th class="text-center">Records</th>
                    <th class="text-right">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td class="text-center">
                            <span class="rank-number">{{ $index + 1 }}</span>
                        </td>
                        <td>{{ $row->$compareBy }}</td>
                        <td class="text-right">{{ number_format($row->total_production, 2) }}</td>
                        <td class="text-right">{{ number_format($row->total_area, 2) }}</td>
                        <td class="text-right">{{ number_format($row->avg_productivity, 2) }}</td>
                        <td class="text-center">{{ number_format($row->record_count) }}</td>
                        <td class="text-right">
                            @php
                                $percentage = $totalProduction > 0 ? ($row->total_production / $totalProduction * 100) : 0;
                            @endphp
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="percentage">{{ number_format($percentage, 1) }}%</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td class="text-right">{{ number_format($data->sum('total_production'), 2) }}</td>
                    <td class="text-right">{{ number_format($data->sum('total_area'), 2) }}</td>
                    <td class="text-right">{{ number_format($data->avg('avg_productivity'), 2) }}</td>
                    <td class="text-center">{{ number_format($data->sum('record_count')) }}</td>
                    <td class="text-right">100%</td>
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
