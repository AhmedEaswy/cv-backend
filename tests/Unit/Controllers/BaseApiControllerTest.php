<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Tests\TestCase;

class BaseApiControllerTest extends TestCase
{
    public function test_success_response_has_correct_structure(): void
    {
        $controller = new class extends BaseApiController {
            public function testSuccess()
            {
                return $this->successResponse(['key' => 'value'], 'Operation successful', 201);
            }
        };

        $response = $controller->testSuccess();
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('Operation successful', $data['message']);
        $this->assertEquals(['key' => 'value'], $data['result']);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_success_response_defaults(): void
    {
        $controller = new class extends BaseApiController {
            public function testSuccess()
            {
                return $this->successResponse();
            }
        };

        $response = $controller->testSuccess();
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('', $data['message']);
        $this->assertNull($data['result']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_error_response_has_correct_structure(): void
    {
        $controller = new class extends BaseApiController {
            public function testError()
            {
                return $this->errorResponse('Something went wrong', 422, ['field' => 'Error message']);
            }
        };

        $response = $controller->testError();
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Something went wrong', $data['message']);
        $this->assertEquals(422, $data['code']);
        $this->assertEquals(['field' => 'Error message'], $data['errors']);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_error_response_defaults(): void
    {
        $controller = new class extends BaseApiController {
            public function testError()
            {
                return $this->errorResponse();
            }
        };

        $response = $controller->testError();
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
        $this->assertEquals('', $data['message']);
        $this->assertEquals(400, $data['code']);
        $this->assertNull($data['errors']);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
