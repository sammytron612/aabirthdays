<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportContent['subject'] ?? 'Sobriety Anniversary Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $reportContent['title'] ?? 'Sobriety Anniversary Report' }}</h2>
    </div>

    <div class="content">
        <p>Hello,</p>

        <p>{{ $reportContent['message'] ?? 'Please find the attached sobriety anniversary report in CSV format.' }}</p>

        @if(isset($reportContent['period']))
            <p><strong>Report Period:</strong> {{ $reportContent['period'] }}</p>
        @endif

        @if(isset($reportContent['totalMembers']))
            <p><strong>Total Members:</strong> {{ $reportContent['totalMembers'] }}</p>
        @endif

        <p>The report has been attached as a CSV file for easy viewing and analysis.</p>
    </div>

    <div class="footer">
        @if(isset($reportContent['secretaryName']))
            <p>Best regards,<br>{{ $reportContent['secretaryName'] }}</p>
        @else
            <p>Best regards,<br>Birthday Secretary</p>
        @endif
    </div>
</body>
</html>
