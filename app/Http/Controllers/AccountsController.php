<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $account_type = Schema::hasTable('account_type')
            ? DB::table('account_type')->select('id', 'account_type')->orderBy('id')->get()
            : collect();

        $accountsCount = Schema::hasTable('accounts')
            ? $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->count()
            : 0;

        $totalAccountsBalance = Schema::hasTable('accounts')
            ? $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->sum('balance')
            : 0;

        return view('contents.accounts.index', compact(
            'account_type',
            'accountsCount',
            'totalAccountsBalance'
        ));
    }

    public function add(Request $request)
    {
        $account_type = Schema::hasTable('account_type')
            ? DB::table('account_type')->select('id', 'account_type')->orderBy('id')->get()
            : collect();

        return view('contents.accounts.add', compact('account_type'));
    }

    public function listTable(Request $request)
    {
        if (!Schema::hasTable('accounts')) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        if (Schema::hasTable('account_type')) {
            $query = $this->scopeCreatedByCurrentUser(
                DB::table('accounts as a')->leftJoin('account_type as act', 'a.account_type_id', '=', 'act.id'),
                'accounts',
                'a.created_by'
            )->select(
                'a.id',
                'a.account_number',
                'a.account_type_id',
                'a.bank_name',
                'a.branch',
                'a.ifsc_code',
                'a.balance',
                DB::raw("COALESCE(act.account_type, '-') as account_type"),
                DB::raw("TO_CHAR(a.created_at, 'DD-MM-YYYY HH24:MI') as created_at"),
                DB::raw("TO_CHAR(a.updated_at, 'DD-MM-YYYY HH24:MI') as updated_at")
            );
        } else {
            $query = $this->scopeCreatedByCurrentUser(DB::table('accounts as a'), 'accounts', 'a.created_by')
                ->select(
                    'a.id',
                    'a.account_number',
                    'a.account_type_id',
                    'a.bank_name',
                    'a.branch',
                    'a.ifsc_code',
                    'a.balance',
                    DB::raw("CAST(a.account_type_id AS TEXT) as account_type"),
                    DB::raw("TO_CHAR(a.created_at, 'DD-MM-YYYY HH24:MI') as created_at"),
                    DB::raw("TO_CHAR(a.updated_at, 'DD-MM-YYYY HH24:MI') as updated_at")
                );
        }

        if (!empty($request->search['value'])) {
            $search = strtolower($request->search['value']);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(a.account_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.bank_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.branch) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.ifsc_code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(a.balance AS TEXT) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(a.created_at, 'DD-MM-YYYY HH24:MI') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("TO_CHAR(a.updated_at, 'DD-MM-YYYY HH24:MI') LIKE ?", ["%{$search}%"]);

                if (Schema::hasTable('account_type')) {
                    $q->orWhereRaw('LOWER(act.account_type) LIKE ?', ["%{$search}%"]);
                } else {
                    $q->orWhereRaw('CAST(a.account_type_id AS TEXT) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        $totalRecords = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')->count();
        $filteredRecords = (clone $query)->count();

        $query->orderBy('a.id', 'desc');

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

    public function edit($id)
    {
        $record = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
            ->where('id', $id)
            ->first();

        if (!$record) {
            abort(404);
        }

        $account_type = Schema::hasTable('account_type')
            ? DB::table('account_type')->select('id', 'account_type')->orderBy('id')->get()
            : collect();

        return view('contents.accounts.edit', compact('record', 'account_type'));
    }

    public function view($id)
    {
        if (Schema::hasTable('account_type')) {
            $record = $this->scopeCreatedByCurrentUser(
                DB::table('accounts as a')->leftJoin('account_type as act', 'a.account_type_id', '=', 'act.id'),
                'accounts',
                'a.created_by'
            )->select(
                'a.*',
                DB::raw("COALESCE(act.account_type, '-') as account_type")
            )
                ->where('a.id', $id)
                ->first();
        } else {
            $record = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->where('id', $id)
                ->first();

            if ($record) {
                $record->account_type = $record->account_type_id ?? '-';
            }
        }

        if (!$record) {
            abort(404);
        }

        return view('contents.accounts.view', compact('record'));
    }

    public function submit(Request $request)
    {
        $id = null;

        if ($request->filled('account_id')) {
            $decodedId = base64_decode($request->account_id);

            if (!is_numeric($decodedId)) {
                throw ValidationException::withMessages([
                    'account_id' => 'Invalid account id.'
                ]);
            }

            $id = (int) $decodedId;
        }

        $accountNumberRule = Rule::unique('accounts', 'account_number');

        if ($id) {
            $accountNumberRule->ignore($id);
        }

        $request->validate([
            'account_number' => ['required', 'max:100', $accountNumberRule],
            'account_type_id' => 'required|exists:account_type,id',
            'bank_name' => 'required|max:100',
            'branch' => 'required|max:100',
            'ifsc_code' => 'required|max:100',
            'balance' =>     'numeric','min:0','regex:/^\d+(\.\d{1,2})?$/'
        ]);

        $data = [
            'account_number' => trim($request->account_number),
            'account_type_id' => (int) $request->account_type_id,
            'bank_name' => trim($request->bank_name),
            'branch' => trim($request->branch),
            'ifsc_code' => trim($request->ifsc_code),
            'balance' => $request->balance,
        ];

        if (Schema::hasColumn('accounts', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn('accounts', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        if ($id) {
            $record = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->where('id', $id)
                ->first();

            if (!$record) {
                throw ValidationException::withMessages([
                    'account_id' => 'Account not found.'
                ]);
            }

            DB::table('accounts')->where('id', $id)->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully.'
            ]);
        } else {
            if (Schema::hasColumn('accounts', 'created_at')) {
                $data['created_at'] = now();
            }

            if (Schema::hasColumn('accounts', 'created_by')) {
                $data['created_by'] = Auth::id();
            }

            DB::table('accounts')->insert($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Account added successfully.'
            ]);
        }
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $record = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$record) {
                throw ValidationException::withMessages([
                    'account_id' => 'Account not found.'
                ]);
            }

            if (Schema::hasTable('transactions')) {
                $usedInTransactions = $this->scopeCreatedByCurrentUser(DB::table('transactions'), 'transactions')
                    ->where('account_number_id', $id)
                    ->exists();

                if ($usedInTransactions) {
                    throw ValidationException::withMessages([
                        'account_id' => 'This account is already used in transactions and cannot be deleted.'
                    ]);
                }
            }

            DB::table('accounts')->where('id', $id)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Account deleted successfully.'
        ]);
    }
}
