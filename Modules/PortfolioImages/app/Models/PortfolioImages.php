<?php

namespace Modules\PortfolioImages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Portfolio\Models\Portfolio;

// use Modules\PortfolioImages\Database\Factories\PortfolioImagesFactory;

class PortfolioImages extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'path',
        'alt',
        'portfolio_id',
    ];
    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
