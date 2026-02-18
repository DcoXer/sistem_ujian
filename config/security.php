<?php

return [
    // 0 = disabled. If > 0, active attempts with no activity for N minutes are auto-expired.
    'attempt_idle_timeout_minutes' => (int) env('ATTEMPT_IDLE_TIMEOUT_MINUTES', 0),

    // Retention window for audit tables.
    'audit_retention_days' => (int) env('AUDIT_RETENTION_DAYS', 90),
];

