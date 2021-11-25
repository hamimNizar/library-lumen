<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
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
    public function index(Request $request)
    {
        if($request->auth->role == 'user'){
            return response()->json([
                'success' => false,
                'message' => 'Youre not authorized',
            ], 403);
        }
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
            ], 403);
        }
    }

    public function show(Request $request, $userId)
    {   
        if ($request->auth->role == 'admin') {
            // try{
            //     $user = User::findOrFail($userId);
            //     dd($user);
            // } catch (QueryException $error) {
            //     // dd($error);
            //     return response()->json([
            //         'success' => false,
            //         'message' => "Gagal" . $error->errorInfo,
            //     ], 404);
            // } catch (ModelNotFoundException $e) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "GGAAG" . $e->errorInfo,
            //     ], 404);
            // }
            $user = User::find($userId);
            
            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request Success',
                    'data' => ([
                        'user' => $user
                    ])
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Request Failed',
                ], 404);
            }
        }else{
            if($userId != $request->auth->id){
                return response()->json([
                    'success' => false,
                    'message' => 'Request not match',
                ], 403);
            }
            $user = User::findOrFail($userId);
            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request Success',
                    'data' => ([
                        'user' => $user
                    ])
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Request Failed',
                ], 403);
            }
        }
        
    }

    public function update(Request $request, $userId)
    {
        if ($userId != $request->auth->id){
            return response()->json([
                'success' => false,
                'message' => 'Request Failed',
            ], 403);
        }
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'email' => 'required',
            // 'password' => 'required',
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

    public function destroy(Request $request, $userId)
    {
        if ($userId != $request->auth->id) {
            return response()->json([
                'success' => false,
                'message' => 'Request Failed',
            ], 403);
        }
        
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
