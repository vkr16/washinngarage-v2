<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;

class UsersController extends BaseController
{
    use ResponseTrait;

    public function addUser()
    {
        $fullname = $this->request->getVar('fullname');
        $email    = $this->request->getVar('email');
        $phone    = $this->request->getVar('phone');
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $newUserData = [
            'fullname' => $fullname,
            'email'    => $email,
            'phone'    => $phone,
            'username' => $username,
            'password' => $password
        ];

        $usersModel = new UsersModel();

        if (!$usersModel->insert($newUserData)) {
            $errors = $usersModel->errors();
            $response = [
                'success' => false,
                'message' => 'Failed to add new user',
                'error' => $errors
            ];
            return $this->respond($response, 400);
        } else {
            $response = [
                'success' => true,
                'message' => 'New user added successfully',
                'data' => [
                    'fullname' => $fullname,
                    'email'    => $email,
                    'phone'    => $phone,
                    'role'     => 'operator',
                    'username' => $username,
                ],
            ];
            return $this->respond($response, 200);
        }
    }
}
