<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\CoasterModel;
use App\Models\WagonModel;

class WagonControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $coasterModel;
    protected $wagonModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coasterModel = new CoasterModel();
        $this->wagonModel = new WagonModel();
    }

    /**
     * Helper function to send a JSON POST request
     */
    protected function postJson(string $uri, array $data)
    {
        return $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('post', $uri);
    }

    public function testAddWagonSuccess()
    {
        // Create a test coaster
        $coasterId = $this->coasterModel->add([
            'staff_count' => 10,
            'customer_count' => 100,
            'track_length' => 500,
            'hours_from' => '8:00',
            'hours_to' => '18:00'
        ]);

        $data = [
            'seat_count' => 20,
            'speed' => 80.5
        ];

        $result = $this->postJson("api/coasters/{$coasterId}/wagons", $data);
        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'ok']);
    }

    public function testAddWagonValidationFail()
    {
        // Create a test coaster
        $coasterId = $this->coasterModel->add([
            'staff_count' => 10,
            'customer_count' => 100,
            'track_length' => 500,
            'hours_from' => '8:00',
            'hours_to' => '18:00'
        ]);

        $data = [
            'seat_count' => -5,  // Invalid seat count
            'speed' => 0         // Invalid speed
        ];

        $result = $this->postJson("api/coasters/{$coasterId}/wagons", $data);
        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testAddWagonToNonExistingCoaster()
    {
        $data = [
            'seat_count' => 20,
            'speed' => 80.5
        ];

        $result = $this->postJson("api/coasters/999999/wagons", $data);
        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testRemoveWagonSuccess()
    {
        // Create a test coaster
        $coasterId = $this->coasterModel->add([
            'staff_count' => 10,
            'customer_count' => 100,
            'track_length' => 500,
            'hours_from' => '8:00',
            'hours_to' => '18:00'
        ]);

        // Create a test wagon
        $wagonId = $this->wagonModel->add([
            'seat_count' => 20,
            'speed' => 80.5,
        ], $coasterId);

        $result = $this->call('delete', "api/coasters/{$coasterId}/wagons/{$wagonId}");
        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'ok']);
    }

    public function testRemoveWagonNotFound()
    {
        // Create a test coaster
        $coasterId = $this->coasterModel->add([
            'staff_count' => 10,
            'customer_count' => 100,
            'track_length' => 500,
            'hours_from' => '8:00',
            'hours_to' => '18:00'
        ]);

        $result = $this->call('delete', "api/coasters/{$coasterId}/wagons/999999");
        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testRemoveWagonFromNonExistingCoaster()
    {
        $result = $this->call('delete', "api/coasters/999999/wagons/1");
        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }
}
