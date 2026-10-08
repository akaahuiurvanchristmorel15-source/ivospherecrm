<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity event.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?int $domainId = null,
        ?array $properties = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'domain_id' => $domainId,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
