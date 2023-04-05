<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['fullname', 'email', 'phone', 'username', 'role', 'password'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'fullname' => 'required|min_length[3]|alpha_space',
        'email' => 'required|valid_email|is_unique[users.email]',
        'phone' => 'required|numeric|min_length[7]',
        'username' => 'required|alpha_numeric|min_length[3]|max_length[10]|is_unique[users.username]',
        'password' => 'required|min_length[5]'
    ];
    protected $validationMessages   = [
        'fullname' => [
            'required' => 'Full name can not be empty',
            'min_length' => 'Full name field must be at least 3 characters long.',
            'alpha_space' => 'Full name should only contain alphabetic characters and spaces.',
        ],
        'email' => [
            'required' => 'Email can not be empty',
            'valid_email' => 'Email must be a valid email address',
            'is_unique' => 'Email already associated with another user',
        ],
        'phone' => [
            'required' => 'Phone number can not be empty',
            'numeric' => 'Phone number should only contain numeric characters',
            'min_length' => 'Phone number must be at least 7 characters long',
        ],
        'username' => [
            'required' => 'Username can not be empty',
            'alpha_numeric' => 'Username should only contain alpha-numeric characters',
            'min_length' => 'Username must be at least 3 characters long',
            'max_length' => 'Username must be at most 10 characters long',
            'is_unique' => 'Username already associated with another user',
        ],
        'password' => [
            'required' => 'Password can not be empty',
            'min_length' => 'Password must be at least 5 characters long'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    function hashPassword(array $data)
    {
        $password = $data['data']['password'];

        $data['data']['password'] = password_hash($password, PASSWORD_DEFAULT);
        return $data;
    }
}
