<?php

namespace Modules\Employer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Employer\Database\Factories\CostFactory;

class Cost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'amount',
        'costable_id',
        'costable_type',
        'employer_id'

    ];
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
