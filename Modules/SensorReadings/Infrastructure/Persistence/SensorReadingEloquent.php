<?php

namespace Modules\SensorReadings\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SensorReadingEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'sensor_readings';

    protected $fillable = ['sensor_id','timestamp','value','2)','unit'];

    protected $casts = [
        'sensor_id' => 'integer',
        'timestamp' => 'datetime',
        'value' => 'float',
    ];
}