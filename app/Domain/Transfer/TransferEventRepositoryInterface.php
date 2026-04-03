<?php

namespace App\Domain\Transfer;

interface TransferEventRepositoryInterface
{
    public function insertBatch(TransferEventCollection $events): TransferResult;

    /**
     * Returns a zero-value summary if the station has no events.
     */
    public function getStationSummary(string $stationId): StationSummary;
}
