<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('clinic::lang.dashboard_card')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .dashboard {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 200px;
            text-align: left;
            position: relative;
        }

        .card-title {
            font-size: 14px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .card-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: white;
        }

        .card-icon.users { background-color: #00b7ff; }
        .card-icon.subscription { background-color: #8b5cf6; }
        .card-icon.free-users { background-color: #3b82f6; }
        .card-icon.income { background-color: #10b981; }
        .card-icon.expense { background-color: #ef4444; }

        .card-change {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-change.positive {
            color: #10b981;
        }

        .card-change.negative {
            color: #ef4444;
        }

        .card-change .arrow {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Total Users Card -->
        <div class="card">
            <div class="card-title">@lang('clinic::lang.total_users')</div>
            <div class="card-value">20,000</div>
            <div class="card-change positive">
                <span class="arrow">▲</span> +5000 Last 30 days users
            </div>
            <div class="card-icon users">👥</div>
        </div>

        <!-- Total Subscription Card -->
        <div class="card">
            <div class="card-title">@lang('clinic::lang.total_subscription')</div>
            <div class="card-value">15,000</div>
            <div class="card-change negative">
                <span class="arrow">▼</span> -800 Last 30 days subscription
            </div>
            <div class="card-icon subscription">🎖</div>
        </div>

        <!-- Total Free Users Card -->
        <div class="card">
            <div class="card-title">@lang('clinic::lang.total_free_users')</div>
            <div class="card-value">5,000</div>
            <div class="card-change positive">
                <span class="arrow">▲</span> +200 Last 30 days users
            </div>
            <div class="card-icon free-users">👥</div>
        </div>

        <!-- Total Income Card -->
        <div class="card">
            <div class="card-title">@lang('clinic::lang.total_income')</div>
            <div class="card-value">$42,000</div>
            <div class="card-change positive">
                <span class="arrow">▲</span> +$20,000 Last 30 days income
            </div>
            <div class="card-icon income">💵</div>
        </div>

        <!-- Total Expense Card -->
        <div class="card">
            <div class="card-title">@lang('clinic::lang.total_expense')</div>
            <div class="card-value">$30,000</div>
            <div class="card-change negative">
                <span class="arrow">▲</span> +$5,000 Last 30 days expense
            </div>
            <div class="card-icon expense">📉</div>
        </div>
    </div>
</body>
</html>