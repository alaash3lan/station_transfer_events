<?php

namespace App\Domain\Transfer;

final readonly class TransferResult
{
    public function __construct(
        public int $inserted,
        public int $duplicates,
    ) {}
}
