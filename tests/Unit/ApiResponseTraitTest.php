<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Traits\ApiResponseTrait;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    private object $classWithTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classWithTrait = new class
        {
            use ApiResponseTrait;
        };
    }

    public function test_success_response_returns_standard_structure(): void
    {
        $response = $this->classWithTrait->successResponse(['id' => 1], 'User retrieved successfully', 200);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => ['id' => 1],
            'errors' => null,
        ], $response->getData(true));
    }

    public function test_error_response_returns_standard_structure(): void
    {
        $response = $this->classWithTrait->errorResponse('Validation failed', 422, ['email' => ['Invalid email']]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Validation failed',
            'data' => null,
            'errors' => ['email' => ['Invalid email']],
        ], $response->getData(true));
    }
}
