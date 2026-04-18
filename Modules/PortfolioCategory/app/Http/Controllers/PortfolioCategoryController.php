<?php

namespace Modules\PortfolioCategory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Notifications\Services\NotificationService;
use Modules\PortfolioCategory\Http\Requests\PortfolioCategoryStoreRequest;
use Modules\PortfolioCategory\Http\Requests\PortfolioCategoryUpdateRequest;
use Modules\PortfolioCategory\Models\PortfolioCategory;

class PortfolioCategoryController extends Controller
{
    // List all categories with pagination
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 100);

        $categories = PortfolioCategory::paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'لیست دسته بندی های ',
            'data'    => $categories
        ]);
    }

    // Show a single category
    public function show($id)
    {
        $category = PortfolioCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'دسته بندی پیدا نشد',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'جزئیات دسته بندی',
            'data'    => $category
        ]);
    }

    // Store a new category
    public function store(PortfolioCategoryStoreRequest $request, NotificationService $notifications)
    {
        $validated = $request->validated();
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('portfolios/categories', 'public');
        }
        $category = PortfolioCategory::create($validated);
        $notifications->create(
            " ثبت دسته بندی نمونه کار",
            "دسته بندی نمونه کار  {$category->title}در سیستم ثبت  شد",
            "notification_portfolio",
            ['notification_portfolio' => $category->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'دسته بندی با موفقیت ثبت شد',
            'data'    => $category
        ], 201);
    }

    // Update a category
    public function update(PortfolioCategoryUpdateRequest $request, $id, NotificationService $notifications)
    {
        $category = PortfolioCategory::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'دسته بندی پیدا نشد',
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = $request->file('icon')->store('portfolios/categories', 'public');
        } elseif ($request->filled('icon') && is_string($request->icon)) {
            $data['icon'] = $category->icon;
        } else {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $data['icon'] = null;
        }
        $category->update($data);
        $notifications->create(
            " بروزرسانی دسته بندی نمونه کار",
            "دسته بندی نمونه کار  {$category->title}در سیستم ویرایش  شد",
            "notification_portfolio",
            ['notification_portfolio' => $category->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'دسته بندی ویرایش شد',
            'data'    => $category
        ]);
    }

    // Delete a category
    public function destroy($id, NotificationService $notifications)
    {
        $category = PortfolioCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'دسته بندی پیدا نشد',
            ], 404);
        }
        if ($category->portfolios()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این دسته‌بندی به نمونه کار متصل است و قابل حذف نیست.',
            ], 422);
        }

        $notifications->create(
            " حذف دسته بندی نمونه کار",
            "دسته بندی نمونه کار  {$category->title}از سیستم حذف  شد",
            "notification_portfolio",
            ['notification_portfolio' => $category->id]
        );
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'دسته بندی با موفقیت حذف شد',
        ]);
    }
}
