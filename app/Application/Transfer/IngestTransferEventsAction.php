<?php

namespace App\Application\Transfer;

use App\Domain\Transfer\TransferEventCollection;
use App\Domain\Transfer\TransferEventRepositoryInterface;
use App\Domain\Transfer\TransferResult;
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
