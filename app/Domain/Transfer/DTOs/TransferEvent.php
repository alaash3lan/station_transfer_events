<?php

namespace App\Domain\Transfer\DTOs;

final readonly class TransferEvent
{
    public function __construct(
        public string $event_id,
        public string $station_id,
        public float $amount,
        public string $status,
        public string $created_at,
    ) {}
}
