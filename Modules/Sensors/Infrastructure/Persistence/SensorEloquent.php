<?php

namespace Modules\Sensors\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SensorEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'sensors';

    protected $fillable = ['name', 'type', 'control_unit_id', 'active'];
    protected $casts = [
        'control_unit_id' => 'integer',
        'active' => 'boolean',
    ];
}
