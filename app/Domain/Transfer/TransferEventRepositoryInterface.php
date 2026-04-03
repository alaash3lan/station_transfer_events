<?php

namespace App\Domain\Transfer;

interface TransferEventRepositoryInterface
{
    /**
     * @param TransferEvent[] $events
     */
    public function insertBatch(array $events): TransferResult;

    public function getStationSummary(string $stationId): StationSummary;
}
