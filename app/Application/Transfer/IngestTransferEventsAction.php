<?php

namespace App\Application\Transfer;

use App\Domain\Transfer\Contracts\Repositories\TransferEventRepositoryInterface;
use App\Domain\Transfer\DTOs\TransferEventCollection;
use App\Domain\Transfer\DTOs\TransferResult;
use Illuminate\Support\Facades\Log;

class IngestTransferEventsAction
{
    public function __construct(
        private readonly TransferEventRepositoryInterface $repository,
    ) {}

    public function execute(TransferEventCollection $events): TransferResult
    {
        Log::info('Ingesting transfer events batch', ['count' => $events->count()]);
        $result = $this->repository->insertBatch($events);

        Log::info('Transfer events ingested', [
            'inserted' => $result->inserted,
            'duplicates' => $result->duplicates,
        ]);

        return $result;
    }
}
