<?php

namespace App\Models\KalenderPelari;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CalendarProject extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT_TRIAL = 'DRAFT_TRIAL';
    public const STATUS_DRAFT_SAVED = 'DRAFT_SAVED';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PENDING_CHECKOUT = 'PENDING_CHECKOUT';
    public const STATUS_PRODUCTION_ACTIVE = 'PRODUCTION_ACTIVE';
    public const STATUS_ARCHIVED = 'ARCHIVED';
    public const STATUS_EXPIRED_TRIAL = 'EXPIRED_TRIAL';

    protected $table = 'kp_calendar_projects';

    protected $fillable = [
        'user_id', 'template_id', 'cover_asset_id', 'anonymous_uuid',
        'name', 'year', 'format_type', 'is_anonymous', 'start_week_on',
        'status', 'state_draft', 'version_counter', 'expires_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'state_draft' => 'array',
        'version_counter' => 'integer',
        'expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dispatchesEvents = [];

    protected static function booted(): void
    {
        static::creating(function (self $project) {
            if ($project->is_anonymous && empty($project->anonymous_uuid)) {
                $project->anonymous_uuid = (string) Str::uuid();
            }
            if (empty($project->year)) {
                $project->year = (int) now()->format('Y') + 1;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverAsset(): BelongsTo
    {
        return $this->belongsTo(UploadedAsset::class, 'cover_asset_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(CalendarPage::class, 'project_id')
            ->orderBy('month_number');
    }

    public function elements()
    {
        return $this->hasManyThrough(
            CalendarElement::class,
            CalendarPage::class,
            'project_id',
            'page_id'
        );
    }

    public function routeEditor(): string
    {
        if ($this->is_anonymous) {
            return route('kalender-pelari.trial.editor', $this->anonymous_uuid);
        }

        return route('kalender-pelari.editor', $this);
    }
}
