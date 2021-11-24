<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function index()
    {
        $user = User::all();
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Request Success',
                'data' => ([
                    'users' => $user
                ])
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Request Failed',
            ], 400);
        }
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Request Success',
                'data' => ([
                    'user' => $user
                ])
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Request Failed',
            ], 400);
        }
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        try {
            $user->update($request->all());
            $response = [
                'success' => true,
                'message' => 'User Data Updated',
                'data' => ([
                    'user' => $user
                ])
            ];
            return response()->json($response, 200);
        } catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => "Gagal" . $error->errorInfo,
            ], 400);
        }
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);

        try {
            $user->delete();
            $response = [
                'success' => true,
                'message' => 'User Data Deleted',
            ];
            return response()->json($response, 200);
        } catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => "Gagal" . $error->errorInfo,
            ]);
        }
    }
}
