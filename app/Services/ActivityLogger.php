<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
