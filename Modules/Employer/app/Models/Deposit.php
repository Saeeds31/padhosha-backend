<?php

namespace Modules\Employer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Employer\Database\Factories\DepositFactory;

class Deposit extends Model
{
    use HasFactory;
    protected $table = 'deposit';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'image',
        'amount',
        'status',
        'employer_id',
        'admin_note'
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
