<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): array
    {
        return array_merge([
            'event_id' => uniqid('evt_'),
            'station_id' => 'S1',
            'amount' => 100.00,
            'status' => 'approved',
            'created_at' => '2026-02-19T10:00:00Z',
        ], $overrides);
    }

    private function ingestEvents(array $events): void
    {
        $this->postJson('/api/transfers', ['events' => $events])
            ->assertStatus(201);
    }

    public function test_summary_returns_correct_totals_for_approved_events(): void
    {
        $this->ingestEvents([
            $this->makeEvent(['event_id' => 'E1', 'amount' => 200.25, 'status' => 'approved']),
            $this->makeEvent(['event_id' => 'E2', 'amount' => 250.00, 'status' => 'approved']),
            $this->makeEvent(['event_id' => 'E3', 'amount' => 100.00, 'status' => 'pending']),
            $this->makeEvent(['event_id' => 'E4', 'amount' => 50.00, 'status' => 'rejected']),
        ]);

        $response = $this->getJson('/api/stations/S1/summary');

        $response->assertStatus(200)
            ->assertJson([
                'station_id' => 'S1',
                'total_approved_amount' => 450.25,
                'events_count' => 4,
            ]);
    }

    public function test_summary_for_nonexistent_station_returns_zeroes(): void
    {
        $response = $this->getJson('/api/stations/UNKNOWN/summary');

        $response->assertStatus(200)
            ->assertJson([
                'station_id' => 'UNKNOWN',
                'total_approved_amount' => 0,
                'events_count' => 0,
            ]);
    }

    public function test_out_of_order_arrival_produces_same_totals(): void
    {
        $eventA = $this->makeEvent(['event_id' => 'E1', 'amount' => 100.00, 'status' => 'approved']);
        $eventB = $this->makeEvent(['event_id' => 'E2', 'amount' => 250.50, 'status' => 'approved']);
        $eventC = $this->makeEvent(['event_id' => 'E3', 'amount' => 50.00, 'status' => 'pending']);

        // Ingest in reverse order
        $this->ingestEvents([$eventC]);
        $this->ingestEvents([$eventA]);
        $this->ingestEvents([$eventB]);

        $response = $this->getJson('/api/stations/S1/summary');

        $response->assertStatus(200)
            ->assertJson([
                'station_id' => 'S1',
                'total_approved_amount' => 350.50,
                'events_count' => 3,
            ]);
    }

    public function test_concurrent_ingestion_does_not_double_count(): void
    {
        $sharedEvent = $this->makeEvent(['event_id' => 'SHARED', 'amount' => 100.00, 'status' => 'approved']);
        $uniqueA = $this->makeEvent(['event_id' => 'A1', 'amount' => 50.00, 'status' => 'approved']);
        $uniqueB = $this->makeEvent(['event_id' => 'B1', 'amount' => 75.00, 'status' => 'approved']);

        // Simulate concurrent requests with overlapping event_ids
        $responseA = $this->postJson('/api/transfers', ['events' => [$sharedEvent, $uniqueA]]);
        $responseB = $this->postJson('/api/transfers', ['events' => [$sharedEvent, $uniqueB]]);

        $responseA->assertStatus(201);
        $responseB->assertStatus(201);

        // SHARED should be inserted only once
        $totalInserted = $responseA->json('inserted') + $responseB->json('inserted');
        $totalDuplicates = $responseA->json('duplicates') + $responseB->json('duplicates');
        $this->assertEquals(3, $totalInserted);
        $this->assertEquals(1, $totalDuplicates);

        $this->assertDatabaseCount('transfer_events', 3);

        // Summary should reflect exactly 3 events, amount = 100 + 50 + 75 = 225
        $this->getJson('/api/stations/S1/summary')
            ->assertJson([
                'total_approved_amount' => 225.00,
                'events_count' => 3,
            ]);
    }

    public function test_summary_isolates_stations(): void
    {
        $this->ingestEvents([
            $this->makeEvent(['event_id' => 'E1', 'station_id' => 'S1', 'amount' => 100.00]),
            $this->makeEvent(['event_id' => 'E2', 'station_id' => 'S2', 'amount' => 200.00]),
        ]);

        $response = $this->getJson('/api/stations/S1/summary');

        $response->assertStatus(200)
            ->assertJson([
                'station_id' => 'S1',
                'total_approved_amount' => 100.00,
                'events_count' => 1,
            ]);
    }
}
