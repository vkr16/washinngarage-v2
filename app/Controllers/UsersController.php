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

            $validation = service('validation');
            $validation->check($id, 'numeric');
            if (!$validation->getErrors()) {
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
                        'success' => false,
                        'message' => 'No user found with id ' . $id,
                    ];
                    return $this->respond($response, 404);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Invalid user id format provided',
                ];
                return $this->respond($response, 400);
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Required parameter not provided : id'
            ];
            return $this->respond($response, 400);
        }
    }

    public function updateUser()
    {
        $id = $this->request->getRawInputVar('id');

        $fullname = $this->request->getRawInputVar('fullname');
        $email    = $this->request->getRawInputVar('email');
        $phone    = $this->request->getRawInputVar('phone');
        $username = $this->request->getRawInputVar('username');

        if (!is_null($id)) {
            $validation = service('validation');
            $validation->check($id, 'numeric');
            if ($validation->getErrors()) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid user id format provided',
                ];
                return $this->respond($response, 400);
            } else {
                $usersModel = new UsersModel();
                if ($oldUserData = $usersModel->find($id)) {
                    $oldUserData['fullname'] != $fullname ? $newUserData['fullname'] = $fullname : '';
                    $oldUserData['email'] != $email ? $newUserData['email'] = $email : '';
                    $oldUserData['phone'] != $phone ? $newUserData['phone'] = $phone : '';
                    $oldUserData['username'] != $username ? $newUserData['username'] = $username : '';

                    if (isset($newUserData)) {
                        if ($usersModel->where('id', $id)->set($newUserData)->update()) {
                            $response = [
                                'success' => true,
                                'message' => 'Successfully updated',
                                'data' => [
                                    'userId' => $id,
                                    'userData' => $newUserData
                                ]
                            ];
                            return $this->respond($response, 200);
                        } else {
                            $errors = $usersModel->errors();
                            $response = [
                                'success' => false,
                                'message' => 'Failed to update user data',
                                'error' => $errors
                            ];
                            return $this->respond($response, 400);
                        }
                    } else {
                        $response = [
                            'success' => true,
                            'message' => 'Nothing changed'
                        ];
                        return $this->respond($response, 200);
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'There is no user with id : ' . $id
                    ];
                    return $this->respond($response, 404);
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Required parameter not provided : id'
            ];
            return $this->respond($response, 400);
        }
    }

    public function deleteUser()
    {
        $id = $this->request->getRawInputVar('id');

        if (!is_null($id) && !empty($id)) {
            $usersModel = new UsersModel();
            if ($user = $usersModel->find($id)) {
                helper('jwt');
                $headerAuthentication = $this->request->getServer("HTTP_AUTHORIZATION");
                $decodedToken = decodeJWT(getJWT($headerAuthentication));
                if ($user['email'] == $decodedToken->email) {
                    $response = [
                        'success' => false,
                        'message' => 'Not allowed, can not delete currently active user'
                    ];
                    return $this->respond($response, 405);
                } else {
                    if ($usersModel->delete($id)) {
                        $response = [
                            'success' => true,
                            'message' => 'User deleted successfully',
                            'data' => [
                                'deletedUser' => [
                                    'id' => $id,
                                    'fullname' => $user['fullname'],
                                    'email' => $user['email'],
                                    'phone' => $user['phone'],
                                    'username' => $user['username'],
                                ]
                            ]
                        ];
                        return $this->respond($response, 200);
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Failed to delete user',
                            'errors' => $usersModel->errors()
                        ];
                        return $this->respond($response, 400);
                    }
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to delete, user not found'
                ];
                return $this->respond($response, 404);
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Required parameter not provided : id'
            ];
            return $this->respond($response, 400);
        }
    }

    public function promoteUser()
    {
        $id = $this->request->getRawInputVar('id');

        if (!is_null($id)) {
            $validation = service('validation');
            $validation->check($id, 'numeric');
            if ($validation->getErrors()) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid user id format provided',
                ];
                return $this->respond($response, 400);
            } else {
                $usersModel = new UsersModel();
                if ($user = $usersModel->find($id)) {
                    helper('jwt');
                    $headerAuthentication = $this->request->getServer("HTTP_AUTHORIZATION");
                    $decodedToken = decodeJWT(getJWT($headerAuthentication));

                    if ($decodedToken->email == $user['email']) {
                        $response = [
                            'success' => false,
                            'message' => 'You are already an administrator'
                        ];
                        return $this->respond($response, 400);
                    } else {
                        if ($user['role'] == 'staff') {
                            if ($usersModel->where('id', $id)->set(['role' => 'admin'])->update()) {
                                $response = [
                                    'success' => true,
                                    'message' => $user['fullname'] . ' has been promoted as an administrator',
                                ];
                                return $this->respond($response, 200);
                            } else {
                                $response = [
                                    'success' => false,
                                    'message' => 'Failed to promote user as administrator',
                                    'error' => $usersModel->errors()
                                ];
                                return $this->respond($response, 400);
                            }
                        } else {
                            $response = [
                                'success' => false,
                                'message' => $user['fullname'] . ' is already an administrator'
                            ];
                            return $this->respond($response, 400);
                        }
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to promote, user not found'
                    ];
                    return $this->respond($response, 404);
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Required parameter not provided : id'
            ];
            return $this->respond($response, 400);
        }
    }
}
