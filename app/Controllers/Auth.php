<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\JWTService;
use App\Libraries\RateLimiter;
use CodeIgniter\Cookie\Cookie;

class Auth extends BaseController
{
    protected $jwtService;
    protected $rateLimiter;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->rateLimiter = new RateLimiter();
    }

    public function login()
    {
        // Cek jika sudah login via JWT atau session
        $token = $this->jwtService->getTokenFromRequest();
        if ($token) {
            $userData = $this->jwtService->verifyToken($token);
            if ($userData) {
                return redirect()->to(dashboard_url_for_role($userData['role']));
            }
        }

        if (session()->get('isLoggedIn')) {
            return redirect()->to(dashboard_url_for_role((string) session()->get('role')));
        }
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $session = session();
        $model = new UserModel();
        
        // Username/password untuk DB: jangan pakai esc() (bisa mengacau pencocokan & XSS tidak relevan di sini)
        $username = trim((string) $this->request->getPost('username'));
        $password = $this->request->getPost('password');

        // Validasi input
        $validation = \Config\Services::validation();
        if (!$validation->run([
            'username' => $username,
            'password' => $password,
        ], 'login')) {
            return view('auth/login', ['validation' => $validation]);
        }

        // Rate limiting - cek berdasarkan IP dan username
        $clientIP = $this->request->getIPAddress();
        $identifier = $clientIP . '_' . $username;

        if (!$this->rateLimiter->isAllowed($identifier)) {
            $remainingTime = $this->rateLimiter->getRemainingTime($identifier);
            $minutes = ceil($remainingTime / 60);
            $session->setFlashdata('error', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit.");
            return redirect()->to('login');
        }

        // Query menggunakan Query Builder (aman dari SQL injection)
        $user = $model->where('username', $username)->first();

        if ($user) {
            // Verify password menggunakan password_verify (aman dari timing attack)
            if (password_verify($password, $user['password'])) {
                // Cek apakah user aktif
                if (isset($user['is_active']) && $user['is_active'] == 0) {
                    $session->setFlashdata('error', 'Akun Anda telah dinonaktifkan.');
                    return redirect()->to('login');
                }

                $role = trim((string) ($user['role'] ?? ''));

                // Reset rate limiting setelah login berhasil
                $this->rateLimiter->resetAttempts($identifier);

                // Generate JWT token
                $token = $this->jwtService->generateToken([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $role,
                ]);

                // Set session data (untuk kompatibilitas)
                $ses_data = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'role'     => $role,
                    'isLoggedIn' => TRUE,
                    'jwt_token' => $token
                ];
                $session->set($ses_data);

                // Set JWT token di cookie (HttpOnly untuk keamanan)
                $response = redirect()->to(dashboard_url_for_role($role));
                
                // Set cookie menggunakan Cookie class CodeIgniter 4
                $cookie = new Cookie(
                    'jwt_token',
                    $token,
                    [
                        'expires' => time() + 86400, // 24 jam
                        'httponly' => true,
                        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                        'samesite' => Cookie::SAMESITE_LAX
                    ]
                );
                
                $response->setCookie($cookie);

                return $response;
            } else {
                // Record failed attempt
                $this->rateLimiter->recordFailedAttempt($identifier);
                $remaining = $this->rateLimiter->getRemainingAttempts($identifier);
                
                $session->setFlashdata('error', "Password salah. Sisa percobaan: {$remaining}");
                return redirect()->to('login');
            }
        } else {
            // Record failed attempt (jangan reveal bahwa username tidak ada)
            $this->rateLimiter->recordFailedAttempt($identifier);
            $remaining = $this->rateLimiter->getRemainingAttempts($identifier);
            
            $session->setFlashdata('error', "Username atau password salah. Sisa percobaan: {$remaining}");
            return redirect()->to('login');
        }
    }

    public function logout()
    {
        $session = session();
        
        // Hapus JWT token dari cookie
        $response = redirect()->to('login');
        $response->deleteCookie('jwt_token');
        
        // Destroy session
        $session->destroy();
        
        return $response;
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken()
    {
        $token = $this->jwtService->getTokenFromRequest();
        if (!$token) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token tidak ditemukan'
            ])->setStatusCode(401);
        }

        $newToken = $this->jwtService->refreshToken($token);
        if ($newToken) {
            // Update token di session dan cookie
            session()->set('jwt_token', $newToken);
            $response = $this->response->setJSON([
                'success' => true,
                'token' => $newToken
            ]);
            // Set cookie menggunakan Cookie class CodeIgniter 4
            $cookie = new Cookie(
                'jwt_token',
                $newToken,
                [
                    'expires' => time() + 86400,
                    'httponly' => true,
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'samesite' => Cookie::SAMESITE_LAX
                ]
            );
            $response->setCookie($cookie);
            return $response;
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Token tidak valid'
        ])->setStatusCode(401);
    }
}
