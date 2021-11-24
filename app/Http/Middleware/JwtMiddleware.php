<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken() ?? $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token required'
            ], 401);
        }

        //TODO: handler jika token tidak sama , signature verification

        try {
            $credential = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provided token is expired'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error while decoding token'
            ], 400);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Signature verification failed'
            ], 400);
        }
        // $user = User::find($credential->sub);
        $user = User::where('email', $credential->sub)->first();
        if($guard == null){
            $request->auth = $user;
            return $next($request);
        }
        if($user->role != $guard){
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 400);
        }
        $request->auth = $user;
        return $next($request);
    }
}
