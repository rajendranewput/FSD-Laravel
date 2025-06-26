<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Authentication Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class AuthController extends Controller
{
    /**
     * JWT secret key for token encoding/decoding
     * 
     * @var string
     */
    protected $secretKey;

    /**
     * Constructor - Initialize the JWT secret key
     * 
     * Sets up the secret key from environment variables for JWT operations
     */
    public function __construct()
    {
        $this->secretKey = env('JWT_SECRET');
    }

    /**
     * Refresh JWT Token
     * 
     * @param Request $request The incoming HTTP request containing the current token
     * @return JsonResponse JSON response with new token or error message
     * 
     * @throws \Exception When token validation fails
     * 
     * @api {post} /refresh-token Refresh JWT Token
     * @apiName RefreshToken
     * @apiGroup Authentication
     * @apiHeader {String} Authorization Bearer token
     * @apiSuccess {String} access_token New JWT token
     * @apiSuccess {Number} expires_in Token expiration time in seconds
     */
    public function refreshToken(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Token not provided');
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            $payload = [
                'iat'  => time(),
                'exp'  => time() + 3600,
                'data' => [
                    'food_standard_role' => $decoded->data->food_standard_role,
                    'team_name'   => $decoded->data->team_name,
                    'name'    => $decoded->data->name,
                ]
            ];

            $newToken = JWT::encode($payload, $this->secretKey, 'HS256');

            return $this->successResponse([
                'access_token' => $newToken,
                'expires_in'   => 3600
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }
}

