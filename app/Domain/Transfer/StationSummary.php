<?php

namespace App\Domain\Transfer;

final readonly class StationSummary
{
    public function __construct(
        public string $station_id,
        public string $total_approved_amount,
        public int $events_count,
    ) {}
}
