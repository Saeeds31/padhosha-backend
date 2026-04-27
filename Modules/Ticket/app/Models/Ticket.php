<?php

namespace Modules\Ticket\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Users\Models\User;

class Ticket extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'status',
        'doer_id',
        'sender_id'
    ];
    public function doer()
    {
        return $this->belongsTo(User::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class);
    }
    public function tickets()
    {
        return $this->hasMany(Message::class);
    }
}
