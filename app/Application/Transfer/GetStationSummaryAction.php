<?php

namespace App\Application\Transfer;

use App\Domain\Transfer\Contracts\Repositories\TransferEventRepositoryInterface;
use App\Domain\Transfer\DTOs\StationSummary;
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
