<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JwtBlacklistService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Logout Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class LogoutController extends Controller
{
    /**
     * Process User Logout
     * 
     * @param Request $request The incoming HTTP request containing tokens
     * @return JsonResponse JSON response confirming logout success
     * 
     * @throws \Exception When token processing fails
     * 
     * @api {get} /logout Process User Logout
     * @apiName Logout
     * @apiGroup Authentication
     * @apiHeader {String} Authorization Bearer token
     * @apiParam {String} refresh_token Refresh token to invalidate
     * @apiSuccess {String} message Logout confirmation message
     */
    public function index(Request $request){
        $secretKey = env('JWT_SECRET');
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Token not provided');
        }

        $token = $matches[1];
        $refreshToken = $request->refresh_token;
        $jwtDecoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $refreshDecoded = JWT::decode($refreshToken, new Key($secretKey, 'HS256'));
       
        $blacklist = app(JwtBlacklistService::class);
        
        $jwtTtl = $jwtDecoded->exp - time();
        $refreshTtl = $refreshDecoded->exp - time();
        
        $blacklist->blacklist($jwtDecoded->jti, $jwtTtl);
        $blacklist->blacklist($refreshDecoded->jti, $refreshTtl);
        
        return $this->successResponse(null, 'Logged out successfully');
    }
}
