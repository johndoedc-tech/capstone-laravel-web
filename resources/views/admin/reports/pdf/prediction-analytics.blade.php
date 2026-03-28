<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediction Analytics Report</title>
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
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }
        .top-stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .top-stat-box {
            display: table-cell;
            width: 33.33%;
            padding: 12px;
            vertical-align: top;
        }
        .top-stat-box h3 {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-label {
            color: #6b7280;
        }
        .stat-value {
            font-weight: bold;
            color: #1f2937;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table thead {
            background-color: #f3f4f6;
        }
        .data-table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
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
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .data-table td.text-right {
            text-align: right;
        }
        .data-table td.text-center {
            text-align: center;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
        }
        .status-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-failed {
            background-color: #fee2e2;
            color: #991b1b;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Prediction Analytics Report</h1>
        <p>Analysis of Prediction Performance and User Activity</p>
    </div>

    <!-- Summary Cards -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card">
                <div class="label">Total Predictions</div>
                <div class="value">{{ number_format($analytics['total']) }}</div>
                <div class="unit">{{ $analytics['successful'] }} successful</div>
            </td>
            <td class="summary-card">
                <div class="label">Success Rate</div>
                <div class="value">{{ number_format($analytics['success_rate'], 1) }}%</div>
                <div class="unit">{{ $analytics['failed'] }} failed</div>
            </td>
            <td class="summary-card">
                <div class="label">Avg Confidence</div>
                <div class="value">{{ number_format(($analytics['avg_confidence'] ?? 0) * 100, 1) }}%</div>
                <div class="unit">Model accuracy</div>
            </td>
            <td class="summary-card">
                <div class="label">Total Predicted</div>
                <div class="value">{{ number_format($analytics['total_predicted_production'], 0) }}</div>
                <div class="unit">Metric Tons</div>
            </td>
        </tr>
    </table>

    <!-- Top Statistics -->
    <div class="section">
        <table class="top-stats">
            <tr>
                <td class="top-stat-box">
                    <h3>Top Predicted Crops</h3>
                    @foreach($topCrops->take(5) as $crop)
                        <div class="stat-item">
                            <span class="stat-label">{{ $crop->crop }}</span>
                            <span class="stat-value">{{ number_format($crop->count) }}</span>
                        </div>
                    @endforeach
                </td>
                <td class="top-stat-box">
                    <h3>Top Municipalities</h3>
                    @foreach($topMunicipalities->take(5) as $municipality)
                        <div class="stat-item">
                            <span class="stat-label">{{ $municipality->municipality }}</span>
                            <span class="stat-value">{{ number_format($municipality->count) }}</span>
                        </div>
                    @endforeach
                </td>
                <td class="top-stat-box">
                    <h3>Performance Metrics</h3>
                    <div class="stat-item">
                        <span class="stat-label">Avg Response Time</span>
                        <span class="stat-value">{{ number_format($analytics['avg_response_time'] ?? 0, 0) }}ms</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Production</span>
                        <span class="stat-value">{{ number_format($analytics['total_predicted_production'], 0) }} MT</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Success Rate</span>
                        <span class="stat-value">{{ number_format($analytics['success_rate'], 1) }}%</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Recent Predictions Table -->
    <div class="section">
        <div class="section-title">Recent Predictions</div>
        @if($predictions->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Municipality</th>
                        <th>Crop</th>
                        <th class="text-right">Predicted (MT)</th>
                        <th class="text-center">Confidence</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($predictions->take(30) as $prediction)
                        <tr>
                            <td>{{ $prediction->created_at->format('M d, Y') }}</td>
                            <td>{{ $prediction->user->name ?? 'N/A' }}</td>
                            <td>{{ $prediction->municipality }}</td>
                            <td style="font-weight: 600;">{{ $prediction->crop }}</td>
                            <td class="text-right">{{ number_format($prediction->predicted_production_mt, 2) }}</td>
                            <td class="text-center">{{ number_format(($prediction->confidence_score ?? 0) * 100, 1) }}%</td>
                            <td class="text-center">
                                @if($prediction->status === 'success')
                                    <span class="status-badge status-success">Success</span>
                                @else
                                    <span class="status-badge status-failed">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($predictions->count() > 30)
                <p style="text-align: center; margin-top: 15px; font-size: 11px; color: #6b7280;">
                    Showing first 30 of {{ number_format($predictions->total()) }} predictions
                </p>
            @endif
        @else
            <div class="no-data">
                <p><strong>No predictions found</strong></p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
