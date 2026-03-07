<?php

return [
    'author_max_questions_per_exam' => (int) env('EXAM_AUTHOR_MAX_QUESTIONS', 100),
    'content_cache_store' => env('EXAM_CONTENT_CACHE_STORE'),
    'content_cache_ttl_seconds' => (int) env('EXAM_CONTENT_CACHE_TTL_SECONDS', 1800),
    'content_cache_lock_seconds' => (int) env('EXAM_CONTENT_CACHE_LOCK_SECONDS', 10),
    'content_cache_lock_wait_seconds' => (int) env('EXAM_CONTENT_CACHE_LOCK_WAIT_SECONDS', 3),
    'content_cache_fallback_wait_ms' => (int) env('EXAM_CONTENT_CACHE_FALLBACK_WAIT_MS', 1200),
    'timer_poll_interval_seconds' => (int) env('EXAM_TIMER_POLL_INTERVAL_SECONDS', 30),
];
