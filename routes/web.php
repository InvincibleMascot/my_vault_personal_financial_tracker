<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsGoalsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersTypeController;
use App\Models\UserType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TimeZone (Header)
    Route::get('/server-time', function () {
        return response()->json([
            'server_time' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
        ]);
    })->name('server.time');

    Route::middleware('user.type.access:' . UserType::ACCESS_OVERVIEW)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('user.type.access:' . UserType::ACCESS_TRANSACTIONS)->group(function () {
        // Transactions
        Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
        Route::post('/transactions_list_table', [TransactionsController::class, 'listTable'])->name('transactions_list_table');
        Route::get('/transactions_summary', [TransactionsController::class, 'summary'])->name('transactions_summary');
        Route::post('/transactions_submit', [TransactionsController::class, 'submit'])->name('transactions_submit');
        Route::get('/transactions_edit/{id}', [TransactionsController::class, 'edit'])->name('transactions_edit');
        Route::get('/transactions_view/{id}', [TransactionsController::class, 'view'])->name('transactions_view');
        Route::get('/transactions_delete/{id}', [TransactionsController::class, 'destroy'])->name('transactions_delete');

        // Accounts
        Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts.index');
        Route::post('/accounts_list_table', [AccountsController::class, 'listTable'])->name('accounts_list_table');
        Route::get('/accounts_add', [AccountsController::class, 'add'])->name('accounts_add');
        Route::get('/accounts_edit/{id}', [AccountsController::class, 'edit'])->name('accounts_edit');
        Route::get('/accounts_view/{id}', [AccountsController::class, 'view'])->name('accounts_view');
        Route::post('/accounts_submit', [AccountsController::class, 'submit'])->name('accounts_submit');
        Route::get('/accounts_delete/{id}', [AccountsController::class, 'destroy'])->name('accounts_delete');

        // Incomes
        Route::get('/income', [IncomeController::class, 'index'])->name('income');
        Route::post('/income/list-table', [IncomeController::class, 'listTable'])->name('income_list_table');
        Route::get('/income_add', [IncomeController::class, 'income_add'])->name('income_add');
        Route::post('/income_submit', [IncomeController::class, 'income_submit'])->name('income_submit');
        Route::get('/income_view/{id}', [IncomeController::class, 'income_view'])->name('income_view');
    });

    Route::middleware('user.type.access:' . UserType::ACCESS_PLANNING)->group(function () {
        // Savings
        Route::get('/savings_goals', [SavingsGoalsController::class, 'index'])->name('savings_goals');
        Route::get('/savings_add', [SavingsGoalsController::class, 'savings_add'])->name('savings_add');
        Route::post('/savings-submit', [SavingsGoalsController::class, 'savings_submit'])->name('savings_submit');
        Route::post('/savings-goals-list-table', [SavingsGoalsController::class, 'listtable'])->name('savings_list_table');
        Route::get('/savings_view/{id}', [SavingsGoalsController::class, 'savings_view'])->name('savings_view');
        Route::match(['get', 'post'], '/savings_edit/{id}', [SavingsGoalsController::class, 'savings_edit'])->name('savings_edit');
        Route::post('/savings-delete', [SavingsGoalsController::class, 'savings_delete'])->name('savings_delete');
    });

    Route::middleware('user.type.access:' . UserType::ACCESS_USER_MANAGEMENT)->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('/users/list-table', [UsersController::class, 'listTable'])->name('users.list-table');
        Route::get('/users_add', [UsersController::class, 'add'])->name('users.add');
        Route::get('/users_edit/{id}', [UsersController::class, 'edit'])->name('users.edit');
        Route::get('/users_view/{id}', [UsersController::class, 'view'])->name('users.view');
        Route::post('/users_submit', [UsersController::class, 'submit'])->name('users.submit');
        Route::get('/users_delete/{id}', [UsersController::class, 'destroy'])->name('users.delete');

        Route::get('/user-types', [UsersTypeController::class, 'index'])->name('user-types.index');
        Route::post('/user-types/list-table', [UsersTypeController::class, 'listTable'])->name('user-types.list-table');
        Route::post('/user-types/submit', [UsersTypeController::class, 'submit'])->name('user-types.submit');
        Route::get('/user-types/edit/{id}', [UsersTypeController::class, 'edit'])->name('user-types.edit');
        Route::get('/user-types/delete/{id}', [UsersTypeController::class, 'destroy'])->name('user-types.delete');
    });
});

require __DIR__.'/auth.php';
