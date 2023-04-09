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

    public function getUsers()
    {
        $limit = $this->request->getVar('limit');
        $page = $this->request->getVar('page');

        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $builder->where('deleted_at', null);
        $builder->select(['id', 'fullname', 'email', 'phone', 'username', 'role']);




        if (is_null($limit) && is_null($page)) {
            $users = $builder->get()->getResult();
            $response = [
                'success' => true,
                'message' => 'Successfully get all users data',
                'data' => $users
            ];
            return $this->respond($response, 200);
        } else {
            $totalData = $builder->countAllResults(false);
            $totalPages = ceil($totalData / $limit);
            if ($page > $totalPages) {
                $response = [
                    'success' => false,
                    'message' => 'Requested page is not available',
                ];
                return $this->respond($response, 400);
            } else {
                $offset = ($page - 1) * $limit;
                $users = $builder->get($limit, $offset)->getResult();
                $response = [
                    'success' => true,
                    'message' => 'Successfully get ' . $limit . ' users data for page ' . $page,
                    'data' => [
                        'limit' => $limit,
                        'totalData' => $totalData,
                        'page' => $page,
                        'totalPages' => $totalPages,
                        'users' => $users
                    ]
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function getUserById()
    {
        $id = $this->request->getVar('id');

        if (!is_null($id) && !empty($id)) {
            $db = \Config\Database::connect();
            $builder = $db->table('users');
            $builder->where('deleted_at', null);
            $builder->where('id', $id);
            $builder->select(['id', 'fullname', 'email', 'phone', 'username', 'role']);

            if ($builder->countAllResults(false) > 0) {
                $user = $builder->get()->getResult();
                $response = [
                    'success' => true,
                    'message' => 'Successfully get user data with id ' . $id,
                    'data' => $user
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'success' => true,
                    'message' => 'No user found with id ' . $id,
                ];
                return $this->respond($response, 404);
            }
        } else {
            $response = [
                'success' => true,
                'message' => 'Required parameter not provided : id'
            ];
            return $this->respond($response, 400);
        }
    }
}
