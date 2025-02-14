<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JsonCompareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    protected function tearDown(): void
    {
        Storage::delete(['payload_1.json', 'payload_2.json']);
        parent::tearDown();
    }

    public function test_can_store_valid_json_payload()
    {
        $response = $this->postJson('/api/store', [
            'number' => 1,
            'payload_to_compare' => json_encode(['name' => 'test'])
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Payload 1 stored successfully']);
        
        Storage::assertExists('payload_1.json');
    }

    public function test_cannot_store_invalid_json_payload()
    {
        $response = $this->postJson('/api/store', [
            'number' => 1,
            'payload_to_compare' => 'invalid-json'
        ]);

        $response->assertStatus(400);
    }

    public function test_cannot_store_payload_with_invalid_number()
    {
        $response = $this->postJson('/api/store', [
            'number' => 3,
            'payload_to_compare' => json_encode(['name' => 'test'])
        ]);

        $response->assertStatus(400);
    }

    public function test_can_compare_identical_json_payloads()
    {
        $payload = ['name' => 'test', 'age' => 25];

        // Store both payloads
        $this->postJson('/api/store', [
            'number' => 1,
            'payload_to_compare' => json_encode($payload)
        ]);

        $this->postJson('/api/store', [
            'number' => 2,
            'payload_to_compare' => json_encode($payload)
        ]);

        $response = $this->getJson('/api/compare');

        $response->assertStatus(200)
                ->assertJson([
                    'differences' => [],
                    'message' => 'Comparison completed and payloads deleted successfully'
                ]);
    }

    public function test_can_detect_differences_in_json_payloads()
    {
        $payload1 = ['name' => 'test', 'age' => 25];
        $payload2 = ['name' => 'test', 'age' => 30];

        // Store both payloads
        $this->postJson('/api/store', [
            'number' => 1,
            'payload_to_compare' => json_encode($payload1)
        ]);

        $this->postJson('/api/store', [
            'number' => 2,
            'payload_to_compare' => json_encode($payload2)
        ]);

        $response = $this->getJson('/api/compare');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'differences' => [
                        '*' => ['path', 'type', 'value1', 'value2']
                    ],
                    'message'
                ])
                ->assertJson([
                    'differences' => [
                        [
                            'path' => 'age',
                            'type' => 'value_mismatch',
                            'value1' => 25,
                            'value2' => 30
                        ]
                    ]
                ]);
    }

    public function test_can_detect_missing_fields()
    {
        $payload1 = ['name' => 'test', 'age' => 25];
        $payload2 = ['name' => 'test'];

        // Store both payloads
        $this->postJson('/api/store', [
            'number' => 1,
            'payload_to_compare' => json_encode($payload1)
        ]);

        $this->postJson('/api/store', [
            'number' => 2,
            'payload_to_compare' => json_encode($payload2)
        ]);

        $response = $this->getJson('/api/compare');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'differences' => [
                        '*' => ['path', 'type', 'value']
                    ],
                    'message'
                ])
                ->assertJson([
                    'differences' => [
                        [
                            'path' => 'age',
                            'type' => 'missing_in_second',
                            'value' => 25
                        ]
                    ]
                ]);
    }

    public function test_cannot_compare_without_both_payloads()
    {
        $response = $this->getJson('/api/compare');

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Both payloads must be stored before comparison'
                ]);
    }
} 