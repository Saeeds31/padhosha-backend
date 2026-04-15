<?php

namespace Modules\PortfolioCategory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PortfolioCategory\Database\Factories\PortfolioCategoryFactory;

class PortfolioCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = "portfolio_category";
    protected $fillable = [
        'title',
        'icon',
        'description',
        'meta_title',
        'meta_description',
        'slug'
    ];

    public function portfolios()
    {
        return $this->belongsToMany(PortfolioCategory::class, 'portfolios_categories');
    }
}
