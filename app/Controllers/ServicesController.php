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
        $image = \Config\Services::image();

        $newServiceData['category'] = $this->request->getVar('category');
        $newServiceData['name'] = $this->request->getVar('name');
        $newServiceData['price'] = $this->request->getVar('price');
        $newServiceData['description'] = $this->request->getVar('description');
        $newServiceData['point'] = $this->request->getVar('point');
        $newServiceData['status'] = $this->request->getVar('status');

        $servicesModel = new ServicesModel();

        $validationRule = [
            'thumbnail' => [
                'label' => 'Service thumbnail',
                'rules' => [
                    'uploaded[thumbnail]',
                    'is_image[thumbnail]',
                    'mime_in[thumbnail,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[thumbnail,500]',
                    'max_dims[thumbnail,2000,2000]'
                ]
            ]
        ];

        if (!$this->validate($validationRule)) {
            $errors = $this->validator->getErrors();
            $response = [
                'success' => false,
                'message' => 'Thumbnail must be in (.jpg, .jpeg, .gif, .png, .webp) format and 500kb or less in size and maximum 2000 x 2000 pixels in square ratio',
                'errors' => $errors
            ];
            return $this->respond($response, 400);
        }
        $img = $this->request->getFile('thumbnail');
        $randomName = $img->getRandomName();

        if ($img->move(WRITEPATH . 'thumbnails', $randomName)) {

            $image->withFile(WRITEPATH . 'thumbnails/' . $randomName)
                ->fit(1500, 1500, 'center')
                ->save(WRITEPATH . 'thumbnails/' . 'processed_' . $randomName);
            unlink(WRITEPATH . 'thumbnails/' . $randomName);
            $newServiceData['thumbnail'] = 'processed_' . $randomName;
            if (!$servicesModel->insert($newServiceData)) {
                unlink(WRITEPATH . 'thumbnails/' . $randomName);
                $errors = $servicesModel->errors();
                $response = [
                    'success' => false,
                    'message' => 'Parameter did not pass the required validation',
                    'errors' => $errors
                ];
                return $this->respond($response, 400);
            } else {
                $response = [
                    'success' => true,
                    'message' => 'New service has been added successfully',
                    'data' => $newServiceData
                ];
                return $this->respond($response, 200);
            }
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
