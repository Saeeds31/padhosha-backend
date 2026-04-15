<?php

namespace Modules\PortfolioTechnology\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PortfolioTechnology extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'icon',
        'description',
        'meta_title',
        'meta_description',
        'slug'
    ];
    protected $table = "technologies";
    public function portfiolios()
    {
        return $this->belongsToMany(PortfolioTechnology::class, 'technology_portfolio', 'portfolio_id', 'technology_id');
    }
}
