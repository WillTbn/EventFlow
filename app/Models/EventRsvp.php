<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'event_id',
        'name',
        'email',
        'phone',
        'communication_preference',
        'notifications_scope',
        'status',
        'source',
    ];

    /**
     * Get the event that owns the RSVP.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the workspace (tenant) that owns the RSVP.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'workspace_id');
    }
}
