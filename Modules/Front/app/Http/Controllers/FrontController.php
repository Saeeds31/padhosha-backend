<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Articles\Models\Article;
use Modules\Banners\Models\Banner;
use Modules\Categories\Models\Category;
use Modules\Comments\Models\Comment;
use Modules\Employer\Models\Employer;
use Modules\Menus\Models\Menu;
use Modules\Portfolio\Models\Portfolio;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductVariant;
use Modules\Settings\Models\Setting;
use Modules\Sliders\Models\Slider;
use Modules\Users\Models\User;

class FrontController extends Controller
{
    // app/Http/Controllers/ProductController.php

    public function priceRange(): array
    {
        // کمترین/بیشترین قیمت در جدول products
        $minProductPrice = Product::min('price');
        $maxProductPrice = Product::max('price');

        // کمترین/بیشترین قیمت در جدول variants
        $minVariantPrice = ProductVariant::min('price');
        $maxVariantPrice = ProductVariant::max('price');

        // محاسبه‌ی نهایی با در نظر گرفتن مقدار null
        $min = null;
        $max = null;
        if ($minProductPrice !== null && $minVariantPrice !== null) {
            $min = min($minProductPrice, $minVariantPrice);
        } elseif ($minProductPrice !== null) {
            $min = $minProductPrice;
        } else {
            $min = $minVariantPrice;
        }

        if ($maxProductPrice !== null && $maxVariantPrice !== null) {
            $max = max($maxProductPrice, $maxVariantPrice);
        } elseif ($maxProductPrice !== null) {
            $max = $maxProductPrice;
        } else {
            $max = $maxVariantPrice;
        }

        return [
            'min_price' => $min,
            'max_price' => $max,
        ];
    }

    public function filters()
    {
        $data = [];
        $data['categories'] = Category::with('children')
            ->whereNull('parent_id')
            ->get();
        $data['price'] = $this->priceRange();
        return response()->json([
            'success' => true,
            'message' => 'فیلتر های محصولات',
            'data'    => $data
        ], 200);
    }

    public function HomeProducts()
    {
        $categories = Category::with([
            'products' => function ($q) {
                $q->where('status', 'published')->latest()->take(8);
            }
        ])
            ->where('show_products_in_home', true)
            ->get();

        $result = $categories->map(function ($category) {
            return [
                'category' => $category,
                'products' => $category->products,
            ];
        });

        return response()->json($result);
    }
    public function home()
    {
        $data = [];
        $data['blogs'] = Article::latestArticles();
        $data['portfolios'] = Portfolio::homeData();
        $data['comments'] =   User::whereHas('roles', function ($q) {
            $q->where('slug', 'employer');
        })
            ->with(['comments' => function ($q) {
                $q->where('commentable_type', 'Support');
            }])
            ->latest()->take(12)->get();
        $data['logos'] = Employer::latest()->take(12)->select(['bussines_logo', 'link'])->get();
        return response()->json([
            'success' => true,
            'message' => 'اطلاعات صفحه اصلی',
            'data'    => $data
        ], 200);
    }

    public function base(Request $request)
    {
        $data = [];
        // بررسی وضعیت لاگین کاربر
        $user = Auth::guard('sanctum')->user();
        $data['user'] = $user ??  null;
        // settings
        $data['settings'] = Setting::all()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->value];
                })->toArray();
            });
        // menus
        $menus = Menu::with('children')
            ->whereNull('parent_id')
            ->get();
        $groupedMenus = [];
        foreach ($menus as $menu) {
            if (!isset($groupedMenus[$menu->group])) {
                $groupedMenus[$menu->group] = [];
            }
            $groupedMenus[$menu->group][] = $menu;
        }
        $data['menus'] = $groupedMenus;
        return response()->json([
            'success' => true,
            'message' => 'home data successfully',
            'data'    => $data
        ], 200);
    }
}
