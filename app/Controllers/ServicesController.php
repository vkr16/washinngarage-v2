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

        $servicesModel = new ServicesModel();

        $validationResult = $this->thumbnailValidation();
        if ($validationResult === true) {
            $preprocessedThumbnail = $this->thumbnailPreprocess();
            if ($preprocessedThumbnail !== false) {
                $newServiceData['thumbnail'] = $preprocessedThumbnail;
                if ($servicesModel->insert($newServiceData)) {
                    $response = [
                        'success' => true,
                        'message' => 'New service has been added successfully',
                        'data' => $newServiceData
                    ];
                    return $this->respond($response, 200);
                } else {
                    unlink(WRITEPATH . 'thumbnails/' . $preprocessedThumbnail);
                    $response = [
                        'success' => false,
                        'message' => 'There might be some errors with the database server, please try again later',
                        'errors' => 'Failed to add new service to database'
                    ];
                    return $this->respond($response, 500);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'There was an error while processing your thumbnail file, please try again!',
                    'errors' => 'Fail to preprocess thumbnail image'
                ];
                return $this->respond($response, 400);
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Parameter did not pass the required validation',
                'errors' => $validationResult
            ];
            return $this->respond($response, 400);
        }
    }

    public function thumbnailValidation()
    {
        $validationRule = [
            'thumbnail' => [
                'label' => 'Service thumbnail',
                'rules' => [
                    'uploaded[thumbnail]',
                    'is_image[thumbnail]',
                    'mime_in[thumbnail,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                ]
            ]
        ];

        if (!$this->validate($validationRule)) {
            $validationErrors = $this->validator->getErrors();
            return $validationErrors;
        } else {
            return true;
        }
    }

    public function thumbnailPreprocess()
    {
        $image = \Config\Services::image();

        $img = $this->request->getFile('thumbnail');
        $randomName = $img->getRandomName();

        if ($img->move(WRITEPATH . 'thumbnails', $randomName)) {
            $processedThumbnailName = bin2hex(openssl_random_pseudo_bytes(16)) . '.jpeg';
            $image->withFile(WRITEPATH . 'thumbnails/' . $randomName)
                ->fit(500, 500, 'center')
                ->convert(IMAGETYPE_JPEG)
                ->save(WRITEPATH . 'thumbnails/' . 'processed_' . $processedThumbnailName);
            unlink(WRITEPATH . 'thumbnails/' . $randomName);
            $thumbnailFileName = 'processed_' . $processedThumbnailName;
            return $thumbnailFileName;
        } else {
            return false;
        }
    }
}
