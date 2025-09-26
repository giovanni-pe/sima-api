<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'address',
        'is_frequent',
        'usage_count'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_frequent' => 'boolean',
        'usage_count' => 'integer'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true);
    }

    public function scopeMostUsed($query, $limit = 10)
    {
        return $query->where('usage_count', '>', 0)
                    ->orderBy('usage_count', 'desc')
                    ->limit($limit);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = trim($value);
    }

    // Accessors
    public function getFormattedCoordinatesAttribute()
    {
        return number_format($this->latitude, 6) . ', ' . number_format($this->longitude, 6);
    }

    // Methods
    public function incrementUsage()
    {
        $this->increment('usage_count');

        // Auto-mark as frequent if used more than 3 times
        if ($this->usage_count >= 3 && !$this->is_frequent) {
            $this->update(['is_frequent' => true]);
        }

        return $this;
    }

    public function calculateDistance($latitude, $longitude)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $latDiff = deg2rad($latitude - $this->latitude);
        $lngDiff = deg2rad($longitude - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }
}
