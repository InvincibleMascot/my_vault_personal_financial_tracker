<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    public function index()
    {
        return view('contents.users.index');
    }

    public function add()
    {
        $this->ensureSuperAdminCanCreateUser();

        $userTypes = $this->userTypes();

        return view('contents.users.add', compact('userTypes'));
    }

    public function edit($id)
    {
        $record = DB::table('users')->where('id', $id)->first();

        if (!$record) {
            abort(404);
        }

        $userTypes = $this->userTypes();

        return view('contents.users.edit', compact('record', 'userTypes'));
    }

    public function view($id)
    {
        $record = DB::table('users as u')
            ->leftJoin('user_types as ut', 'u.user_type_id', '=', 'ut.id')
            ->select('u.*', DB::raw("COALESCE(ut.user_type, '-') as user_type"))
            ->where('u.id', $id)
            ->first();

        if (!$record) {
            abort(404);
        }

        return view('contents.users.view', compact('record'));
    }

    public function listTable(Request $request)
    {
        $query = DB::table('users as u')
            ->leftJoin('user_types as ut', 'u.user_type_id', '=', 'ut.id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.user_type_id',
                DB::raw("COALESCE(ut.user_type, '-') as user_type"),
                DB::raw("TO_CHAR(u.created_at, 'DD-MM-YYYY HH24:MI') as created_at"),
                DB::raw("TO_CHAR(u.updated_at, 'DD-MM-YYYY HH24:MI') as updated_at")
            );

        if (!empty($request->search['value'])) {
            $search = strtolower($request->search['value']);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(u.name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(u.email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(ut.user_type) LIKE ?', ["%{$search}%"]);
            });
        }

        $totalRecords = DB::table('users')->count();
        $filteredRecords = (clone $query)->count();

        $length = (int) $request->length;
        if ($length <= 0) {
            $length = 10;
        }

        $data = $query
            ->orderBy('u.id', 'desc')
            ->skip((int) $request->start)
            ->take($length)
            ->get()
            ->map(function ($row) {
                $row->user_type_label = ucwords(str_replace('_', ' ', (string) $row->user_type));

                return $row;
            });

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function submit(Request $request)
    {
        $id = null;

        if ($request->filled('user_id')) {
            $decodedId = base64_decode($request->user_id);

            if (!is_numeric($decodedId)) {
                throw ValidationException::withMessages([
                    'user_id' => 'Invalid user id.',
                ]);
            }

            $id = (int) $decodedId;
        }

        $emailRule = Rule::unique('users', 'email');
        if ($id) {
            $emailRule->ignore($id);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', $emailRule],
            'user_type_id' => ['required', 'integer', 'exists:user_types,id'],
        ];

        if ($id) {
            $rules['password'] = ['nullable', 'confirmed', Rules\Password::defaults()];
        } else {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $data = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'user_type_id' => (int) $validated['user_type_id'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if (Schema::hasColumn('users', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        if ($id) {
            $record = DB::table('users')->where('id', $id)->first();

            if (!$record) {
                throw ValidationException::withMessages([
                    'user_id' => 'User not found.',
                ]);
            }

            DB::table('users')->where('id', $id)->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully.',
            ]);
        }

        $this->ensureSuperAdminCanCreateUser();

        if (Schema::hasColumn('users', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('users', 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        DB::table('users')->insert($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User added successfully.',
        ]);
    }

    public function destroy($id)
    {
        $record = DB::table('users')->where('id', $id)->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'user_id' => 'User not found.',
            ]);
        }

        if ((int) $id === (int) Auth::id()) {
            throw ValidationException::withMessages([
                'user_id' => 'You cannot delete your own user account.',
            ]);
        }

        DB::table('users')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }

    private function ensureSuperAdminCanCreateUser(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Only Super Admin can create users.');
        }
    }

    private function userTypes()
    {
        return DB::table('user_types')
            ->select('id', 'user_type')
            ->orderBy('id')
            ->get();
    }
}

