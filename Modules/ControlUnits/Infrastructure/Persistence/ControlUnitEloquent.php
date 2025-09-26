<?php

namespace Modules\ControlUnits\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ControlUnitEloquent extends Model
{
    use SoftDeletes;

    protected $table = 'control_units';

    protected $fillable = ['serial_code','model','installed_at','status','parcel_id','mqtt_client_id','mqtt_username','mqtt_password_enc','status_topic','lwt_topic','last_seen_at','active'];

    protected $casts = [
        'parcel_id' => 'integer',
        'active' => 'boolean',
    ];
}