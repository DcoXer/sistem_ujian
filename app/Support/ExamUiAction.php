<?php

namespace App\Support;

final class ExamUiAction
{
    public const START_ENABLED = 'start_enabled';
    public const START_DISABLED = 'start_disabled';
    public const CONTINUE_ENABLED = 'continue_enabled';
    public const CONTINUE_DISABLED = 'continue_disabled';
    public const WAITING_RESULT = 'waiting_result';
    public const RESULT = 'result';

    public static function all(): array
    {
        return [
            'start_enabled' => self::START_ENABLED,
            'start_disabled' => self::START_DISABLED,
            'continue_enabled' => self::CONTINUE_ENABLED,
            'continue_disabled' => self::CONTINUE_DISABLED,
            'waiting_result' => self::WAITING_RESULT,
            'result' => self::RESULT,
        ];
    }
}

