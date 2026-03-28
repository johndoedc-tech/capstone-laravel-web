<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Report</title>
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
        .data-table th.text-center {
            text-align: center;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
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
        }
        .user-avatar {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin-right: 10px;
            vertical-align: middle;
            padding: 0;
        }
        .user-name {
            display: inline-block;
            vertical-align: middle;
            font-weight: 600;
            color: #1f2937;
        }
        .prediction-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .prediction-active {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .prediction-inactive {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background-color: #f3f4f6;
            color: #6b7280;
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
        <h1>User Activity Report</h1>
        <p>Track Farmer Engagement and Prediction Usage</p>
    </div>

    <!-- Summary Cards -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card">
                <div class="label">Total Farmers</div>
                <div class="value">{{ number_format($stats['total_farmers']) }}</div>
                <div class="unit">Registered users</div>
            </td>
            <td class="summary-card">
                <div class="label">Active Farmers</div>
                <div class="value">{{ number_format($stats['active_farmers']) }}</div>
                <div class="unit">Made predictions</div>
            </td>
            <td class="summary-card">
                <div class="label">Total Predictions</div>
                <div class="value">{{ number_format($stats['total_predictions']) }}</div>
                <div class="unit">All time</div>
            </td>
            <td class="summary-card">
                <div class="label">Avg per User</div>
                <div class="value">{{ number_format($stats['avg_predictions_per_user'] ?? 0, 1) }}</div>
                <div class="unit">Predictions</div>
            </td>
        </tr>
    </table>

    <!-- Farmer Activity Table -->
    <div class="section-title">Farmer Activity</div>

    @if($users->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">Rank</th>
                    <th>Farmer</th>
                    <th>Email</th>
                    <th class="text-center">Predictions</th>
                    <th>Joined</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $user)
                    <tr>
                        <td class="text-center">
                            <span class="rank-number">{{ $index + 1 }}</span>
                        </td>
                        <td>
                            <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span class="user-name">{{ $user->name }}</span>
                        </td>
                        <td style="color: #6b7280;">{{ $user->email }}</td>
                        <td class="text-center">
                            <span class="prediction-badge {{ $user->predictions_count > 0 ? 'prediction-active' : 'prediction-inactive' }}">
                                {{ number_format($user->predictions_count) }}
                            </span>
                        </td>
                        <td style="color: #6b7280;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-center">
                            @if($user->predictions_count > 0)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($users->total() > $users->count())
            <p style="text-align: center; margin-top: 15px; font-size: 11px; color: #6b7280;">
                Showing {{ $users->count() }} of {{ number_format($users->total()) }} users
            </p>
        @endif
    @else
        <div class="no-data">
            <p><strong>No users found</strong></p>
            <p>Try adjusting your filters</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
