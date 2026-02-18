<?php

namespace App\Console\Commands;

use App\Models\ExamAttemptAudit;
use App\Models\SecurityAudit;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'security:prune-audits {--days=}';

    protected $description = 'Prune old security and attempt audit logs based on retention policy.';

    public function handle(): int
    {
        $retentionDays = (int) ($this->option('days') ?? config('security.audit_retention_days', 90));
        $retentionDays = max(1, $retentionDays);
        $cutoff = now()->subDays($retentionDays);

        $securityDeleted = SecurityAudit::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $attemptDeleted = ExamAttemptAudit::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$securityDeleted} security audits and {$attemptDeleted} attempt audits older than {$retentionDays} days.");

        return 0;
    }
}

