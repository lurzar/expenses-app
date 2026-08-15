<?php

namespace App\Modules\ActivityLog\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class ActivityLog extends Model
{
    use HasPublicId;

    public const UPDATED_AT = null;

    protected $fillable = [
        'event',
        'actor_id',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function publicIdColumn(): string
    {
        return 'activity_id';
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Activity records are append-only.');
        });

        static::deleting(function (): never {
            throw new LogicException('Activity records can only be removed by retention pruning.');
        });
    }
}
