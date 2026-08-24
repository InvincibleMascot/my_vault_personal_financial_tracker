<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SavingsGoalsController extends Controller
{
    public function index()
    {
        $savingTotals = $this->scopeCreatedByCurrentUser(DB::table('savings_goals'), 'savings_goals')
            ->selectRaw('COALESCE(SUM(savings_amount), 0) as total_saving_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN total_account_savings ~ '^[0-9]+(\\.[0-9]+)?$' THEN total_account_savings::numeric ELSE 0 END), 0) as total_account_savings")
            ->selectRaw("COALESCE(SUM(CASE WHEN totol_cash_saving ~ '^[0-9]+(\\.[0-9]+)?$' THEN totol_cash_saving::numeric ELSE 0 END), 0) as total_cash_saving")
            ->first();

        return view('contents.savings_goals.index', compact('savingTotals'));
    }

    public function savings_add()
    {
        $accounts = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
            ->select('id', 'account_number')
            ->orderBy('id', 'desc')
            ->get();

        $durations = DB::table('duration')
            ->select('id', 'duration_name')
            ->orderBy('id', 'asc')
            ->get();

        $transaction_types = DB::table('transaction_type')
            ->select('id', 'transaction_type')
            ->orderBy('id', 'asc')
            ->get();

        return view('contents.savings_goals.add', compact(
            'accounts',
            'durations',
            'transaction_types'
        ));
    }

    public function savings_submit(Request $request)
    {
        $method = DB::table('transaction_type')
            ->where('id', $request->savings_method)
            ->first();

        $methodName = strtolower(trim($method->transaction_type ?? ''));
        $isCash = $methodName === 'cash';

        $rules = [
            'saving_name' => 'required|string|max:100',
            'savings_category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'savings_for' => 'required|string|max:100',
            'savings_method' => 'required|integer|exists:transaction_type,id',
            'savings_amount' => 'required|integer|min:1',
            'duration' => 'required|integer|exists:duration,id',
        ];

        if (!$isCash) {
            $rules['accounts_id'] = 'required|integer|exists:accounts,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$isCash) {
            $account = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->where('id', $request->accounts_id)
                ->first();

            if (!$account) {
                return response()->json([
                    'status' => false,
                    'errors' => ['accounts_id' => ['Selected account does not exist.']]
                ], 422);
            }
        }

        $amount = (int) $request->savings_amount;

        $data = [
            'saving_name' => $request->saving_name,
            'description' => $request->description,
            'savings_for' => $request->savings_for,
            'accounts_id' => $isCash ? null : $request->accounts_id,
            'savings_cash' => $isCash ? $amount : null,
            'savings_amount' => $amount,
            'duration' => $request->duration,
            'savings_category' => $request->savings_category,
            'savings_method' => $request->savings_method,
            'total_account_savings' => $isCash ? null : $amount,
            'totol_cash_saving' => $isCash ? $amount : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('savings_goals', 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        if (Schema::hasColumn('savings_goals', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        DB::table('savings_goals')->insert($data);

        return response()->json([
            'status' => true,
            'message' => 'Savings goal added successfully.'
        ]);
    }

    public function listtable(Request $request)
    {
        $totalRecords = $this->scopeCreatedByCurrentUser(DB::table('savings_goals'), 'savings_goals')->count();

        $query = $this->scopeCreatedByCurrentUser(DB::table('savings_goals as a'), 'savings_goals', 'a.created_by')
            ->leftJoin('accounts as b', 'a.accounts_id', '=', 'b.id')
            ->leftJoin('duration as d', 'a.duration', '=', 'd.id')
            ->leftJoin('transaction_type as t', 'a.savings_method', '=', 't.id')
            ->select(
                'a.id',
                DB::raw("COALESCE(a.saving_name, '-') as savings_name"),
                DB::raw("COALESCE(a.savings_category, '-') as savings_category"),
                DB::raw("COALESCE(a.description, '-') as savings_description"),
                DB::raw("COALESCE(a.savings_for, '-') as savings_for"),
                DB::raw("COALESCE(b.account_number, '-') as savings_account"),
                DB::raw("COALESCE(t.transaction_type, '-') as savings_method"),
                DB::raw("COALESCE(d.duration_name, '-') as savings_duration"),
                'a.savings_amount',
                DB::raw("COALESCE(a.total_account_savings, '-') as total_account_savings"),
                DB::raw("COALESCE(a.totol_cash_saving, '-') as total_cash_saving"),
                'a.created_at',
                'a.updated_at'
            );

        if (!empty($request->search['value'])) {
            $search = strtolower($request->search['value']);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(a.id AS TEXT) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.saving_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.savings_category) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.savings_for) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(b.account_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(t.transaction_type) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(d.duration_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(a.savings_amount AS TEXT) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.total_account_savings) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.totol_cash_saving) LIKE ?', ["%{$search}%"]);
            });
        }

        $filteredRecords = (clone $query)->count();

        $columns = [
            'id' => 'a.id',
            'savings_name' => 'a.saving_name',
            'savings_category' => 'a.savings_category',
            'savings_description' => 'a.description',
            'savings_for' => 'a.savings_for',
            'savings_account' => 'b.account_number',
            'savings_method' => 't.transaction_type',
            'savings_duration' => 'd.duration_name',
            'savings_amount' => 'a.savings_amount',
            'total_account_savings' => 'a.total_account_savings',
            'total_cash_saving' => 'a.totol_cash_saving',
            'created_at' => 'a.created_at',
            'updated_at' => 'a.updated_at',
        ];

        if (!empty($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $request->columns[$columnIndex]['data'];
            $direction = $request->order[0]['dir'];

            if (isset($columns[$columnName])) {
                $query->orderBy($columns[$columnName], $direction);
            }
        } else {
            $query->orderBy('a.id', 'desc');
        }

        $length = (int) $request->length;

        if ($length <= 0) {
            $length = 10;
        }

        $data = $query
            ->skip((int) $request->start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

public function savings_view($savingsid)
{
    $record = $this->scopeCreatedByCurrentUser(DB::table('savings_goals as a'), 'savings_goals', 'a.created_by')
        ->leftJoin('accounts as b', 'a.accounts_id', '=', 'b.id')
        ->leftJoin('duration as d', 'a.duration', '=', 'd.id')
        ->leftJoin('transaction_type as t', 'a.savings_method', '=', 't.id')
        ->where('a.id', $savingsid)
        ->select(
            'a.id',
            'a.saving_name',
            DB::raw("COALESCE(a.savings_category, '-') as savings_category"),
            DB::raw("COALESCE(a.description, '-') as description"),
            DB::raw("COALESCE(a.savings_for, '-') as savings_for"),
            DB::raw("COALESCE(b.account_number, '-') as savings_account"),
            DB::raw("COALESCE(t.transaction_type, '-') as savings_method"),
            DB::raw("COALESCE(d.duration_name, '-') as savings_duration"),
            'a.savings_amount',
            DB::raw("COALESCE(a.total_account_savings, '-') as total_account_savings"),
            DB::raw("COALESCE(a.totol_cash_saving, '-') as total_cash_saving"),
            'a.created_at',
            'a.updated_at'
        )
        ->first();

    if (!$record) {
        abort(404, 'Savings goal not found.');
    }

    return view('contents.savings_goals.view', compact('record'));
}

public function savings_edit(Request $request, $savingsid)
{
    $record = $this->scopeCreatedByCurrentUser(DB::table('savings_goals'), 'savings_goals')
        ->where('id', $savingsid)
        ->first();

    if (!$record) {
        abort(404, 'Savings goal not found.');
    }

    if ($request->isMethod('post')) {
        $method = DB::table('transaction_type')
            ->where('id', $request->savings_method)
            ->first();

        $methodName = strtolower(trim($method->transaction_type ?? ''));
        $isCash = $methodName === 'cash';

        $rules = [
            'saving_name' => 'required|string|max:100',
            'savings_category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'savings_for' => 'required|string|max:100',
            'savings_method' => 'required|integer|exists:transaction_type,id',
            'savings_amount' => 'required|integer|min:1',
            'duration' => 'required|integer|exists:duration,id',
        ];

        if (!$isCash) {
            $rules['accounts_id'] = 'required|integer|exists:accounts,id';
        }

        $request->validate($rules);

        if (!$isCash) {
            $account = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->where('id', $request->accounts_id)
                ->first();

            if (!$account) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected account does not exist.',
                ], 422);
            }
        }

        $amount = (int) $request->savings_amount;

        $data = [
            'saving_name' => trim($request->saving_name),
            'savings_category' => trim($request->savings_category),
            'description' => trim($request->description),
            'savings_for' => trim($request->savings_for),
            'savings_method' => $request->savings_method,
            'accounts_id' => $isCash ? null : $request->accounts_id,
            'savings_cash' => $isCash ? $amount : null,
            'savings_amount' => $amount,
            'duration' => $request->duration,
            'total_account_savings' => $isCash ? null : $amount,
            'totol_cash_saving' => $isCash ? $amount : null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('savings_goals', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        DB::table('savings_goals')
            ->where('id', $savingsid)
            ->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Savings goal updated successfully.',
        ]);
    }

    $accounts = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
        ->select('id', 'account_number')
        ->orderBy('id', 'desc')
        ->get();

    $durations = DB::table('duration')
        ->select('id', 'duration_name')
        ->orderBy('id', 'asc')
        ->get();

    $transaction_types = DB::table('transaction_type')
        ->select('id', 'transaction_type')
        ->orderBy('id', 'asc')
        ->get();

    return view('contents.savings_goals.edit', compact(
        'record',
        'accounts',
        'durations',
        'transaction_types'
    ));
}

public function savings_delete(Request $request)
{
    $id = base64_decode($request->rec_id, true);

    if (!$id) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid record.',
        ], 422);
    }

    $record = $this->scopeCreatedByCurrentUser(DB::table('savings_goals'), 'savings_goals')
        ->where('id', $id)
        ->first();

    if (!$record) {
        return response()->json([
            'status' => 'error',
            'message' => 'Savings goal not found.',
        ], 404);
    }

    DB::table('savings_goals')
        ->where('id', $id)
        ->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Savings goal deleted successfully.',
    ]);
}
}
