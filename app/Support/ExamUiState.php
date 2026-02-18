<?php

namespace App\Support;

final class ExamUiState
{
    public const NOT_STARTED = 'not_started';
    public const RUNNING = 'running';
    public const FINISHED = 'finished';
    public const WAITING_RESULT = 'waiting_result';

    public static function all(): array
    {
        return [
            'not_started' => self::NOT_STARTED,
            'running' => self::RUNNING,
            'finished' => self::FINISHED,
            'waiting_result' => self::WAITING_RESULT,
        ];
    }
}

