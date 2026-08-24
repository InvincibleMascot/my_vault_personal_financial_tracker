<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        return view('contents.income.index');
    }

    public function income_add()
    {
        $income_type = DB::table('income_type')
            ->select('id', 'income_type_name')
            ->orderBy('id', 'desc')
            ->get();

        $income_duration = DB::table('duration')
            ->select('id', 'duration_name as income_duration')
            ->orderBy('id', 'desc')
            ->get();

        $accounts = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
            ->select('id', 'account_number')
            ->orderBy('id', 'desc')
            ->get();

        return view('contents.income.add', compact(
            'accounts',
            'income_type',
            'income_duration'
        ));
    }

    public function listTable(Request $request)
    {
        $totalRecords = $this->scopeCreatedByCurrentUser(DB::table('income'), 'income')->count();

        $query = $this->scopeCreatedByCurrentUser(DB::table('income as a'), 'income', 'a.created_by')
            ->leftJoin('accounts as b', 'a.income_credited_account_number', '=', 'b.id')
            ->leftJoin('income_type as c', 'a.income_type', '=', 'c.id')
            ->leftJoin('duration as d', 'a.duration', '=', 'd.id')
            ->select(
                'a.id',
                'a.income_name',
                DB::raw("COALESCE(c.income_type_name, '-') as income_type"),
                'a.income_description',
                'a.income_from',
                'a.income_amount',
                DB::raw("COALESCE(b.account_number, '-') as income_credited_account_number"),
                DB::raw("COALESCE(d.duration_name, '-') as income_duration"),
                'a.created_at',
                'a.updated_at'
            );

        if (!empty($request->search['value'])) {
            $search = strtolower($request->search['value']);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(a.id AS TEXT) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.income_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(c.income_type_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.income_description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(a.income_from) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('CAST(a.income_amount AS TEXT) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(b.account_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(d.duration_name) LIKE ?', ["%{$search}%"]);
            });
        }

        $filteredRecords = (clone $query)->count();

        $columns = [
            'id' => 'a.id',
            'income_name' => 'a.income_name',
            'income_type' => 'c.income_type_name',
            'income_description' => 'a.income_description',
            'income_from' => 'a.income_from',
            'income_amount' => 'a.income_amount',
            'income_credited_account_number' => 'b.account_number',
            'income_duration' => 'd.duration_name',
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

    public function income_submit(Request $request)
    {
        $request->validate([
            'income_name' => 'required|string|max:100',
            'income_type' => 'required|exists:income_type,id',
            'income_from' => 'required|string|max:100',
            'income_amount' => 'required|numeric|min:0',
            'income_credited_account_number' => 'required|exists:accounts,id',
            'income_duration' => 'required|exists:duration,id',
            'income_description' => 'required|string',
        ]);

        $account = $this->scopeCreatedByCurrentUser(DB::table('accounts'), 'accounts')
            ->where('id', $request->income_credited_account_number)
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'income_credited_account_number' => 'Selected account does not exist.',
            ]);
        }

        $data = [
            'income_name' => trim($request->income_name),
            'income_type' => $request->income_type,
            'income_from' => trim($request->income_from),
            'income_amount' => $request->income_amount,
            'income_credited_account_number' => $request->income_credited_account_number,
            'duration' => $request->income_duration,
            'income_description' => trim($request->income_description),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('income', 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        if (Schema::hasColumn('income', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        DB::table('income')->insert($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Income added successfully.',
        ]);
    }

  public function income_view($incomeid)
    {
        $record = $this->scopeCreatedByCurrentUser(DB::table('income as a'), 'income', 'a.created_by')
            ->leftJoin('accounts as b', 'a.income_credited_account_number', '=', 'b.id')
            ->leftJoin('income_type as c', 'a.income_type', '=', 'c.id')
            ->leftJoin('duration as d', 'a.duration', '=', 'd.id')
            ->where('a.id', $incomeid)
            ->select(
                'a.id',
                'a.income_name',
                DB::raw("COALESCE(c.income_type_name, '-') as income_type"),
                'a.income_description',
                'a.income_from',
                'a.income_amount',
                DB::raw("COALESCE(b.account_number, '-') as income_credited_account_number"),
                DB::raw("COALESCE(d.duration_name, '-') as income_duration"),
                'a.created_at',
                'a.updated_at'
            )
            ->first();

        if (!$record) {
            abort(404, 'Income record not found.');
        }

        return view('contents.income.view', compact('record'));
    }
}
