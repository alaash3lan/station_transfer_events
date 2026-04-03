<?php

namespace App\Domain\Transfer;

final readonly class TransferEventCollection
{
    /** @var TransferEvent[] */
    private array $items;

    public function __construct(TransferEvent ...$events)
    {
        $this->items = $events;
    }

    /**
     * @return TransferEvent[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
