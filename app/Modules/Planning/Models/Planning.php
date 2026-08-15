<?php

namespace App\Modules\Planning\Models;

use App\Models\User;
use App\Modules\Planning\Database\Factories\PlanningFactory;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $planning_id
 * @property int $user_id
 * @property int $month
 * @property int $year
 * @property string $salary
 * @property string $saving_rate
 * @property Collection<string, mixed>|null $sections
 * @property Collection<string, float|int|string>|null $totals
 * @property-read string $name
 * @property-read string $spending
 */
class Planning extends Model
{
    /** @use HasFactory<PlanningFactory> */
    use HasFactory;

    use HasPublicId, SoftDeletes;

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
        'saving_rate',
        'sections',
        'totals',
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
            'month' => 'integer',
            'year' => 'integer',
            'salary' => 'decimal:2',
            'saving_rate' => 'decimal:2',
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

    /** @return Attribute<string, never> */
    protected function name(): Attribute
    {
        return new Attribute(
            get: fn () => match ($this->month) {
                1 => 'January',
                2 => 'February',
                3 => 'March',
                4 => 'April',
                5 => 'May',
                6 => 'June',
                7 => 'July',
                8 => 'August',
                9 => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
                default => 'Invalid month',
            }.', '.$this->year,
        );
    }

    /** @return Attribute<string, never> */
    protected function spending(): Attribute
    {
        return new Attribute(
            get: fn () => (string) ($this->totals?->get('spending') ?? '0.00'),
        );
    }

    /**
     * Get the user that owns the Planning
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Planning>  $query
     * @return Builder<Planning>
     */
    protected function scopeThisMonth(Builder $query): Builder
    {
        return $query->where('month', (int) now()->format('n'));
    }
}
