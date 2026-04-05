<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JWTService;

class AuthFilter implements FilterInterface
{
    protected $jwtService;

    public function __construct()
    {
        $this->jwtService = new JWTService();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $isAuthenticated = false;
        $userData = null;

        // Cek JWT token terlebih dahulu
        $token = $this->jwtService->getTokenFromRequest();
        if ($token) {
            $userData = $this->jwtService->verifyToken($token);
            if ($userData) {
                $isAuthenticated = true;
                // Set session dari JWT untuk kompatibilitas
                session()->set([
                    'id' => $userData['id'],
                    'username' => $userData['username'],
                    'role' => trim((string) ($userData['role'] ?? '')),
                    'isLoggedIn' => true
                ]);
            }
        }

        // Fallback ke session jika JWT tidak ada atau invalid
        if (!$isAuthenticated && session()->get('isLoggedIn')) {
            $isAuthenticated = true;
            $userData = [
                'id' => session()->get('id'),
                'username' => session()->get('username'),
                'role' => trim((string) session()->get('role')),
            ];
        }

        // Jika tidak terautentikasi, redirect ke login
        if (!$isAuthenticated) {
            return redirect()->to('login');
        }

        // Optional: Role based filtering if arguments are provided
        if ($arguments && $userData && !in_array($userData['role'], $arguments)) {
            return redirect()->back()->with('error', 'Akses tidak diizinkan');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
