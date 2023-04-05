<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;


class AuthController extends BaseController
{
    use ResponseTrait;

    public function auth()
    {
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $usersModel = new UsersModel();
        $validation = service('validation');
        $validation->setRules(
            [
                'username' => 'required|alpha_numeric|min_length[3]|max_length[10]',
                'password' => 'required|min_length[5]',
            ],
            [
                'username' => [
                    'required' => 'Username can not be empty',
                    'alpha_numeric' => 'Username should only contain alpha-numeric characters',
                    'min_length' => 'Username must be at least 3 characters long',
                    'max_length' => 'Username must be at most 10 characters long'
                ],
                'password' => [
                    'required' => 'Password can not be empty',
                    'min_length' => 'Password must be at least 5 characters long'
                ]
            ]
        );

        if ($validation->run(['username' => $username, 'password' => $password])) {
            if ($user = $usersModel->where('username', $username)->find()) {
                if (password_verify($password, $user[0]['password'])) {
                    $email = $user[0]['email'];
                    helper('jwt');
                    $jwt = encodeJWT($email);

                    $response = [
                        'success' => true,
                        'message' => 'Authentication successful, token has been generated',
                        'data' => [
                            'jwt' => $jwt,
                        ]
                    ];
                    return $this->respond($response, 200);
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Authentication failed, invalid password',
                    ];
                    return $this->respond($response, 400);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'User not found',
                ];

                return $this->respond($response, 404);
            }
        } else {
            $errors = $validation->getErrors();
            $response = [
                'success' => false,
                'message' => 'Authentication failed, invalid username or password',
                'error' => $errors
            ];
            return $this->respond($response, 400);
        }
    }
}
