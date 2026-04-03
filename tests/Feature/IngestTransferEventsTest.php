<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestTransferEventsTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): array
    {
        return array_merge([
            'event_id' => uniqid('evt_'),
            'station_id' => 'S1',
            'amount' => 100.50,
            'status' => 'approved',
            'created_at' => '2026-02-19T10:00:00Z',
        ], $overrides);
    }

    public function test_ingest_valid_events_returns_201_with_correct_counts(): void
    {
        $events = [
            $this->makeEvent(['event_id' => 'E1']),
            $this->makeEvent(['event_id' => 'E2']),
            $this->makeEvent(['event_id' => 'E3']),
        ];

        $response = $this->postJson('/api/transfers', ['events' => $events]);

        $response->assertStatus(201)
            ->assertJson([
                'inserted' => 3,
                'duplicates' => 0,
            ]);

        $this->assertDatabaseCount('transfer_events', 3);
    }

    public function test_duplicate_events_are_not_inserted_twice(): void
    {
        $events = [
            $this->makeEvent(['event_id' => 'E1']),
            $this->makeEvent(['event_id' => 'E2']),
        ];

        $this->postJson('/api/transfers', ['events' => $events]);

        $response = $this->postJson('/api/transfers', ['events' => $events]);

        $response->assertStatus(201)
            ->assertJson([
                'inserted' => 0,
                'duplicates' => 2,
            ]);

        $this->assertDatabaseCount('transfer_events', 2);
    }

    public function test_mixed_new_and_duplicate_events_returns_correct_split(): void
    {
        $this->postJson('/api/transfers', [
            'events' => [
                $this->makeEvent(['event_id' => 'E1']),
            ],
        ]);

        $response = $this->postJson('/api/transfers', [
            'events' => [
                $this->makeEvent(['event_id' => 'E1']),
                $this->makeEvent(['event_id' => 'E2']),
                $this->makeEvent(['event_id' => 'E3']),
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'inserted' => 2,
                'duplicates' => 1,
            ]);

        $this->assertDatabaseCount('transfer_events', 3);
    }

    public function test_invalid_payload_returns_422(): void
    {
        $response = $this->postJson('/api/transfers', [
            'events' => [
                [
                    'event_id' => '',
                    'station_id' => 'S1',
                    'amount' => -5,
                    'status' => 'approved',
                    'created_at' => 'not-a-date',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'events.0.event_id',
                'events.0.amount',
                'events.0.created_at',
            ]);
    }

    public function test_empty_events_array_returns_422(): void
    {
        $response = $this->postJson('/api/transfers', ['events' => []]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['events']);
    }
}
