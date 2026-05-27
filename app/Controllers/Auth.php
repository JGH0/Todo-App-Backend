<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login_register');
    }

    public function attemptLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            session()->set('user_id', $user['id']);
            session()->set('user_email', $user['email']);
            return redirect()->to('/dashboard'); // or wherever
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }

    public function attemptRegister()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $name = $this->request->getPost('name');

        $userModel = new UserModel();
        $data = [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
        ];

        if ($userModel->insert($data)) {
            // Registration successful, auto login
            $user = $userModel->where('email', $email)->first();
            session()->set('user_id', $user['id']);
            session()->set('user_email', $user['email']);
            return redirect()->to('/dashboard');
        } else {
            return redirect()->back()->with('error', $userModel->errors());
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }
}