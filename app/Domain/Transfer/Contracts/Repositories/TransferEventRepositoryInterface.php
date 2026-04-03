<?php

namespace App\Domain\Transfer\Contracts\Repositories;

use App\Domain\Transfer\DTOs\StationSummary;
use App\Domain\Transfer\DTOs\TransferEventCollection;
use App\Domain\Transfer\DTOs\TransferResult;

interface TransferEventRepositoryInterface
{
    public function insertBatch(TransferEventCollection $events): TransferResult;

    /**
     * Returns a zero-value summary if the station has no events.
     */
    public function getStationSummary(string $stationId): StationSummary;
}
