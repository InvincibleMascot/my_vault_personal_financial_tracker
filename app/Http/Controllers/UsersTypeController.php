<?php

namespace App\Http\Controllers;

use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsersTypeController extends Controller
{
    public function index()
    {
        return view('contents.user_types.index');
    }

    public function listTable(Request $request)
    {
        if (!Schema::hasTable('user_types')) {
            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = DB::table('user_types')->select(
            'id',
            'user_type',
            'access_to',
            DB::raw("TO_CHAR(created_at, 'DD-MM-YYYY HH24:MI') as created_at"),
            DB::raw("TO_CHAR(updated_at, 'DD-MM-YYYY HH24:MI') as updated_at")
        );

        if (!empty($request->search['value'])) {
            $search = strtolower($request->search['value']);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(user_type) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(access_to) LIKE ?', ["%{$search}%"]);
            });
        }

        $totalRecords = DB::table('user_types')->count();
        $filteredRecords = (clone $query)->count();

        $length = (int) $request->length;
        if ($length <= 0) {
            $length = 10;
        }

        $data = $query
            ->orderBy('id', 'asc')
            ->skip((int) $request->start)
            ->take($length)
            ->get()
            ->map(function ($row) {
                $normalizedType = UserType::normalize($row->user_type);
                $row->user_type_label = UserType::labels()[$normalizedType] ?? ucwords(str_replace('_', ' ', (string) $row->user_type));
                $row->access_to_label = collect(explode(',', (string) $row->access_to))
                    ->filter()
                    ->map(fn ($item) => ucwords(str_replace('_', ' ', $item)))
                    ->implode(', ');

                return $row;
            });

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function edit($id)
    {
        $record = DB::table('user_types')->where('id', $id)->first();

        if (!$record) {
            abort(404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $record,
        ]);
    }

    public function submit(Request $request)
    {
        $id = null;

        if ($request->filled('user_type_id')) {
            $decodedId = base64_decode($request->user_type_id);

            if (!is_numeric($decodedId)) {
                throw ValidationException::withMessages([
                    'user_type_id' => 'Invalid user type id.',
                ]);
            }

            $id = (int) $decodedId;
        }

        $userTypeRule = Rule::unique('user_types', 'user_type');
        if ($id) {
            $userTypeRule->ignore($id);
        }

        $validated = $request->validate([
            'user_type' => ['required', Rule::in(array_keys(UserType::labels())), $userTypeRule],
        ]);

        $userType = UserType::normalize($validated['user_type']);
        $accessTo = implode(',', UserType::defaultAccessFor($userType));

        $data = [
            'user_type' => $userType,
            'access_to' => $accessTo,
        ];

        if (Schema::hasColumn('user_types', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($id) {
            $record = DB::table('user_types')->where('id', $id)->first();

            if (!$record) {
                throw ValidationException::withMessages([
                    'user_type_id' => 'User type not found.',
                ]);
            }

            DB::table('user_types')->where('id', $id)->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'User type updated successfully.',
            ]);
        }

        if (Schema::hasColumn('user_types', 'created_at')) {
            $data['created_at'] = now();
        }

        DB::table('user_types')->insert($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User type added successfully.',
        ]);
    }

    public function destroy($id)
    {
        $record = DB::table('user_types')->where('id', $id)->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'user_type_id' => 'User type not found.',
            ]);
        }

        if (Schema::hasColumn('users', 'user_type_id')) {
            $usedByUsers = DB::table('users')->where('user_type_id', $id)->exists();

            if ($usedByUsers) {
                throw ValidationException::withMessages([
                    'user_type_id' => 'This user type is assigned to users and cannot be deleted.',
                ]);
            }
        }

        DB::table('user_types')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User type deleted successfully.',
        ]);
    }
}