<?php

namespace App\Application\Transfer;

use App\Domain\Transfer\StationSummary;
use App\Domain\Transfer\TransferEventRepositoryInterface;
use Illuminate\Support\Facades\Log;

class GetStationSummaryAction
{
    public function __construct(
        private readonly TransferEventRepositoryInterface $repository,
    ) {}

    public function execute(string $stationId): StationSummary
    {
        Log::info('Fetching station summary', ['station_id' => $stationId]);

        return $this->repository->getStationSummary($stationId);
    }
}
