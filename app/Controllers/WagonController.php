<?php

namespace App\Controllers;

use App\Models\CoasterModel;
use App\Models\WagonModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class WagonController extends ResourceController
{
    protected WagonModel $wagonModel;
    protected CoasterModel $coasterModel;

    public function __construct()
    {
        $this->wagonModel = new WagonModel();
        $this->coasterModel = new CoasterModel();
    }

    /**
     * Add wagon to coaster
     * @param int $coaster_id
     * @return ResponseInterface
     */
    public function add(int $coaster_id): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        // Validate data
        $rules = self::getRules();
        if(!$this->validateData($data, $rules)) {
            $this->logger->warning(lang('Wagon.wagon_warning_add'), $this->validator->getErrors());

            return $this->respond([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ], 400);
        }

        try {
            // Check coaster exists
            if(!$this->coasterModel->exists($coaster_id)) {
                $this->logger->warning(lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id]));

                return $this->respond([
                    'status' => 'error',
                    'message' => lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id])
                ], 404);
            }

            // All wagons must have the same parameters
            $coaster = $this->wagonModel->getByCoasterId($coaster_id);
            if(
                !empty($coaster)
                && ($coaster['seat_count'] != $data['seat_count'] || $coaster['speed'] != $data['speed'])
            ) {
                $this->logger->warning(lang('Wagon.parameters_different'));

                return $this->respond([
                    'status' => 'error',
                    'message' => lang('Wagon.parameters_different')
                ], 400);
            }

            $wagon_id = $this->wagonModel->add([
                'seat_count' => $data['seat_count'],
                'speed' => $data['speed']
            ], $coaster_id);    
        } catch(\Throwable $e) {
            $this->logger->error(lang('Wagon.wagon_error_add') . ': ' . $e->getMessage());

            return $this->respond([
                'status' => 'error',
                'message' => lang('Wagon.wagon_error_add')
            ], 500);
        }

        // Response
        $this->logger->info(lang('Wagon.wagon_added', ['wagon_id' => $wagon_id]));
        return $this->respond([
            'status' => 'ok',
            'message' => lang('Wagon.wagon_added', ['wagon_id' => $wagon_id])
        ], 200);
    }

    /**
     * Remove wagon
     * @param int $coaster_id
     * @param int $wagon_id
     * @return ResponseInterface
     */
    public function remove(int $coaster_id, int $wagon_id): ResponseInterface
    {
        try {
            // Get coaster and wagon
            $coasterExists = $this->coasterModel->exists($coaster_id);
            $wagonExists = $this->wagonModel->exists($wagon_id, $coaster_id);

            // Validate
            if(!$coasterExists) {
                $this->logger->warning(lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id]));
    
                return $this->respond([
                    'status' => 'error',
                    'message' => lang('Coaster.coaster_not_exists', ['coaster_id' => $coaster_id])
                ], 404);
            } else if(!$wagonExists) {
                $this->logger->warning(lang('Wagon.wagon_not_exists', ['wagon_id' => $wagon_id]));
    
                return $this->respond([
                    'status' => 'error',
                    'message' => lang('Wagon.wagon_not_exists', ['wagon_id' => $wagon_id])
                ], 404);
            }

            // Remove
            $this->wagonModel->delete($wagon_id, $coaster_id);
        } catch(\Throwable $e) {
            $this->logger->error(lang('Wagon.wagon_error_remove') . ': ' . $e->getMessage());

            return $this->respond([
                    'status' => 'error',
                    'message' => lang('Wagon.wagon_error_remove')
                ], 500);
        }

        // Response
        $this->logger->info(lang('Wagon.wagon_removed', ['wagon_id' => $wagon_id]));
        return $this->respond([
            'status' => 'ok',
            'message' => lang('Wagon.wagon_removed', ['wagon_id' => $wagon_id])
        ], 200);
    }

    private static function getRules(): array
    {
        $rules = [
            'seat_count' => 'required|integer|greater_than[0]',
            'speed' => 'required|decimal|greater_than[0]'
        ];

        return $rules;
    }
}