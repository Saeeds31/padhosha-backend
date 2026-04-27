<?php

namespace Modules\Employer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ticket\Models\Subscription;
use Modules\Users\Models\User;

// use Modules\Employer\Database\Factories\EmployerFactory;

class Employer extends Model
{
    use HasFactory;
    protected $fillable = [
        'bussines_label',
        'bussines_logo',
        'link',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
