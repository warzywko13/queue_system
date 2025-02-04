<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\CoasterModel;

class CoasterControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $coasterModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coasterModel = new CoasterModel();
    }

    public function testAddCoasterSuccess()
    {
        $data = [
            'staff_count' => 10,
            'customer_count' => 100,
            'track_length' => 500,
            'hours_from' => '8:00',
            'hours_to' => '18:00'
        ];

        $result = $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('post', 'api/coasters');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'ok']);
    }

    public function testAddCoasterValidationFail()
    {
        $data = [
            'staff_count' => -5,  // Invalid
            'customer_count' => 0, // Invalid
            'track_length' => 500,
            'hours_from' => '08:00',
            'hours_to' => '18:00'
        ];

        $result = $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('post', 'api/coasters');

        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testUpgradeCoasterSuccess()
    {
        $coasterId = $this->coasterModel->add([
            'staff_count' => 8,
            'customer_count' => 80,
            'track_length' => 400,
            'hours_from' => '9:00',
            'hours_to' => '17:00'
        ]);

        $data = [
            'staff_count' => 12,
            'customer_count' => 120,
            'hours_from' => '10:00',
            'hours_to' => '19:00'
        ];

        $result = $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('put', "api/coasters/{$coasterId}");

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'ok']);
    }

    public function testUpgradeCoasterNotFound()
    {
        $data = [
            'staff_count' => 12,
            'customer_count' => 120,
            'hours_from' => '10:00',
            'hours_to' => '19:00'
        ];

        $result = $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('put', 'api/coasters/999999');

        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testUpgradeCoasterValidationFail()
    {
        $coasterId = $this->coasterModel->add([
            'staff_count' => 8,
            'customer_count' => 80,
            'track_length' => 400,
            'hours_from' => '9:00',
            'hours_to' => '17:00'
        ]);

        $data = [
            'staff_count' => 0,  // Invalid
            'customer_count' => -10, // Invalid
            'hours_from' => '20:00',
            'hours_to' => '19:00' // Invalid hours
        ];

        $result = $this->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->call('put', "api/coasters/{$coasterId}");

        $result->assertStatus(400);
        $result->assertJSONFragment(['status' => 'error']);
    }
}
