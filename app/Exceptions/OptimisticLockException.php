<?php

namespace App\Exceptions;

class OptimisticLockException extends StateConflictException
{
    public function __construct(
        string $message = 'Jawaban sudah berubah. Muat ulang halaman lalu coba lagi.',
        public readonly ?int $currentVersion = null,
        public readonly ?int $currentOptionId = null
    ) {
        parent::__construct($message);
    }
}

