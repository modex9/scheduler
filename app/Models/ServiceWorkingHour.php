<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ServiceWorkingHour extends Pivot
{
    protected $table = 'service_working_hours';

    protected $fillable = [
        'service_id',
        'working_hour_id',
    ];
}
