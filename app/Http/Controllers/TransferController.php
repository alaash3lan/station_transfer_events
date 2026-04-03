<?php

namespace App\Http\Controllers;

use App\Application\Transfer\GetStationSummaryAction;
use App\Application\Transfer\IngestTransferEventsAction;
use App\Http\Requests\IngestTransferEventsRequest;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function ingest(
        IngestTransferEventsRequest $request,
        IngestTransferEventsAction $action,
    ): JsonResponse {
        $result = $action->execute($request->toCollection());

        return response()->json([
            'inserted' => $result->inserted,
            'duplicates' => $result->duplicates,
        ], 201);
    }

    public function summary(
        string $stationId,
        GetStationSummaryAction $action,
    ): JsonResponse {
        $summary = $action->execute($stationId);

        return response()->json([
            'station_id' => $summary->station_id,
            'total_approved_amount' => $summary->total_approved_amount,
            'events_count' => $summary->events_count,
        ]);
    }
}
