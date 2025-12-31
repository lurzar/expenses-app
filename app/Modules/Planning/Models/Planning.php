<?php

namespace App\Modules\Planning\Models;

use App\Models\User;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planning extends Model
{
    use HasFactory, HasPublicId, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'plannings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'planning_id',
        'user_id',
        'month',
        'year',
        'salary',
        'sections',
        'totals',
        'slug',
    ];

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'salary' => 'float',
            'sections' => AsCollection::class,
            'totals' => AsCollection::class,
        ];
    }

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['name', 'spending'];

    /**
     * Get the column name for the public ID.
     */
    public function publicIdColumn(): string
    {
        return 'planning_id';
    }

    public function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->month.', '.$this->year,
        );
    }

    public function spending(): Attribute
    {
        $value = $this->totals ? $this->totals->sum() : 0.00;

        return new Attribute(
            get: fn () => number_format($value, 2),
        );
    }

    /**
     * Get the user that owns the Planning
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    protected function scopeThisMonth($query)
    {
        return $query->where('month', getCurrentMonthName());
    }
}
