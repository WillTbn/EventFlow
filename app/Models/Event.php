<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'status',
        'is_public',
        'capacity',
        'main_photo_path',
        'main_photo_medium_path',
        'main_photo_thumb_path',
    ];

    /**
     * Get the user that created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the photos for the event.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }
}
