<?php

namespace Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PortfolioCategory\Models\PortfolioCategory;
use Modules\PortfolioImages\Models\PortfolioImages;
use Modules\PortfolioTechnology\Models\PortfolioTechnology;

// use Modules\Portfolio\Database\Factories\PortfolioFactory;

class Portfolio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = "portfolios";
    protected $fillable = [
        'title',
        'main_image',
        'link',
        'meta_title',
        'meta_description',
        'description',
        'slug',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
    public function images()
    {
        return $this->hasMany(PortfolioImages::class);
    }

    public function categories()
    {
        return $this->belongsToMany(PortfolioCategory::class, 'portfolios_categories', 'portfolio_id', 'portfolio_category_id');
    }
    public function technologies()
    {
        return $this->belongsToMany(PortfolioTechnology::class, 'technology_portfolio', 'portfolio_id', 'technology_id');
    }


    public static function homeData()
    {
       
        $portfolios = self::with(['categories', 'images', 'technologies'])
            ->where('status', true)
            ->get();

        $grouped = [];

        foreach ($portfolios as $portfolio) {
            foreach ($portfolio->categories as $category) {
                $grouped[$category->title][] = $portfolio;
            }
        }

        return $grouped;
    }
}
