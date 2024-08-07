<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Constellation extends Model
{
    /** @var string $table */
    protected $table = "constellations";

    /**
     * Regions relation.
     *
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    /**
     * Systems relation.
     *
     * @return HasMany
     */
    public function systems(): HasMany
    {
        return $this->hasMany(System::class);
    }
}
