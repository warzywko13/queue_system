<?php

namespace App\Controllers;

use App\Models\CoasterModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class CoasterController extends ResourceController
{
    protected CoasterModel $coasterModel;

    public function __construct()
    {
        $this->coasterModel = new CoasterModel();
    }

    /**
     * Add new coaster
     * @return ResponseInterface
     */
    public function add(): ResponseInterface
    {
        // Get data
        $data = $this->request->getJSON(true);
        
        // Validation
        $rules = self::getRules('add');
        if(!$this->validateData($data, $rules)) {
            $this->logger->warning(lang('coaster_warning_add'), $this->validator->getErrors());

            return $this->respond([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ], 400);
        }

        // Add coaster
        try {
            $coaster_id = $this->coasterModel->add([
                'staff_count' => $data['staff_count'],
                'customer_count' => $data['customer_count'],
                'track_length' => $data['track_length'],
                'hours_from' => $data['hours_from'],
                'hours_to' => $data['hours_to']
            ]);
        } catch(\Throwable $e) {
            $this->logger->error(lang('Coaster.coaster_error_add') . ': ' . $e->getMessage());

            return $this->respond([
                'status' => 'error',
                'message' => lang('Coaster.coaster_error_add')
            ], 500);
        }

        // Response
        $this->logger->info(lang('Coaster.coaster_added', ['coaster_id' => $coaster_id]));

        return $this->respond([
            'status' => 'ok',
            'message' => lang('Coaster.coaster_added', ['coaster_id' => $coaster_id])
        ], 200);
    }

    /**
     * Update coaster by coaster_id
     * @param int $coaster_id
     * @return ResponseInterface
     */
    public function upgrade(int $coaster_id): ResponseInterface
    {
        // Get data
        $data = $this->request->getJSON(true);

        $rules = self::getRules('upgrade');
        if(!$this->validateData($data, $rules)) {
            $this->logger->warning(
                lang('Coaster.coaster_warning_update', ['coaster_id' => $coaster_id]), 
                $this->validator->getErrors()
            );

            return $this->respond([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ], 400);
        }

        try {
            // Check coaster
            if(!$this->coasterModel->get($coaster_id)) {
                $this->logger->warning(lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id]));
            
                return $this->respond([
                    'status' => 'error',
                    'message' => lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id])
                ], 404);
            }

            // Update
            $this->coasterModel->update($coaster_id, [
                'staff_count' => $data['staff_count'],
                'customer_count' => $data['customer_count'],
                'hours_from' => $data['hours_from'],
                'hours_to' => $data['hours_to']
            ]);
        } catch(\Throwable $e) {
            $this->logger->error(lang('Coaster.coaster_error_update') . ': ' . $e->getMessage());
            
            return $this->respond([
                'status' => 'error',
                'message' => lang('Coaster.coaster_error_update')
            ], 500);
        }
        
        // Response
        $this->logger->info(lang('Coaster.coaster_updated', ['coaster_id' => $coaster_id]));

        return $this->respond([
            'status' => 'ok',
            'message' => lang('Coaster.coaster_updated', ['coaster_id' => $coaster_id])
        ], 200); 
    }

    private static function getRules(string $action): array
    {
        $rules = [
            'staff_count' => 'required|integer|greater_than[0]',
            'customer_count' => 'required|integer|greater_than[0]',
            'hours_from' => 'required|hours_format',
            'hours_to' => 'required|hours_format|hours_greater[hours_from]'
        ];

        if($action === 'add') {
            $rules['track_length'] = 'required|integer|greater_than[0]';
        }

        return $rules;
    }
    
}