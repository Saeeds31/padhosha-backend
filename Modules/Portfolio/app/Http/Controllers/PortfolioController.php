<?php

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Notifications\Services\NotificationService;
use Modules\Portfolio\Http\Requests\PortfolioStoreRequest;
use Modules\Portfolio\Http\Requests\PortfolioUpdateRequest;
use Modules\Portfolio\Models\Portfolio;
use Modules\PortfolioCategory\Models\PortfolioCategory;
use Modules\Products\Http\Requests\ProductStoreRequest;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Portfolio::with(['categories', 'technologies'])->latest();
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        if ($status = $request->get('status')) {
            $query->where(function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
        $portfolios = $query->paginate(15);
        return response()->json($portfolios);
    }

    // ذخیره نمونه کار
    public function store(PortfolioStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();

        // main_image
        if ($request->hasFile('main_image')) {
            $data['main_image'] = $request->file('main_image')->store('portfolios/main', 'public');
        }

        $portfolio = Portfolio::create($data);
        // دسته‌بندی‌ها
        if (!empty($data['categories'])) {
            $portfolio->categories()->sync($data['categories']);
        }
        if (!empty($data['technologies'])) {
            $portfolio->technologies()->sync($data['technologies']);
        }
        // تصاویر اضافی
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('portfolios/images', 'public');
                $portfolio->images()->create([
                    'path'       => $path,
                    'alt'        => $portfolio->title,
                ]);
            }
        }
        $notifications->create(
            "ثبت نمونه کار",
            "نمونه کار {$portfolio->title} در سیستم ثبت شد",
            "notification_portfolio",
            ['portfolio' => $portfolio->id]
        );
        return response()->json($portfolio->load('categories', 'images', 'technologies'));
    }

    public function show($id)
    {
        $portfolio = Portfolio::with(['categories', 'images', 'technologies'])->find($id);

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'نمونه کار پیدا نشد',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'جزئیات نمونه کار',
            'data'    => $portfolio
        ]);
    }
    // آپدیت نمونه کار
    public function update(PortfolioUpdateRequest $request, Portfolio $portfolio, NotificationService $notifications)
    {
        $data = $request->validated();
        if ($request->hasFile('main_image')) {
            // حالت 2: فایل جدید اومده
            if ($portfolio->main_image) {
                Storage::disk('public')->delete($portfolio->main_image);
            }
            $data['main_image'] = $request->file('main_image')->store('portfolios/main', 'public');
        } elseif ($request->filled('main_image') && is_string($request->main_image)) {
            // حالت 1: رشته ارسال شده (تصویر قبلی دست نخورده)
            $data['main_image'] = $portfolio->main_image;
        }

        $portfolio->update($data);
        // دسته‌بندی‌ها
        if (!empty($data['categories'])) {
            $portfolio->categories()->sync($data['categories']);
        }
        if (!empty($data['technologies'])) {
            $portfolio->technologies()->sync($data['technologies']);
        }
        if ($request->filled('deleted_images')) {
            $deletedIds = $request->input('deleted_images'); // [1,2,3,...]
            $oldImages = $portfolio->images()->whereIn('id', $deletedIds)->get();
            foreach ($oldImages as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }
        // تصاویر جدید
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/images', 'public');
                $portfolio->images()->create([
                    'path'       => $path,
                    'alt'        => $portfolio->title,
                ]);
            }
        }
        $notifications->create(
            "ویرایش نمونه کار",
            "نمونه کار {$portfolio->title} در سیستم ویرایش شد",
            "notification_portfolio",
            ['portfolio' => $portfolio->id]
        );
        return response()->json($portfolio->load('categories', 'images'));
    }

    // حذف نمونه کار
    public function destroy(Portfolio $portfolio, NotificationService $notifications)
    {

        if ($portfolio->main_image) {
            Storage::disk('public')->delete($portfolio->main_image);
        }

        foreach ($portfolio->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        $notifications->create(
            "حذف نمونه کار",
            "نمونه کار {$portfolio->title} از سیستم حذف شد",
            "notification_portfolio",
            ['portfolio' => $portfolio->id]
        );

        $portfolio->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function frontIndex(Request $request)
    {
        $query = Portfolio::with(['categories', 'technologies'])
            ->where('status', 1)->latest(); // فقط فعال‌ها

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $category = null;
        if ($category_id = $request->get('category_id')) {
            $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('slug', $category_id);
            });
            $category = PortfolioCategory::where('slug', $category_id)->first();
        }

        $portfolios = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'لیست نمونه کارا',
            'data'    => $portfolios,
            'category'    => $category,
        ]);
    }
    public function frontDetail(Request $request, string $slug)
    {
        $portfolio = Portfolio::with([
            'categories',
            'technologies',
            'images:id,portfolio_id,path',
        ])->where('slug', $slug)->first();
        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => "نمونه کار مد نظر پیدا نشد"
            ]);
        }
        return response()->json([
            'success' => true,
            'data' =>        $portfolio
        ]);
    }
    public function similar(string $slug)
    {
        $portfolio = Portfolio::with('categories')->where('slug', $slug)->first();
        if (!$portfolio) {
            return response()->json([
                'success' => true,
                'data' => [
                    'similar_portfolios' => []
                ]
            ]);
        }
        // گرفتن ID دسته‌ها
        $categoryIds = $portfolio->categories->pluck('id');
        // پیدا کردن نمونه کارات مشابه
        $similar = Portfolio::where('status', 1)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $portfolio->id) // حذف نمونه کار اصلی
            ->with([
                'images:id,portfolio_id,path',
                'categories'
            ])
            ->limit(10)
            ->get();
        // اگر مشابه پیدا نشد → fallback
        if ($similar->isEmpty()) {
            $similar = Portfolio::where('status', 1)
                ->where('id', '!=', $portfolio->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        return response()->json([
            'success' => true,
            'data' => [
                'similar_portfolios' => $similar
            ]
        ]);
    }
}
