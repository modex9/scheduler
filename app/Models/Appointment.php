<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_date',
        'appointment_time',
        'service_id',
        'client_email',
        'client_name',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date:Y-m-d',
        'appointment_time' => 'datetime:H:i',
    ];

    /**
     * Get the service for this appointment.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope to get only confirmed appointments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to get appointments for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    /**
     * Get the full appointment datetime.
     */
    public function getAppointmentDateTimeAttribute()
    {
        return $this->appointment_date->setTimeFrom($this->appointment_time);
    }
}
