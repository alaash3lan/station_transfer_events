<?php

namespace App\Http\Requests;

use App\Domain\Transfer\TransferEvent;
use App\Domain\Transfer\TransferEventCollection;
use Illuminate\Foundation\Http\FormRequest;

class IngestTransferEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'events' => ['required', 'array', 'min:1', 'max:1000'],
            'events.*.event_id' => ['required', 'string', 'filled'],
            'events.*.station_id' => ['required', 'string', 'filled'],
            'events.*.amount' => ['required', 'numeric', 'gte:0'],
            'events.*.status' => ['required', 'string', 'filled'],
            'events.*.created_at' => ['required', 'date_format:Y-m-d\TH:i:sP,Y-m-d\TH:i:s\Z'],
        ];
    }

    /**
     * Map validated payload to a typed domain collection.
     */
    public function toCollection(): TransferEventCollection
    {
        $events = array_map(
            fn(array $event) => new TransferEvent(
                event_id: $event['event_id'],
                station_id: $event['station_id'],
                amount: (string) $event['amount'],
                status: $event['status'],
                created_at: $event['created_at'],
            ),
            $this->validated()['events'],
        );

        return new TransferEventCollection(...$events);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'events.required' => 'The events array is required.',
            'events.min' => 'At least one event must be provided.',
            'events.*.event_id.required' => 'Each event must have an event_id.',
            'events.*.station_id.required' => 'Each event must have a station_id.',
            'events.*.amount.required' => 'Each event must have an amount.',
            'events.*.amount.numeric' => 'Amount must be a number.',
            'events.*.amount.gte' => 'Amount must be a non-negative number.',
            'events.*.status.required' => 'Each event must have a status.',
            'events.*.created_at.required' => 'Each event must have a created_at timestamp.',
            'events.*.created_at.date_format' => 'created_at must be a valid ISO8601 datetime (e.g. 2026-02-19T10:00:00Z).',
        ];
    }
}
