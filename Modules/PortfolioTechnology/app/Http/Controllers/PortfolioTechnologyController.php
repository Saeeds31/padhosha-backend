<?php

namespace Modules\PortfolioTechnology\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Notifications\Services\NotificationService;
use Modules\PortfolioTechnology\Http\Requests\PortfolioTechnologyStoreRequest;
use Modules\PortfolioTechnology\Http\Requests\PortfolioTechnologyUpdateRequest;
use Modules\PortfolioTechnology\Models\PortfolioTechnology;

class PortfolioTechnologyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 100);

        $technologies = PortfolioTechnology::paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'لیست تکنولوژی ها ',
            'data'    => $technologies
        ]);
    }

    public function show($id)
    {
        $technology = PortfolioTechnology::find($id);

        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'تکنولوژی پیدا نشد',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'جزئیات تکنولوژی',
            'data'    => $technology
        ]);
    }

    // Store a new technology
    public function store(PortfolioTechnologyStoreRequest $request, NotificationService $notifications)
    {
        $validated = $request->validated();
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('technologies/icons', 'public');
        }
        $technology = PortfolioTechnology::create($validated);
        $notifications->create(
            " ثبت تکنولوژی ",
            "تکنولوژی   {$technology->title}در سیستم ثبت  شد",
            "notification_portfolio",
            ['notification_portfolio' => $technology->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'تکنولوژی با موفقیت ثبت شد',
            'data'    => $technology
        ], 201);
    }

    // Update a technology
    public function update(PortfolioTechnologyUpdateRequest $request, $id, NotificationService $notifications)
    {
        $technology = PortfolioTechnology::find($id);
        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'تکنولوژی پیدا نشد',
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            if ($technology->icon) {
                Storage::disk('public')->delete($technology->icon);
            }
            $data['icon'] = $request->file('icon')->store('technologies/icons', 'public');
        } elseif ($request->filled('icon') && is_string($request->icon)) {
            $data['icon'] = $technology->icon;
        }
        $technology->update($data);
        $notifications->create(
            " بروزرسانی تکنولوژی ",
            "تکنولوژی   {$technology->title}در سیستم ویرایش  شد",
            "notification_portfolio",
            ['notification_portfolio' => $technology->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'تکنولوژی ویرایش شد',
            'data'    => $technology
        ]);
    }

    // Delete a technology
    public function destroy($id, NotificationService $notifications)
    {
        $technology = PortfolioTechnology::find($id);

        if (!$technology) {
            return response()->json([
                'success' => false,
                'message' => 'تکنولوژی پیدا نشد',
            ], 404);
        }
        if ($technology->portfiolios()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'این تکنولوژی به نمونه کار متصل است و قابل حذف نیست.',
            ], 422);
        }

        $notifications->create(
            " حذف تکنولوژی ",
            "تکنولوژی   {$technology->title}از سیستم حذف  شد",
            "notification_portfolio",
            ['notification_portfolio' => $technology->id]
        );
        $technology->delete();
        return response()->json([
            'success' => true,
            'message' => 'تکنولوژی با موفقیت حذف شد',
        ]);
    }
}
