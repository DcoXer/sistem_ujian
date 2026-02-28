<?php

return [
    'author_max_questions_per_exam' => (int) env('EXAM_AUTHOR_MAX_QUESTIONS', 100),
    'content_cache_store' => env('EXAM_CONTENT_CACHE_STORE'),
    'content_cache_ttl_seconds' => (int) env('EXAM_CONTENT_CACHE_TTL_SECONDS', 1800),
];
