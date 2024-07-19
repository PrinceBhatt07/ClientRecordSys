<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function superAdminPanel()
    {
        return view('super-admin.super-admin-dashboard');
    }
    
    public function getAllUsers()
    {
        try {
            $users = User::all()->toArray();
            return response()->json(['success' => true, 'data' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function addUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'role' => 'required|in:Admin,User',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->all()], 200);
            }

            //added later to resolve email constraint voilation with soft deleted email
            $isSoftDeletedUser = User::onlyTrashed()->where('email', $request->email)->first();
            if ($isSoftDeletedUser) {
                $isSoftDeletedUser->restore();
                $is_admin = ($request->role === 'Admin' ? true : false);
                User::where('email', $request->email)->update([
                    "deleted_at" => NULL,
                    "name" => $request->name,
                    "is_admin" => $is_admin
                ]);
                return response()->json(['success' => true, 'message' => 'User created successfully'], 203);
            }

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ];

            if ($request->role === 'Admin') {
                $userData['is_admin'] = true;
            }

            $user = User::create($userData);

            return response()->json(['success' => true, 'message' => 'User created successfully'], 203);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function assignRoles(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "id" => "required",
                "role" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()], 400);
            }

            $user = User::find($request->id);

            $user->update([
                "is_admin" => $request->role === "Admin" ? true : false
            ]);

            return response()->json(['success' => true, 'message' => 'User role updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()], 400);
            }

            $user = User::find($request->id);
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function editUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "id" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()], 400);
            }

            $user = User::find($request->id);
            if ($user) {
                return response()->json(['success' => true, 'data' => $user], 200);
            }
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "id" => "required",
                "name" => "required",
                "email" => "required|email",
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()], 400);
            }
            $user = User::find($request->id);
            if ($user) {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
                return response()->json(['success' => true, 'message' => 'User updated successfully'], 200);
            }
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
