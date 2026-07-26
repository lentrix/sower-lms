<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an entry in the system log.
     *
     * $model is a loose reference only - it is not a foreign key, so the
     * referenced record may already be gone (e.g. a deleted payment).
     */
    public static function record(string $action, string $description, ?Model $model = null, array $properties = []) {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'loggable_type' => $model ? get_class($model) : null,
            'loggable_id' => $model?->id,
            'properties' => $properties ?: null,
        ]);
    }
}
