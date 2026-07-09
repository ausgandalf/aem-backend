<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        // Core identity
        'name',
        'registration_number',
        'legal_status',
        'type',
        'founded_year',
        // Registered (legal) address
        'registered_country',
        'registered_state_province',
        'registered_city',
        'registered_address_line1',
        'registered_address_line2',
        'registered_postal_code',
        // Operating / correspondence address
        'current_same_as_registered',
        'current_country',
        'current_state_province',
        'current_city',
        'current_address_line1',
        'current_address_line2',
        'current_postal_code',
        // Contact
        'contact_email',
        'contact_phone',
        'website_url',
        // Social
        'social_facebook',
        'social_linkedin',
        'social_twitter',
        'social_instagram',
        'social_youtube',
        'social_whatsapp',
        // Financials
        'currency',
        'annual_income',
        'annual_expenditure',
        'reserves_policy',
        // Metadata
        'status',
        'note',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata'                    => 'array',
            'founded_year'                => 'integer',
            'annual_income'               => 'decimal:2',
            'annual_expenditure'          => 'decimal:2',
            'current_same_as_registered'  => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}