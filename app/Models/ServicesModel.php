<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['category', 'name', 'price', 'description', 'thumbnail', 'point', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'thumbnail' => [
            'label' => 'Service Thumbnail',
            'rules' => [
                'uploaded[thumbnail]', 'is_image[thumbnail]', 'mime_in[thumbnail,image/jpg,image/jpeg,image/gif,image/png,image/webp]', 'max_size[thumbnail,500]', 'max_dims[thumbnail,500,500]'
            ]
        ],
        'category' => [
            'label' => 'Service category',
            'rules' => [
                'numeric', 'required'
            ]
        ],
        'name' => [
            'label' => 'Service name',
            'rules' => [
                'string', 'required'
            ]
        ],
        'price' => [
            'label' => 'Service price',
            'rules' => [
                'numeric', 'required'
            ]
        ],
        'description' => [
            'label' => 'Service description',
            'rules' => [
                'string'
            ]
        ],
        'point' => [
            'label' => 'Service point',
            'rules' => [
                'numeric', 'required'
            ]
        ],
        'status' => [
            'label' => 'Service status',
            'rules' => [
                'numeric', 'required'
            ]
        ]
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
