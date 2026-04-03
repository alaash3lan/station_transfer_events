<?php

namespace App\Domain\Transfer\DTOs;

final readonly class TransferResult
{
    public function __construct(
        public int $inserted,
        public int $duplicates,
    ) {}
}
