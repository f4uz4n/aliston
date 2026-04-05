<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\Config\Services;

class JWTService
{
    private $secretKey;
    private $algorithm = 'HS256';
    private $expiration = 86400; // 24 jam dalam detik

    public function __construct()
    {
        // Secret harus stabil antar-request; jangan generate random di sini (token login jadi tidak bisa diverifikasi)
        $key = env('JWT_SECRET_KEY', '');
        $this->secretKey = is_string($key) && $key !== ''
            ? $key
            : 'tour-n-travel-ci4-dev-jwt-set-JWT_SECRET_KEY-di-env';
    }

    /**
     * Generate JWT token untuk user
     */
    public function generateToken(array $userData): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expiration;

        $payload = [
            'iat' => $issuedAt,           // Issued at
            'exp' => $expirationTime,     // Expiration time
            'data' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'role' => $userData['role'],
            ]
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verify dan decode JWT token
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $data = (array) $decoded->data;
            if (isset($data['role'])) {
                $data['role'] = trim((string) $data['role']);
            }

            return $data;
        } catch (\Exception $e) {
            // Token invalid, expired, atau error lainnya
            log_message('error', 'JWT Verification Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresh token (generate token baru dengan data yang sama)
     */
    public function refreshToken(string $oldToken): ?string
    {
        $userData = $this->verifyToken($oldToken);
        if ($userData) {
            return $this->generateToken($userData);
        }
        return null;
    }

    /**
     * Get token dari request header atau cookie
     */
    public function getTokenFromRequest(): ?string
    {
        $request = Services::request();
        
        // Cek dari Authorization header (Bearer token)
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Cek dari cookie
        $token = $request->getCookie('jwt_token');
        if (!empty($token)) {
            return $token;
        }

        // Cek dari session (fallback untuk kompatibilitas)
        $session = Services::session();
        return $session->get('jwt_token');
    }
}
