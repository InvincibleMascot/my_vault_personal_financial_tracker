<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller{


    public function index(Request $request)
    {
        $dateRangeStart = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dateRangeEnd = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        if ($dateRangeStart->gt($dateRangeEnd)) {
            $tempDate = $dateRangeStart;
            $dateRangeStart = $dateRangeEnd;
            $dateRangeEnd = $tempDate;
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        $incomeExpenseRange = $request->income_expense_range ?? 'this_year';

        $incomeExpenseRangeData = $this->getDashboardDateRange(
            $incomeExpenseRange,
            $request->income_expense_start_date,
            $request->income_expense_end_date,
            'this_year'
        );

        $incomeExpenseStartDate = $incomeExpenseRangeData['start'];
        $incomeExpenseEndDate = $incomeExpenseRangeData['end'];
        $incomeExpenseRangeLabel = $incomeExpenseRangeData['label'];

        $spendingRange = $request->spending_range ?? 'this_month';

        $spendingRangeData = $this->getDashboardDateRange(
            $spendingRange,
            $request->spending_start_date,
            $request->spending_end_date,
            'this_month'
        );

        $spendingStartDate = $spendingRangeData['start'];
        $spendingEndDate = $spendingRangeData['end'];
        $spendingRangeLabel = $spendingRangeData['label'];

        $dateRangeStart = $spendingStartDate;
        $dateRangeEnd = $spendingEndDate;

        $totalAccounts = Schema::hasTable('accounts')
            ? (int) $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->count()
            : 0;

        $accountsAddedThisMonth = 0;

        if (Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'created_at')) {
            $accountsAddedThisMonth = (int) $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
        }

        $totalBankAccountBalance = Schema::hasTable('accounts') && Schema::hasColumn('accounts', 'balance')
            ? (int) $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->sum('balance')
            : 0;

        $latestCashBalance = $this->getLatestCashBalance();

        $totalBalance = $totalBankAccountBalance + $latestCashBalance;

        $netTransactionThisMonth = $this->getNetTransactionAmount($monthStart, $monthEnd);
        $previousBalance = $totalBalance - $netTransactionThisMonth;
        $balanceChangePercent = $this->percentageChange($totalBalance, $previousBalance);

        $incomeThisMonth = $this->getIncomeTotal($monthStart, $monthEnd);
        $incomeLastMonth = $this->getIncomeTotal($lastMonthStart, $lastMonthEnd);
        $incomeChangePercent = $this->percentageChange($incomeThisMonth, $incomeLastMonth);

        $incomeSourcesThisMonth = 0;

        if (
            Schema::hasTable('income') &&
            Schema::hasColumn('income', 'income_from') &&
            Schema::hasColumn('income', 'created_at')
        ) {
            $incomeSourcesThisMonth = (int) $this->scopeCreatedByCurrentUser(DB::table('income'), 'income')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->distinct()
                ->count('income_from');
        }

        $monthlyChart = $this->getIncomeExpenseChartByRange($incomeExpenseStartDate, $incomeExpenseEndDate);

        $monthlyChartLabels = $monthlyChart['labels'];
        $monthlyIncomeData = $monthlyChart['income'];
        $monthlyExpenseData = $monthlyChart['expenses'];

        $totalIncomeYear = array_sum($monthlyIncomeData);
        $totalExpensesYear = array_sum($monthlyExpenseData);
        $incomeExpenseDifference = $totalIncomeYear - $totalExpensesYear;

        $topSpendingCategories = $this->getTopSpendingCategories($spendingStartDate, $spendingEndDate);
        $totalSpentInRange = (int) $topSpendingCategories->sum('total_amount');

        $recentActivities = $this->getRecentActivities();

        $upcomingBills = $this->getUpcomingBills();
        $upcomingBillsCount = $upcomingBills->count();

        $savingsGoals = $this->getSavingsGoals();
        $savingsGoalsCount = $this->getSavingsGoalsCount();

        $hasOverspending = $incomeThisMonth > 0
            ? $totalExpensesYear <= $totalIncomeYear
            : $totalExpensesYear <= 0;

        $savingsOnTrack = $savingsGoals->count() > 0
            ? $savingsGoals->avg('progress_percent') >= 30
            : true;

        $allBillsPaid = $upcomingBillsCount === 0;
        $lowDebtUsage = true;

        $financialHealthScore = 0;
        $financialHealthScore += $hasOverspending ? 25 : 0;
        $financialHealthScore += $savingsOnTrack ? 25 : 0;
        $financialHealthScore += $allBillsPaid ? 25 : 0;
        $financialHealthScore += $lowDebtUsage ? 25 : 0;

        if ($financialHealthScore >= 75) {
            $financialHealthStatus = 'Good';
            $financialHealthMessage = "Keep it up! You're on the right track.";
        } elseif ($financialHealthScore >= 50) {
            $financialHealthStatus = 'Fair';
            $financialHealthMessage = 'A few items need your attention.';
        } else {
            $financialHealthStatus = 'Needs Attention';
            $financialHealthMessage = 'Review expenses, bills, and savings goals.';
        }

        return view('dashboard', compact(
            'dateRangeStart',
            'dateRangeEnd',
            'totalAccounts',
            'accountsAddedThisMonth',
            'totalBalance',
            'balanceChangePercent',
            'incomeThisMonth',
            'incomeChangePercent',
            'incomeSourcesThisMonth',
            'savingsGoalsCount',
            'upcomingBillsCount',
            'monthlyChartLabels',
            'monthlyIncomeData',
            'monthlyExpenseData',
            'totalIncomeYear',
            'totalExpensesYear',
            'incomeExpenseDifference',
            'topSpendingCategories',
            'totalSpentInRange',
            'recentActivities',
            'upcomingBills',
            'savingsGoals',
            'financialHealthScore',
            'financialHealthStatus',
            'financialHealthMessage',
            'hasOverspending',
            'savingsOnTrack',
            'allBillsPaid',
            'lowDebtUsage',
            'incomeExpenseRange',
            'incomeExpenseStartDate',
            'incomeExpenseEndDate',
            'incomeExpenseRangeLabel',
            'spendingRange',
            'spendingStartDate',
            'spendingEndDate',
            'spendingRangeLabel'
        ));
    }

    public function getTransactionMethodIds($methodName)
    {
        if (!Schema::hasTable('transaction_method')) {
            return collect();
        }

        return DB::table('transaction_method')
            ->whereRaw('LOWER(transaction_method) = ?', [strtolower($methodName)])
            ->pluck('id');
    }

    public function getLatestCashBalance()
    {
        if (!Schema::hasTable('transactions') || !Schema::hasTable('transaction_type')) {
            return 0;
        }

        $cashTransactionTypeIds = DB::table('transaction_type')
            ->whereRaw('LOWER(transaction_type) = ?', ['cash'])
            ->pluck('id');

        if ($cashTransactionTypeIds->isEmpty()) {
            return 0;
        }

        return (int) (
            $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->whereIn('transaction_type_id', $cashTransactionTypeIds)
                ->whereNotNull('cash_balance')
                ->orderBy('id', 'desc')
                ->value('cash_balance') ?? 0
        );
    }

    public function getNetTransactionAmount($startDate, $endDate)
    {
        if (!Schema::hasTable('transactions')) {
            return 0;
        }

        $creditIds = $this->getTransactionMethodIds('credit');
        $debitIds = $this->getTransactionMethodIds('debit');

        $creditTotal = 0;
        $debitTotal = 0;

        if ($creditIds->isNotEmpty()) {
            $creditQuery = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->whereIn('transaction_method_id', $creditIds);

            if (Schema::hasColumn('transactions', 'created_at')) {
                $creditQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $creditTotal = (int) $creditQuery->sum('amount_paid');
        }

        if ($debitIds->isNotEmpty()) {
            $debitQuery = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->whereIn('transaction_method_id', $debitIds);

            if (Schema::hasColumn('transactions', 'created_at')) {
                $debitQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $debitTotal = (int) $debitQuery->sum('amount_paid');
        }

        return $creditTotal - $debitTotal;
    }

    public function getIncomeTotal($startDate, $endDate)
    {
        if (!Schema::hasTable('income') || !Schema::hasColumn('income', 'income_amount')) {
            return 0;
        }

        $query = $this->scopeCreatedByCurrentUser(DB::table('income'), 'income');

        if (Schema::hasColumn('income', 'created_at')) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (int) $query->sum('income_amount');
    }

    public function getExpenseTotal($startDate, $endDate)
    {
        if (!Schema::hasTable('transactions')) {
            return 0;
        }

        $debitIds = $this->getTransactionMethodIds('debit');

        if ($debitIds->isEmpty()) {
            return 0;
        }

        $query = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
            ->whereIn('transaction_method_id', $debitIds);

        if (Schema::hasColumn('transactions', 'created_at')) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (int) $query->sum('amount_paid');
    }

    public function getMonthlyIncomeExpenseChart($yearStart, $yearEnd)
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        $currentMonth = (int) Carbon::now()->format('n');

        $incomeMonthlyRows = collect();
        $expenseMonthlyRows = collect();

        if (
            Schema::hasTable('income') &&
            Schema::hasColumn('income', 'income_amount') &&
            Schema::hasColumn('income', 'created_at')
        ) {
            $incomeMonthlyRows = $this->scopeCreatedByCurrentUser(DB::table('income'), 'income')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM created_at) as month_number'),
                    DB::raw('SUM(income_amount) as total_amount')
                )
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->pluck('total_amount', 'month_number');
        }

        $debitIds = $this->getTransactionMethodIds('debit');

        if (
            Schema::hasTable('transactions') &&
            Schema::hasColumn('transactions', 'amount_paid') &&
            Schema::hasColumn('transactions', 'created_at') &&
            $debitIds->isNotEmpty()
        ) {
            $expenseMonthlyRows = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM created_at) as month_number'),
                    DB::raw('SUM(amount_paid) as total_amount')
                )
                ->whereIn('transaction_method_id', $debitIds)
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
                ->pluck('total_amount', 'month_number');
        }

        for ($month = 1; $month <= $currentMonth; $month++) {
            $labels[] = Carbon::create(Carbon::now()->year, $month, 1)->format('M');

            $incomeData[] = (int) ($incomeMonthlyRows[$month] ?? $incomeMonthlyRows[(string) $month] ?? 0);
            $expenseData[] = (int) ($expenseMonthlyRows[$month] ?? $expenseMonthlyRows[(string) $month] ?? 0);
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    public function getTopSpendingCategories($startDate, $endDate)
    {
        if (!Schema::hasTable('transactions') || !Schema::hasTable('transaction_category')) {
            return collect();
        }

        $debitIds = $this->getTransactionMethodIds('debit');

        if ($debitIds->isEmpty()) {
            return collect();
        }

        $query = $this->scopeCreatedByCurrentUser(DB::table('transactions as t'), 'transactions', 't.created_by')
            ->leftJoin('transaction_category as tc', 't.transaction_category_id', '=', 'tc.id')
            ->whereIn('t.transaction_method_id', $debitIds)
            ->select(
                DB::raw("COALESCE(tc.transaction_category, 'Others') as category_name"),
                DB::raw('SUM(t.amount_paid) as total_amount')
            )
            ->groupBy('tc.transaction_category')
            ->orderByDesc('total_amount')
            ->limit(5);

        if (Schema::hasColumn('transactions', 'created_at')) {
            $query->whereBetween('t.created_at', [$startDate, $endDate]);
        }

        $rows = $query->get();
        $total = (int) $rows->sum('total_amount');

        return $rows->map(function ($row) use ($total) {
            $row->total_amount = (int) $row->total_amount;
            $row->percent = $total > 0 ? round(($row->total_amount / $total) * 100) : 0;

            return $row;
        });
    }

    public function getRecentActivities()
    {
        $activities = collect();

        if (Schema::hasTable('transactions')) {
            $transactionRows = $this->scopeCreatedByCurrentUser(DB::table('transactions as t'), 'transactions', 't.created_by')
                ->leftJoin('transaction_method as tm', 't.transaction_method_id', '=', 'tm.id')
                ->leftJoin('transaction_type as tt', 't.transaction_type_id', '=', 'tt.id')
                ->select(
                    't.id',
                    't.transaction_name as title',
                    DB::raw("COALESCE(tt.transaction_type, '-') as subtitle"),
                    't.amount_paid as amount',
                    DB::raw("COALESCE(tm.transaction_method, '-') as method_name"),
                    't.created_at'
                )
                ->orderBy('t.id', 'desc')
                ->limit(5)
                ->get();

            foreach ($transactionRows as $row) {
                $method = strtolower(trim((string) $row->method_name));

                $activities->push((object) [
                    'title' => $row->title,
                    'subtitle' => $row->subtitle,
                    'amount' => (int) $row->amount,
                    'type' => $method === 'credit' ? 'credit' : 'debit',
                    'created_at' => $row->created_at,
                    'icon' => $method === 'credit' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle',
                ]);
            }
        }

        if (Schema::hasTable('income')) {
            $incomeRows = $this->scopeCreatedByCurrentUser(DB::table('income'), 'income')
                ->select(
                    'id',
                    'income_name as title',
                    DB::raw("'Income' as subtitle"),
                    'income_amount as amount',
                    'created_at'
                )
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();

            foreach ($incomeRows as $row) {
                $activities->push((object) [
                    'title' => $row->title,
                    'subtitle' => $row->subtitle,
                    'amount' => (int) $row->amount,
                    'type' => 'credit',
                    'created_at' => $row->created_at,
                    'icon' => 'bi-wallet2',
                ]);
            }
        }

        return $activities
            ->sortByDesc('created_at')
            ->take(3)
            ->values();
    }

    public function getUpcomingBills()
    {
        $table = $this->firstExistingTable([
            'bill_reminders',
            'bills',
            'recurring_expenses',
        ]);

        if (!$table) {
            return collect();
        }

        $columns = Schema::getColumnListing($table);

        $nameColumn = collect(['bill_name', 'expense_name', 'title', 'name'])
            ->first(function ($column) use ($columns) {
                return in_array($column, $columns);
            });

        $amountColumn = collect(['bill_amount', 'amount', 'expense_amount'])
            ->first(function ($column) use ($columns) {
                return in_array($column, $columns);
            });

        $dueDateColumn = collect(['due_date', 'next_due_date', 'bill_due_date'])
            ->first(function ($column) use ($columns) {
                return in_array($column, $columns);
            });

        $statusColumn = collect(['status', 'payment_status'])
            ->first(function ($column) use ($columns) {
                return in_array($column, $columns);
            });

        $query = $this->scopeCreatedByCurrentUser(DB::table($table), $table);

        if ($dueDateColumn) {
            $query->whereBetween($dueDateColumn, [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays(7)->endOfDay()
            ])->orderBy($dueDateColumn, 'asc');
        }

        if ($statusColumn) {
            $query->whereRaw("LOWER(CAST({$statusColumn} AS TEXT)) NOT IN (?, ?)", [
                'paid',
                'completed'
            ]);
        }

        $rows = $query
            ->limit(3)
            ->get([
                DB::raw($nameColumn ? "COALESCE({$nameColumn}, 'Bill') as title" : "'Bill' as title"),
                DB::raw($amountColumn ? "COALESCE({$amountColumn}, 0) as amount" : "0 as amount"),
                DB::raw($dueDateColumn ? "{$dueDateColumn} as due_date" : "NOW() as due_date"),
            ]);

        return $rows->map(function ($row) {
            $row->amount = (int) $row->amount;

            return $row;
        });
    }

    public function getSavingsGoals()
{
    if (!Schema::hasTable('savings_goals')) {
        return collect();
    }

    $accountSavingExpression = Schema::hasColumn('savings_goals', 'total_account_savings')
        ? "CASE WHEN sg.total_account_savings ~ '^[0-9]+(\\.[0-9]+)?$' THEN sg.total_account_savings::numeric ELSE 0 END"
        : "0";

    $cashSavingExpression = Schema::hasColumn('savings_goals', 'totol_cash_saving')
        ? "CASE WHEN sg.totol_cash_saving ~ '^[0-9]+(\\.[0-9]+)?$' THEN sg.totol_cash_saving::numeric ELSE 0 END"
        : "0";

    $savedAmountExpression = "({$accountSavingExpression} + {$cashSavingExpression})";

    $query = $this->scopeCreatedByCurrentUser(DB::table('savings_goals as sg'), 'savings_goals', 'sg.created_by')
        ->select(
            'sg.id',
            DB::raw("COALESCE(sg.saving_name, 'Goal') as goal_name"),
            DB::raw("COALESCE(sg.savings_category, '-') as goal_category"),
            DB::raw("COALESCE(sg.savings_for, '-') as savings_for"),
            DB::raw("COALESCE(sg.savings_amount, 0) as target_amount"),
            DB::raw("{$savedAmountExpression} as saved_amount")
        )
        ->orderBy('sg.id', 'desc')
        ->limit(3);

    if (Schema::hasTable('transaction_type')) {
        $query->leftJoin('transaction_type as tt', 'sg.savings_method', '=', 'tt.id')
            ->addSelect(DB::raw("COALESCE(tt.transaction_type, '-') as saving_method"));
    } else {
        $query->addSelect(DB::raw("'-' as saving_method"));
    }

    $rows = $query->get();

    return $rows->map(function ($row) {
        $row->saved_amount = (int) $row->saved_amount;
        $row->target_amount = (int) $row->target_amount;

        $row->progress_percent = $row->target_amount > 0
            ? min(100, round(($row->saved_amount / $row->target_amount) * 100))
            : 0;

        $row->remaining_amount = max(0, $row->target_amount - $row->saved_amount);

        return $row;
    });
}
    public function getSavingsGoalsCount()
    {
        $table = $this->firstExistingTable([
            'savings_goals',
            'saving_goals',
            'goals',
        ]);

        return $table ? (int) $this->scopeCreatedByCurrentUser(DB::table($table), $table)->count() : 0;
    }

    public function firstExistingTable($tables)
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    public function percentageChange($current, $previous)
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    public function getDashboardDateRange($rangeType, $customStartDate, $customEndDate, $defaultRange)
{
    if (empty($rangeType)) {
        $rangeType = $defaultRange;
    }

    if ($rangeType == 'today') {
        return [
            'start' => Carbon::now()->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
            'label' => 'Today',
        ];
    }

    if ($rangeType == 'this_week') {
        return [
            'start' => Carbon::now()->startOfWeek()->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
            'label' => 'This Week',
        ];
    }

    if ($rangeType == 'this_month') {
        return [
            'start' => Carbon::now()->startOfMonth()->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
            'label' => 'This Month',
        ];
    }

    if ($rangeType == 'this_year') {
        return [
            'start' => Carbon::now()->startOfYear()->startOfDay(),
            'end' => Carbon::now()->endOfDay(),
            'label' => 'This Year',
        ];
    }

    if ($rangeType == 'custom' && !empty($customStartDate) && !empty($customEndDate)) {
        $startDate = Carbon::parse($customStartDate)->startOfDay();
        $endDate = Carbon::parse($customEndDate)->endOfDay();

        if ($startDate->gt($endDate)) {
            $temporaryDate = $startDate;
            $startDate = $endDate;
            $endDate = $temporaryDate;
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'label' => 'Custom Range',
        ];
    }

    return [
        'start' => Carbon::now()->startOfMonth()->startOfDay(),
        'end' => Carbon::now()->endOfDay(),
        'label' => 'This Month',
    ];
}

public function getIncomeExpenseChartByRange($startDate, $endDate)
{
    $labels = [];
    $incomeData = [];
    $expenseData = [];

    $differenceInDays = (int) $startDate->diffInDays($endDate);

    if ($differenceInDays <= 31) {
        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            $labels[] = $currentDate->format('d M');
            $incomeData[] = $this->getIncomeTotal($dayStart, $dayEnd);
            $expenseData[] = $this->getExpenseTotal($dayStart, $dayEnd);

            $currentDate->addDay();
        }
    } else {
        $currentMonth = $startDate->copy()->startOfMonth();

        while ($currentMonth->lte($endDate)) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();

            if ($monthStart->lt($startDate)) {
                $monthStart = $startDate->copy();
            }

            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate->copy();
            }

            $labels[] = $currentMonth->format('M Y');
            $incomeData[] = $this->getIncomeTotal($monthStart, $monthEnd);
            $expenseData[] = $this->getExpenseTotal($monthStart, $monthEnd);

            $currentMonth->addMonth();
        }
    }

    return [
        'labels' => $labels,
        'income' => $incomeData,
        'expenses' => $expenseData,
    ];
}
}



