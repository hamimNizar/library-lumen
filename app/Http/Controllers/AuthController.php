<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
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

protected function jwt(User $user)
    {
        $payload = [
            'sub' => $user->email,
            'iss' => "lumen-jwt",
            'aud' => 'http://localhost:8080',
            'iat' => time(),
            'exp' => time() + 60 * 60,
        ];
        return JWT::encode($payload, env('JWT_KEY', 'secret'), 'HS256');
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = Hash::make($request->input('password'));
        $role = $request->input('role');

        try {
            $register = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);
            $tokenJwt = $this->jwt($register);
            $response = [
                'success' => true,
                'message' => 'Register Success!',
                'data' => ([
                    'token' => $tokenJwt
                ])
            ];
            return response()->json($response, 201);
        } catch (QueryException $error) {
            return response()->json([
                'success' => false,
                'message' => "Register Failed!" . $error->errorInfo,
            ], 400);
        }
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        if ($user) {
            if (Hash::check($password, $user->password)) {
                $tokenJwt = $this->jwt($user);
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully Login!',
                    'data' => [
                        'token' => $tokenJwt,
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Password doesnt match!',
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found!',
            ], 404);
        }
    }
}
