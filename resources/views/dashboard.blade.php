<x-app-layout>
    <link href="{{ asset('assets/css/dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/dataTable.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    <div class="dashboard-page">

        <div class="dashboard-header-row">
            <div>
                <h2 class="dashboard-title">Dashboard</h2>
                <p class="dashboard-welcome">
                    Welcome back, {{ auth()->user()->name ?? 'User' }} 👋
                </p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="dashboard-date-box">
                <i class="bi bi-calendar3"></i>

                <input type="date" name="start_date" value="{{ $dateRangeStart->format('Y-m-d') }}">
                <span>-</span>
                <input type="date" name="end_date" value="{{ $dateRangeEnd->format('Y-m-d') }}">

                <button type="submit">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </form>
        </div>

        <div class="row g-3 dashboard-top-cards">

            <div class="col-xl col-lg-4 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon stat-blue">
                        <i class="bi bi-bank"></i>
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">Total Accounts</div>
                        <div class="dashboard-stat-value">{{ number_format($totalAccounts) }}</div>
                        <div class="dashboard-stat-sub">Active accounts</div>
                        <div class="dashboard-stat-trend text-success">
                            <i class="bi bi-arrow-up-short"></i>
                            {{ number_format($accountsAddedThisMonth) }} this month
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon stat-purple">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">Total Balance</div>
                        <div class="dashboard-stat-value">
                            ₹ {{ number_format($totalBalance) }}
                        </div>
                        <div class="dashboard-stat-sub">Across all accounts</div>
                        <div class="dashboard-stat-trend {{ $balanceChangePercent >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi {{ $balanceChangePercent >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                            {{ abs($balanceChangePercent) }}% vs last month
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon stat-green">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">Income this Month</div>
                        <div class="dashboard-stat-value">
                            ₹ {{ number_format($incomeThisMonth) }}
                        </div>
                        <div class="dashboard-stat-sub">
                            From {{ number_format($incomeSourcesThisMonth) }} source
                        </div>
                        <div class="dashboard-stat-trend {{ $incomeChangePercent >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="bi {{ $incomeChangePercent >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short' }}"></i>
                            {{ abs($incomeChangePercent) }}% vs last month
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon stat-orange">
                        <i class="bi bi-bullseye"></i>
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">Savings Goals</div>
                        <div class="dashboard-stat-value">{{ number_format($savingsGoalsCount) }}</div>
                        <div class="dashboard-stat-sub">Goals in progress</div>
                        <a href="{{ url('/savings_goals') }}" class="dashboard-card-link">
                            View goals <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl col-lg-4 col-md-6">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon stat-red">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">Upcoming Bills</div>
                        <div class="dashboard-stat-value">{{ number_format($upcomingBillsCount) }}</div>
                        <div class="dashboard-stat-sub">Due in next 7 days</div>
                        <a href="{{ url('/bill_reminders') }}" class="dashboard-card-link">
                            View bills <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3 mt-1">

            <div class="col-xl-6">
                <div class="dashboard-box">
                    <div class="dashboard-box-header">
                        <div>
                            <h5>Income vs Expenses</h5>
                        </div>

                        <form method="GET" action="{{ route('dashboard') }}" class="dashboard-range-form">
                            <input type="hidden" name="spending_range" value="{{ $spendingRange }}">

                            @if($spendingRange == 'custom')
                                <input type="hidden" name="spending_start_date" value="{{ $spendingStartDate->format('Y-m-d') }}">
                                <input type="hidden" name="spending_end_date" value="{{ $spendingEndDate->format('Y-m-d') }}">
                            @endif

                            <select name="income_expense_range" class="dashboard-range-select" data-custom-target="#incomeExpenseCustomRange">
                                <option value="today" {{ $incomeExpenseRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ $incomeExpenseRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ $incomeExpenseRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="this_year" {{ $incomeExpenseRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $incomeExpenseRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>

                            <div id="incomeExpenseCustomRange" class="dashboard-custom-range" style="{{ $incomeExpenseRange == 'custom' ? '' : 'display:none;' }}">
                                <input type="date" name="income_expense_start_date" value="{{ $incomeExpenseStartDate->format('Y-m-d') }}">
                                <input type="date" name="income_expense_end_date" value="{{ $incomeExpenseEndDate->format('Y-m-d') }}">
                                <button type="submit">Apply</button>
                            </div>
                        </form>
                    </div>

                    <div class="dashboard-chart-area">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>

                    <div class="dashboard-total-strip">
                        <div>
                            <span>Total Income</span>
                            <strong class="text-success">
                                ₹ {{ number_format($totalIncomeYear) }}
                            </strong>
                        </div>

                        <div>
                            <span>Total Expenses</span>
                            <strong class="text-danger">
                                ₹ {{ number_format($totalExpensesYear) }}
                            </strong>
                        </div>

                        <div>
                            <span>Difference</span>
                            <strong>
                                ₹ {{ number_format($incomeExpenseDifference) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="dashboard-box">
                    <div class="dashboard-box-header">
                        <h5>Top Spending Categories</h5>

                        <form method="GET" action="{{ route('dashboard') }}" class="dashboard-range-form">
                            <input type="hidden" name="income_expense_range" value="{{ $incomeExpenseRange }}">

                            @if($incomeExpenseRange == 'custom')
                                <input type="hidden" name="income_expense_start_date" value="{{ $incomeExpenseStartDate->format('Y-m-d') }}">
                                <input type="hidden" name="income_expense_end_date" value="{{ $incomeExpenseEndDate->format('Y-m-d') }}">
                            @endif

                            <select name="spending_range" class="dashboard-range-select" data-custom-target="#spendingCustomRange">
                                <option value="today" {{ $spendingRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="this_week" {{ $spendingRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="this_month" {{ $spendingRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="this_year" {{ $spendingRange == 'this_year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $spendingRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>

                            <div id="spendingCustomRange" class="dashboard-custom-range" style="{{ $spendingRange == 'custom' ? '' : 'display:none;' }}">
                                <input type="date" name="spending_start_date" value="{{ $spendingStartDate->format('Y-m-d') }}">
                                <input type="date" name="spending_end_date" value="{{ $spendingEndDate->format('Y-m-d') }}">
                                <button type="submit">Apply</button>
                            </div>
                        </form>
                    </div>

                    @if($topSpendingCategories->count() > 0)
                        <div class="dashboard-doughnut-area">
                            <canvas id="spendingCategoryChart"></canvas>
                        </div>

                        <div class="dashboard-category-list">
                            @foreach($topSpendingCategories as $category)
                                <div class="dashboard-category-item">
                                    <div>
                                        <span class="category-dot category-dot-{{ $loop->index + 1 }}"></span>
                                        {{ $category->category_name }}
                                    </div>

                                    <strong>
                                        ₹ {{ number_format($category->total_amount) }}
                                        ({{ $category->percent }}%)
                                    </strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="dashboard-empty-box">
                            No spending data found.
                        </div>
                    @endif

                    <div class="dashboard-total-spent">
                        <span>Total Spent</span>
                        <strong>₹ {{ number_format($totalSpentInRange) }}</strong>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="dashboard-box dashboard-health-box">
                    <div class="dashboard-box-header">
                        <h5>Financial Health</h5>
                    </div>

                    <div class="dashboard-health-meter-wrap">
                        <div class="dashboard-health-meter" style="--health-deg: {{ ($financialHealthScore / 100) * 180 }}deg;"></div>

                        <div class="dashboard-health-heart">
                            <i class="bi bi-heart"></i>
                        </div>
                    </div>

                    <h4>{{ $financialHealthStatus }}</h4>
                    <p>{{ $financialHealthMessage }}</p>

                    <div class="dashboard-health-list">

                        <div class="dashboard-health-item">
                            <span>
                                <i class="bi bi-check-circle"></i>
                                No overspending
                            </span>

                            <i class="bi {{ $hasOverspending ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                        </div>

                        <div class="dashboard-health-item">
                            <span>
                                <i class="bi bi-check-circle"></i>
                                Savings goal on track
                            </span>

                            <i class="bi {{ $savingsOnTrack ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                        </div>

                        <div class="dashboard-health-item">
                            <span>
                                <i class="bi bi-check-circle"></i>
                                All bills paid
                            </span>

                            <i class="bi {{ $allBillsPaid ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                        </div>

                        <div class="dashboard-health-item">
                            <span>
                                <i class="bi bi-check-circle"></i>
                                Low debt usage
                            </span>

                            <i class="bi {{ $lowDebtUsage ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3 mt-1">

            <div class="col-xl-4">
                <div class="dashboard-box dashboard-list-box">
                    <div class="dashboard-box-header">
                        <h5>Recent Transactions</h5>

                        <a href="{{ url('/transactions') }}" class="dashboard-small-link">
                            View all
                        </a>
                    </div>

                    @forelse($recentActivities as $activity)
                        <div class="dashboard-recent-row">
                            <div class="dashboard-recent-left">
                                <div class="dashboard-recent-icon {{ $activity->type }}">
                                    <i class="bi {{ $activity->icon }}"></i>
                                </div>

                                <div>
                                    <strong>{{ $activity->title }}</strong>
                                    <span>{{ $activity->subtitle }}</span>
                                </div>
                            </div>

                            <div class="dashboard-recent-amount {{ $activity->type }}">
                                {{ $activity->type === 'credit' ? '+' : '-' }}
                                ₹{{ number_format($activity->amount) }}

                                <span>
                                    {{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('d M, Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-box">
                            No recent transactions found.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-xl-4">
                <div class="dashboard-box dashboard-list-box">
                    <div class="dashboard-box-header">
                        <h5>Upcoming Bills</h5>

                        <a href="{{ url('/bill_reminders') }}" class="dashboard-small-link">
                            View all
                        </a>
                    </div>

                    @forelse($upcomingBills as $bill)
                        <div class="dashboard-bill-row">
                            <div class="dashboard-recent-left">
                                <div class="dashboard-recent-icon bill">
                                    <i class="bi bi-lightning-charge"></i>
                                </div>

                                <div>
                                    <strong>{{ $bill->title }}</strong>
                                    <span>
                                        Due on {{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('d M, Y') : '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="dashboard-bill-amount">
                                ₹ {{ number_format($bill->amount) }}
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-box">
                            No bills due in next 7 days.
                        </div>
                    @endforelse

                    <a href="{{ url('/bill_reminders') }}" class="dashboard-outline-button">
                        <i class="bi bi-plus"></i>
                        Add New Bill
                    </a>
                </div>
            </div>

            <div class="col-xl-4">
    <div class="dashboard-box dashboard-list-box dashboard-goals-compact-card">
        <div class="dashboard-box-header">
            <div>
                <h5>Savings Goals</h5>
                <span class="dashboard-section-subtitle">Goal progress overview</span>
            </div>

            <a href="{{ url('/savings_goals') }}" class="dashboard-small-link">
                View all
            </a>
        </div>

        <div class="dashboard-goals-compact-list">
            @forelse($savingsGoals as $goal)
                @php
                    $savedAmount = (float) ($goal->saved_amount ?? 0);
                    $targetAmount = (float) ($goal->target_amount ?? 0);
                    $progressPercent = (int) ($goal->progress_percent ?? 0);
                    $remainingAmount = max(0, $targetAmount - $savedAmount);
                @endphp

                <div class="dashboard-goal-compact-item">
                    <div class="dashboard-goal-compact-main">
                        <div class="dashboard-goal-compact-left">
                            <div class="dashboard-goal-compact-icon">
                                <i class="bi bi-bullseye"></i>
                            </div>

                            <div class="dashboard-goal-compact-info">
                                <strong>{{ $goal->goal_name }}</strong>

                                <span>
                                    {{ $goal->goal_category ?? 'Goal' }}

                                    @if(!empty($goal->saving_method) && $goal->saving_method !== '-')
                                        • {{ $goal->saving_method }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="dashboard-goal-compact-right">
                            <strong>{{ $progressPercent }}%</strong>
                            <span>complete</span>
                        </div>
                    </div>

                    <div class="dashboard-goal-compact-amounts">
                        <span>
                            Saved:
                            <strong>₹ {{ number_format($savedAmount) }}</strong>
                        </span>

                        <span>
                            Target:
                            <strong>₹ {{ number_format($targetAmount) }}</strong>
                        </span>
                    </div>

                    <div class="dashboard-goal-compact-progress">
                        <div style="width: {{ $progressPercent }}%;"></div>
                    </div>

                    <div class="dashboard-goal-compact-footer">
                        <span>{{ $goal->savings_for ?? 'Savings Goal' }}</span>
                        <strong>₹ {{ number_format($remainingAmount) }} left</strong>
                    </div>
                </div>
            @empty
                <div class="dashboard-goals-compact-empty">
                    <i class="bi bi-bullseye"></i>
                    <strong>No savings goals yet</strong>
                    <span>Create a goal to start tracking progress.</span>
                </div>
            @endforelse
        </div>

        <a href="{{ url('/savings_add') }}" class="dashboard-goal-compact-create">
            <i class="bi bi-plus-lg"></i>
            Create New Goal
        </a>
    </div>
</div>

        </div>

    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function () {
            var monthlyLabels = @json($monthlyChartLabels);
            var incomeData = @json($monthlyIncomeData);
            var expenseData = @json($monthlyExpenseData);

            var incomeExpenseCanvas = document.getElementById('incomeExpenseChart');

            if (incomeExpenseCanvas) {
                new Chart(incomeExpenseCanvas, {
                    type: 'bar',
                    data: {
                        labels: monthlyLabels,
                        datasets: [
                            {
                                label: 'Income',
                                data: incomeData,
                                backgroundColor: '#35c878',
                                borderRadius: 8,
                                barThickness: 18
                            },
                            {
                                label: 'Expenses',
                                data: expenseData,
                                backgroundColor: '#ff4f6d',
                                borderRadius: 8,
                                barThickness: 18
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 14,
                                    color: '#334155',
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#64748b'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#edf2f7'
                                },
                                ticks: {
                                    color: '#64748b',
                                    callback: function (value) {
                                        return '₹ ' + Number(value).toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            var categoryLabels = @json($topSpendingCategories->pluck('category_name')->values());
            var categoryData = @json($topSpendingCategories->pluck('total_amount')->values());

            var spendingCategoryCanvas = document.getElementById('spendingCategoryChart');

            if (spendingCategoryCanvas && categoryData.length > 0) {
                new Chart(spendingCategoryCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [
                            {
                                data: categoryData,
                                backgroundColor: [
                                    '#35c878',
                                    '#3b82f6',
                                    '#8b5cf6',
                                    '#ff9f43',
                                    '#ff4f6d'
                                ],
                                borderWidth: 0,
                                cutout: '68%'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
            $('.dashboard-range-select').on('change', function () {
                var customTarget = $(this).data('custom-target');

                if ($(this).val() === 'custom') {
                    $(customTarget).slideDown(150);
                } else {
                    $(customTarget).hide();
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
</x-app-layout>