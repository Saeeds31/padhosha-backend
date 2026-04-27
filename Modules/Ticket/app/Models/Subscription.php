<?php

namespace Modules\Ticket\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employer\Models\Employer;

// use Modules\Ticket\Database\Factories\SubscriptionFactory;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'start_date',
        'expiration_date',
        'level_type',
        'employer_id'
    ];
    protected $casts = [
        'expiration_date'=>'date'
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
