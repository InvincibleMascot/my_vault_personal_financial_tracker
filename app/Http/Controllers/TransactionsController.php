<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $transactionTypeOptions = Schema::hasTable('transaction_type')
            ? DB::table('transaction_type')->select('id', 'transaction_type')->orderBy('id')->get()
            : collect();

        $transactionMethodOptions = Schema::hasTable('transaction_method')
            ? DB::table('transaction_method')->select('id', 'transaction_method')->orderBy('id')->get()
            : collect();

        $accountNumberOptions = Schema::hasTable('accounts')
            ? $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->select('id', 'account_number', 'bank_name', 'branch', 'balance')->orderBy('id')->get()
            : collect();

        $transactionCategoryOptions = Schema::hasTable('transaction_category')
            ? DB::table('transaction_category')->select('id', 'transaction_category')->orderBy('id')->get()
            : collect();

        $totalBankAccountBalance = Schema::hasTable('accounts')
            ? (int) $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->sum('balance')
            : 0;

        $cashTransactionTypeIds = Schema::hasTable('transaction_type')
            ? DB::table('transaction_type')
                ->whereRaw('LOWER(transaction_type) = ?', ['cash'])
                ->pluck('id')
            : collect();

        $latestCashBalance = 0;

        if (Schema::hasTable('transactions') && $cashTransactionTypeIds->isNotEmpty()) {
            $latestCashBalance = (int) (
                $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->whereIn('transaction_type_id', $cashTransactionTypeIds)
                    ->whereNotNull('cash_balance')
                    ->orderBy('id', 'desc')
                    ->value('cash_balance') ?? 0
            );
        }

        $debitTransactionMethodIds = Schema::hasTable('transaction_method')
            ? DB::table('transaction_method')
                ->whereRaw('LOWER(transaction_method) = ?', ['debit'])
                ->pluck('id')
            : collect();

        $totalDebitAmountThisMonth = 0;

        if (Schema::hasTable('transactions') && $debitTransactionMethodIds->isNotEmpty()) {
            $totalDebitAmountThisMonth = (int) $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->whereIn('transaction_method_id', $debitTransactionMethodIds)
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->sum('amount_paid');
        }

        $totalAvailableBalance = $totalBankAccountBalance + $latestCashBalance;

        return view('contents.transactions.index', [
            'transaction_type' => $transactionTypeOptions,
            'transaction_method' => $transactionMethodOptions,
             'transaction_category' => $transactionCategoryOptions,
            'account_number' => $accountNumberOptions,
            'accountBalance' => $totalBankAccountBalance,
            'cashBalance' => $latestCashBalance,
            'totalSpentThisMonth' => $totalDebitAmountThisMonth,
            'availableBalance' => $totalAvailableBalance,
            'transaction_category' => $transactionCategoryOptions,
        ]);
    }

    public function listTable(Request $request)
    {
        if (!Schema::hasTable('transactions')) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $transactionsListQuery = $this->scopeCreatedByCurrentUser(DB::table('transactions as t'), 'transactions', 't.created_by')
            ->leftJoin('transaction_method as tm', 't.transaction_method_id', '=', 'tm.id')
            ->leftJoin('transaction_type as tt', 't.transaction_type_id', '=', 'tt.id')
            ->leftJoin('transaction_category as tc', 't.transaction_category_id', '=', 'tc.id')
            ->leftJoin('accounts as acc', 't.account_number_id', '=', 'acc.id')
           ->select(
                't.id',
                't.transaction_name',
                't.transaction_description',
                't.transaction_method_id',
                't.transaction_type_id',
                't.transaction_category_id',
                't.account_number_id',
                't.amount_paid',
                't.account_balance',
                't.cash_balance',
                DB::raw("COALESCE(tm.transaction_method, '-') as transaction_method"),
                DB::raw("COALESCE(tt.transaction_type, '-') as transaction_type"),
                DB::raw("COALESCE(tc.transaction_category, '-') as transaction_category"),
                DB::raw("COALESCE(acc.account_number, '-') as account_number"),
                DB::raw("TO_CHAR(t.created_at, 'DD-MM-YYYY HH24:MI') as created_at"),
                DB::raw("TO_CHAR(t.updated_at, 'DD-MM-YYYY HH24:MI') as updated_at")
            );

        if (!empty($request->search['value'])) {
            $datatableSearchText = strtolower($request->search['value']);

            $transactionsListQuery->where(function ($transactionSearchQuery) use ($datatableSearchText) {
                $transactionSearchQuery->whereRaw('LOWER(t.transaction_name) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('LOWER(t.transaction_description) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('LOWER(tm.transaction_method) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('LOWER(tt.transaction_type) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('LOWER(tc.transaction_category) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('LOWER(acc.account_number) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw('CAST(t.amount_paid AS TEXT) LIKE ?', ["%{$datatableSearchText}%"])
                    ->orWhereRaw("TO_CHAR(t.created_at, 'DD-MM-YYYY HH24:MI') LIKE ?", ["%{$datatableSearchText}%"])
                    ->orWhereRaw("TO_CHAR(t.updated_at, 'DD-MM-YYYY HH24:MI') LIKE ?", ["%{$datatableSearchText}%"]);
            });
        }

        $totalTransactionRecords = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')->count();
        $filteredTransactionRecords = (clone $transactionsListQuery)->count();

        $transactionsListQuery->orderBy('t.id', 'desc');

        $datatablePageLength = (int) $request->length;
        if ($datatablePageLength <= 0) {
            $datatablePageLength = 10;
        }

        $transactionRowsForCurrentPage = $transactionsListQuery
            ->skip((int) $request->start)
            ->take($datatablePageLength)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalTransactionRecords,
            'recordsFiltered' => $filteredTransactionRecords,
            'data' => $transactionRowsForCurrentPage,
        ]);
    }

    public function summary(Request $request)
    {
        $totalBankAccountBalance = Schema::hasTable('accounts')
            ? (int) $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->sum('balance')
            : 0;

        $cashTransactionTypeIds = Schema::hasTable('transaction_type')
            ? DB::table('transaction_type')
                ->whereRaw('LOWER(transaction_type) = ?', ['cash'])
                ->pluck('id')
            : collect();

        $latestCashBalance = 0;

        if (Schema::hasTable('transactions') && $cashTransactionTypeIds->isNotEmpty()) {
            $latestCashBalance = (int) (
                $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->whereIn('transaction_type_id', $cashTransactionTypeIds)
                    ->whereNotNull('cash_balance')
                    ->orderBy('id', 'desc')
                    ->value('cash_balance') ?? 0
            );
        }

        $debitTransactionMethodIds = Schema::hasTable('transaction_method')
            ? DB::table('transaction_method')
                ->whereRaw('LOWER(transaction_method) = ?', ['debit'])
                ->pluck('id')
            : collect();

        $totalDebitAmountThisMonth = 0;

        if (Schema::hasTable('transactions') && $debitTransactionMethodIds->isNotEmpty()) {
            $totalDebitAmountThisMonth = (int) $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->whereIn('transaction_method_id', $debitTransactionMethodIds)
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->sum('amount_paid');
        }

        $totalAvailableBalance = $totalBankAccountBalance + $latestCashBalance;

        return response()->json([
            'accountBalance' => $totalBankAccountBalance,
            'cashBalance' => $latestCashBalance,
            'totalSpentThisMonth' => $totalDebitAmountThisMonth,
            'availableBalance' => $totalAvailableBalance,
        ]);
    }

    public function edit($transactionId)
    {
        $transactionRecord = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
            ->where('id', $transactionId)
            ->first();

        if (!$transactionRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transactionRecord
        ]);
    }

    public function view($transactionId)
    {
        $transactionRecordWithDetails = $this->scopeCreatedByCurrentUser(DB::table('transactions as t'), 'transactions', 't.created_by')
            ->leftJoin('transaction_method as tm', 't.transaction_method_id', '=', 'tm.id')
            ->leftJoin('transaction_type as tt', 't.transaction_type_id', '=', 'tt.id')
            ->leftJoin('transaction_category as tc', 't.transaction_category_id', '=', 'tc.id')
            ->leftJoin('accounts as acc', 't.account_number_id', '=', 'acc.id')
            ->select(
                't.*',
                DB::raw("COALESCE(tm.transaction_method, '-') as transaction_method"),
                DB::raw("COALESCE(tt.transaction_type, '-') as transaction_type"),
                DB::raw("COALESCE(acc.account_number, '-') as account_number"),
                DB::raw("COALESCE(tc.transaction_category, '-') as transaction_category")
            )
            ->where('t.id', $transactionId)
            ->first();

        if (!$transactionRecordWithDetails) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transactionRecordWithDetails
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'transaction_name' => 'required|max:50',
            'transaction_method_id' => 'required|exists:transaction_method,id',
            'transaction_type_id' => 'required|exists:transaction_type,id',
            'transaction_category_id' => 'required|exists:transaction_category,id',
            'amount_paid' => 'required|numeric|min:1',
            'transaction_description' => 'required|max:300',
            'account_number_id' => 'nullable|exists:accounts,id',
        ]);

        $transactionId = $request->filled('transaction_id')
            ? base64_decode($request->transaction_id)
            : null;

        $selectedTransactionTypeName = DB::table('transaction_type')
            ->where('id', $request->transaction_type_id)
            ->value('transaction_type');

        $selectedTransactionMethodName = DB::table('transaction_method')
            ->where('id', $request->transaction_method_id)
            ->value('transaction_method');

        $selectedTransactionTypeName = strtolower(trim($selectedTransactionTypeName));
        $selectedTransactionMethodName = strtolower(trim($selectedTransactionMethodName));

        $isCashTransaction = $selectedTransactionTypeName === 'cash';

        if ($selectedTransactionMethodName === 'credit') {
            $selectedMethodBalanceDirection = 1;
        } elseif ($selectedTransactionMethodName === 'debit') {
            $selectedMethodBalanceDirection = -1;
        } else {
            throw ValidationException::withMessages([
                'transaction_method_id' => 'Transaction method must be Credit or Debit.'
            ]);
        }

        if (!$isCashTransaction && !$request->filled('account_number_id')) {
            throw ValidationException::withMessages([
                'account_number_id' => 'Account Number is required for Net Banking, UPI and Card Payment.'
            ]);
        }

        DB::transaction(function () use ($request, $transactionId, $isCashTransaction, $selectedMethodBalanceDirection) {
            $existingTransactionWasCash = false;

            if ($transactionId) {
                $existingTransactionRecord = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->where('id', $transactionId)
                    ->lockForUpdate()
                    ->first();

                if (!$existingTransactionRecord) {
                    throw ValidationException::withMessages([
                        'transaction_id' => 'Transaction not found.'
                    ]);
                }

                $existingTransactionTypeName = DB::table('transaction_type')
                    ->where('id', $existingTransactionRecord->transaction_type_id)
                    ->value('transaction_type');

                $existingTransactionTypeName = strtolower(trim($existingTransactionTypeName));
                $existingTransactionWasCash = $existingTransactionTypeName === 'cash';

                $existingTransactionMethodName = DB::table('transaction_method')
                    ->where('id', $existingTransactionRecord->transaction_method_id)
                    ->value('transaction_method');

                $existingTransactionMethodName = strtolower(trim($existingTransactionMethodName));

                if ($existingTransactionMethodName === 'credit') {
                    $existingMethodBalanceDirection = 1;
                } elseif ($existingTransactionMethodName === 'debit') {
                    $existingMethodBalanceDirection = -1;
                } else {
                    $existingMethodBalanceDirection = 0;
                }

                if (!$existingTransactionWasCash && !empty($existingTransactionRecord->account_number_id)) {
                    $existingTransactionAccount = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                        ->where('id', $existingTransactionRecord->account_number_id)
                        ->lockForUpdate()
                        ->first();

                    if ($existingTransactionAccount) {
                        $accountBalanceBeforeReversal = (int) ($existingTransactionAccount->balance ?? 0);
                        $accountBalanceAfterReversal = $accountBalanceBeforeReversal - ($existingMethodBalanceDirection * (int) $existingTransactionRecord->amount_paid);

                        DB::table('accounts')
                            ->where('id', $existingTransactionRecord->account_number_id)
                            ->update([
                                'balance' => $accountBalanceAfterReversal,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            $selectedAccountId = null;
            $accountBalanceAfterTransaction = null;
            $cashBalanceAfterTransaction = null;

            if (!$isCashTransaction) {
                $selectedAccountId = (int) $request->account_number_id;

                $selectedAccount = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                    ->where('id', $selectedAccountId)
                    ->lockForUpdate()
                    ->first();

                if (!$selectedAccount) {
                    throw ValidationException::withMessages([
                        'account_number_id' => 'Selected Account Number does not exist.'
                    ]);
                }

                $accountBalanceBeforeTransaction = (int) ($selectedAccount->balance ?? 0);
                $transactionAmount = (int) $request->amount_paid;

                if ($selectedMethodBalanceDirection == 1) {
                    $accountBalanceAfterTransaction = $accountBalanceBeforeTransaction + $transactionAmount;
                } else {
                    $accountBalanceAfterTransaction = $accountBalanceBeforeTransaction - $transactionAmount;
                }

                DB::table('accounts')
                    ->where('id', $selectedAccountId)
                    ->update([
                        'balance' => $accountBalanceAfterTransaction,
                        'updated_at' => now(),
                    ]);
            }

            $transactionDataToSave = [
                'transaction_name' => trim($request->transaction_name),
                'transaction_description' => trim($request->transaction_description),
                'transaction_method_id' => (int) $request->transaction_method_id,
                'transaction_type_id' => (int) $request->transaction_type_id,
                'account_number_id' => $selectedAccountId,
                'amount_paid' => (int) $request->amount_paid,
                'account_balance' => $accountBalanceAfterTransaction,
                'cash_balance' => $cashBalanceAfterTransaction,
                'transaction_category_id' => (int) $request->transaction_category_id,
            ];

            if (Schema::hasColumn('transactions', 'updated_at')) {
                $transactionDataToSave['updated_at'] = now();
            }

            if (Schema::hasColumn('transactions', 'updated_by')) {
                $transactionDataToSave['updated_by'] = Auth::id();
            }

            if ($transactionId) {
                DB::table('transactions')->where('id', $transactionId)->update($transactionDataToSave);
            } else {
                if (Schema::hasColumn('transactions', 'created_at')) {
                    $transactionDataToSave['created_at'] = now();
                }

                if (Schema::hasColumn('transactions', 'created_by')) {
                    $transactionDataToSave['created_by'] = Auth::id();
                }

                DB::table('transactions')->insert($transactionDataToSave);
            }

            if ($isCashTransaction || $existingTransactionWasCash) {
                $cashTransactionTypeIds = DB::table('transaction_type')
                    ->whereRaw('LOWER(transaction_type) = ?', ['cash'])
                    ->pluck('id');

                $cashTransactionRowsForRecalculation = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->whereIn('transaction_type_id', $cashTransactionTypeIds)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $recalculatedCashRunningBalance = 0;

                foreach ($cashTransactionRowsForRecalculation as $cashTransactionRow) {
                    $cashTransactionMethodName = DB::table('transaction_method')
                        ->where('id', $cashTransactionRow->transaction_method_id)
                        ->value('transaction_method');

                    $cashTransactionMethodName = strtolower(trim($cashTransactionMethodName));

                    if ($cashTransactionMethodName === 'credit') {
                        $cashTransactionBalanceDirection = 1;
                    } elseif ($cashTransactionMethodName === 'debit') {
                        $cashTransactionBalanceDirection = -1;
                    } else {
                        $cashTransactionBalanceDirection = 0;
                    }

                    $recalculatedCashRunningBalance = $recalculatedCashRunningBalance + ($cashTransactionBalanceDirection * (int) $cashTransactionRow->amount_paid);

                    DB::table('transactions')
                        ->where('id', $cashTransactionRow->id)
                        ->update([
                            'account_number_id' => null,
                            'account_balance' => null,
                            'cash_balance' => $recalculatedCashRunningBalance,
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => $transactionId ? 'Transaction updated successfully.' : 'Transaction added successfully.'
        ]);
    }

    public function destroy($transactionId)
    {
        DB::transaction(function () use ($transactionId) {
            $transactionRecordToDelete = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                ->where('id', $transactionId)
                ->lockForUpdate()
                ->first();

            if (!$transactionRecordToDelete) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'Transaction not found.'
                ]);
            }

            $transactionTypeNameToDelete = DB::table('transaction_type')
                ->where('id', $transactionRecordToDelete->transaction_type_id)
                ->value('transaction_type');

            $transactionMethodNameToDelete = DB::table('transaction_method')
                ->where('id', $transactionRecordToDelete->transaction_method_id)
                ->value('transaction_method');

            $transactionTypeNameToDelete = strtolower(trim($transactionTypeNameToDelete));
            $transactionMethodNameToDelete = strtolower(trim($transactionMethodNameToDelete));

            $deletedTransactionWasCash = $transactionTypeNameToDelete === 'cash';

            if ($transactionMethodNameToDelete === 'credit') {
                $deletedTransactionBalanceDirection = 1;
            } elseif ($transactionMethodNameToDelete === 'debit') {
                $deletedTransactionBalanceDirection = -1;
            } else {
                $deletedTransactionBalanceDirection = 0;
            }

            if (!$deletedTransactionWasCash && !empty($transactionRecordToDelete->account_number_id)) {
                $affectedAccount = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                    ->where('id', $transactionRecordToDelete->account_number_id)
                    ->lockForUpdate()
                    ->first();

                if ($affectedAccount) {
                    $accountBalanceBeforeDeleteReversal = (int) ($affectedAccount->balance ?? 0);
                    $accountBalanceAfterDeleteReversal = $accountBalanceBeforeDeleteReversal - ($deletedTransactionBalanceDirection * (int) $transactionRecordToDelete->amount_paid);

                    DB::table('accounts')
                        ->where('id', $transactionRecordToDelete->account_number_id)
                        ->update([
                            'balance' => $accountBalanceAfterDeleteReversal,
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('transactions')->where('id', $transactionId)->delete();

            if ($deletedTransactionWasCash) {
                $cashTransactionTypeIds = DB::table('transaction_type')
                    ->whereRaw('LOWER(transaction_type) = ?', ['cash'])
                    ->pluck('id');

                $cashTransactionRowsForRecalculation = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->whereIn('transaction_type_id', $cashTransactionTypeIds)
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                $recalculatedCashRunningBalance = 0;

                foreach ($cashTransactionRowsForRecalculation as $cashTransactionRow) {
                    $cashTransactionMethodName = DB::table('transaction_method')
                        ->where('id', $cashTransactionRow->transaction_method_id)
                        ->value('transaction_method');

                    $cashTransactionMethodName = strtolower(trim($cashTransactionMethodName));

                    if ($cashTransactionMethodName === 'credit') {
                        $cashTransactionBalanceDirection = 1;
                    } elseif ($cashTransactionMethodName === 'debit') {
                        $cashTransactionBalanceDirection = -1;
                    } else {
                        $cashTransactionBalanceDirection = 0;
                    }

                    $recalculatedCashRunningBalance = $recalculatedCashRunningBalance + ($cashTransactionBalanceDirection * (int) $cashTransactionRow->amount_paid);

                    DB::table('transactions')
                        ->where('id', $cashTransactionRow->id)
                        ->update([
                            'account_number_id' => null,
                            'account_balance' => null,
                            'cash_balance' => $recalculatedCashRunningBalance,
                            'updated_at' => now(),
                        ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully.'
        ]);
    }
}
