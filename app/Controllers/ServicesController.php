<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ServicesModel;


class ServicesController extends BaseController
{
    use ResponseTrait;

    public function addService()
    {

        $newServiceData['category'] = $this->request->getVar('category');
        $newServiceData['name'] = $this->request->getVar('name');
        $newServiceData['price'] = $this->request->getVar('price');
        $newServiceData['description'] = $this->request->getVar('description');
        $newServiceData['point'] = $this->request->getVar('point');
        $newServiceData['status'] = $this->request->getVar('status');

        // $validationRules = [
        //     'thumbnail' => [
        //         'label' => 'Service Thumbnail',
        //         'rules' => [
        //             'uploaded[thumbnail]', 'is_image[thumbnail]', 'mime_in[thumbnail,image/jpg,image/jpeg,image/gif,image/png,image/webp]', 'max_size[thumbnail,500]', 'max_dims[thumbnail,500,500]'
        //         ]
        //     ],
        //     'category' => [
        //         'label' => 'Service category',
        //         'rules' => [
        //             'numeric', 'required'
        //         ]
        //     ],
        //     'name' => [
        //         'label' => 'Service name',
        //         'rules' => [
        //             'string', 'required'
        //         ]
        //     ],
        //     'price' => [
        //         'label' => 'Service price',
        //         'rules' => [
        //             'numeric', 'required'
        //         ]
        //     ],
        //     'description' => [
        //         'label' => 'Service description',
        //         'rules' => [
        //             'string'
        //         ]
        //     ],
        //     'point' => [
        //         'label' => 'Service point',
        //         'rules' => [
        //             'numeric', 'required'
        //         ]
        //     ],
        //     'status' => [
        //         'label' => 'Service status',
        //         'rules' => [
        //             'numeric', 'required'
        //         ]
        //     ]
        // ];
        // if (!$this->validate($validationRules)) {
        //     $validationError = $this->validator->getErrors();

        //     $response = [
        //         'success' => false,
        //         'message' => 'Request parameter validation failed',
        //         'errors' => $validationError
        //     ];
        //     return $this->respond($response, 400);
        // }
        $servicesModel = new ServicesModel();
        if (!$servicesModel->insert($newServiceData)) {
            $errors = $servicesModel->errors();
            $response = [
                'success' => false,
                'message' => 'Parameter did not pass the required validation',
                'errors' => $errors
            ];
            return $this->respond($response, 400);
        }

        $img = $this->request->getFile('thumbnail');
        $randomName = $img->getRandomName();

        if ($img->move(WRITEPATH . 'thumbnails', $randomName)) {
            $response = [
                'success' => true,
                'message' => 'File uploaded successfully'
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to upload',
                'errors' => 'Image upload error'
            ];
            return $this->respond($response, 400);
        }
    }
}
