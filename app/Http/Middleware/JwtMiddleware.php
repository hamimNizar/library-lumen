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
        // dd($token);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token required'
            ], 401);
        }

        

        //TODO: handler jika token tidak sama , signature verification

        try {
            $credential = JWT::decode($token, env('JWT_KEY'), ['HS256']);
            // dd($credential);
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
        // dd($e);
        // $user = User::find($credential->sub);
        $user = User::where('email', $credential->sub)->first();
        // dd($guard);
        // dd($user);

        
        if ($guard == null) {
            $request->auth = $user;
            return $next($request);
        }else if($user->role != $guard){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $request->auth = $user;
        // dd($user);
        return $next($request);
    }
}
