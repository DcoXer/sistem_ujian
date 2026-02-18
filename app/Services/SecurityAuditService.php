<?php

namespace App\Services;

use App\Models\SecurityAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SecurityAuditService
{
    public function log(Request $request, string $action, ?Model $target = null, array $meta = []): void
    {
        /** @var User|null $actor */
        $actor = $request->user();

        SecurityAudit::query()->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'meta' => $meta ?: null,
        ]);
    }

    public function logSystem(string $action, ?Model $target = null, array $meta = []): void
    {
        SecurityAudit::query()->create([
            'actor_user_id' => null,
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'ip_address' => null,
            'user_agent' => 'system/scheduler',
            'meta' => $meta ?: null,
        ]);
    }
}
