<?php

namespace Modules\Parcels\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParcelEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'parcels';

    protected $fillable = [
        'name','location','area_m2','user_id',
        'latitude','longitude','crop_type','active',
    ];

    protected $casts = [
        'area_m2'   => 'float',
        'latitude'  => 'float',
        'longitude' => 'float',
        'active'    => 'boolean',
    ];
}
