<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use DB;
use App\Http\Controllers\LoginController;
use App\Services\JwtBlacklistService;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return response()->json(['status' => false, 'message' => 'Token not provided', 'action' => 'redirect to login page'], 401);
        }

        $token = $matches[1];
        $secretKey = env('JWT_SECRET');
        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            $jti = $decoded->jti ?? null;
        
            if ($jti && app(JwtBlacklistService::class)->isBlacklisted($jti)) {
                return response()->json(['status' => false, 'message' => 'You have been logged out, Please login again', 'action' => 'redirect to login page'], 401);
            }
            
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            // Attach user data to the request (optional)
            $request->attributes->add(['jwt_user' => (array) $decoded->data]);
            $controller = new LoginController();
            $controller->setCostCentersToRedis($request);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Invalid token: ' . $e->getMessage(), 'action' => 'redirect to login page'], 401);
        }

        return $next($request);
    }
}
