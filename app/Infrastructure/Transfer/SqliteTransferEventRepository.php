<?php

namespace App\Infrastructure\Transfer;

use App\Domain\Transfer\StationSummary;
use App\Domain\Transfer\TransferEventCollection;
use App\Domain\Transfer\TransferEventRepositoryInterface;
use App\Domain\Transfer\TransferResult;
use Illuminate\Support\Facades\DB;

class SqliteTransferEventRepository implements TransferEventRepositoryInterface
{
    public function insertBatch(TransferEventCollection $events): TransferResult
    {
        if ($events->isEmpty()) {
            return new TransferResult(inserted: 0, duplicates: 0);
        }

        $rows = array_map(fn($event) => [
            'event_id' => $event->event_id,
            'station_id' => $event->station_id,
            'amount' => $event->amount,
            'status' => $event->status,
            'created_at' => $event->created_at,
        ], $events->all());

        $inserted = DB::transaction(function () use ($rows) {
            $count = 0;
            foreach (array_chunk($rows, 100) as $chunk) {
                $count += DB::table('transfer_events')->insertOrIgnore($chunk);
            }
            return $count;
        });

        return new TransferResult(
            inserted: $inserted,
            duplicates: $events->count() - $inserted,
        );
    }

    public function getStationSummary(string $stationId): StationSummary
    {
        $result = DB::table('transfer_events')
            ->where('station_id', $stationId)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END), 0) as total_approved_amount, COUNT(*) as events_count',
                ['approved']
            )
            ->first();

        return new StationSummary(
            station_id: $stationId,
            total_approved_amount: number_format((float) $result->total_approved_amount, 2, '.', ''),
            events_count: (int) $result->events_count,
        );
    }
}
