<?php

namespace Modules\Employer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Comments\Http\Requests\CommentStoreRequest;
use Modules\Comments\Models\Comment;
use Modules\Employer\Http\Requests\EmployerStoreRequest;
use Modules\Employer\Http\Requests\EmployerUpdateRequest;
use Modules\Employer\Models\Employer;
use Modules\Notifications\Services\NotificationService;
use Modules\Users\Models\Role;
use Modules\Users\Models\User;
use Modules\Wallet\Models\Wallet;

class EmployerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 1000);

        $employers = Employer::with(['user'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'لیست کارفرمایان',
            'data'    => $employers
        ]);
    }

    // Show single article
    public function show($id)
    {
        $employer = Employer::with(['user'])->find($id);
        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'جزئیات کارفرما',
            'data'    => $employer
        ]);
    }

    // Store new article
    public function store(EmployerStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        if ($request->hasFile('bussines_logo')) {
            $bussines_logo = $request->file('bussines_logo')->store('employers/bussines_logo', 'public');
            $data['bussines_logo'] = $bussines_logo;
        }
        $data['password'] = Hash::make($data['password']);
        $user = User::create([
            'full_name' => $data['full_name'],
            'mobile' => $data['mobile'],
            'password' => $data['password'],
        ]);
        $employer = Employer::create([
            'bussines_logo' => $data['bussines_logo'],
            'bussines_label' => $data['bussines_label'],
            'user_id' => $user->id
        ]);
        $employerRoleId = Role::where('slug', 'employer')->value('id');
        if (!$employerRoleId) {
            return response()->json([
                'message' => 'نقش پیشفرض مشتری وجود ندارد لطفا این نقش را در دیتابیس تعریف کنید'
            ], 422);
        }
        Wallet::create([
            'user_id' => $user->id,
            'balance' =>  0,
        ]);
        $user->roles()->sync([$employerRoleId]);
        $notifications->create(
            " ثبت  کارفرما",
            " کارفرما  {$employer->user->full_name}در سیستم ثبت  شد",
            "notification_employer",
            ['employer' => $employer->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'کارفرما با موفقیت ثبت شد',
            'data'    => $employer
        ], 201);
    }


    // Update article
    public function update(EmployerUpdateRequest $request, $id, NotificationService $notifications)
    {
        $employer = Employer::findOrFail($id);
        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }
        $data = $request->validated();
        if ($request->hasFile('bussines_logo')) {
            if ($employer->bussines_logo) {
                Storage::disk('public')->delete($employer->bussines_logo);
            }
            $bussines_logo = $request->file('bussines_logo')->store('employers/bussines_logo', 'public');
            $data['bussines_logo'] = $bussines_logo;
        }
        $user = $employer->user();

        $userData['full_name'] = $data['full_name'];
        $userData['mobile'] = $data['mobile'];
        if ($request->has('password')) {
            $userData['password'] = Hash::make($data['password']);
        }
        $user->update($userData);
        $employer->update($data);
        $notifications->create(
            " ویرایش  کارفرما",
            " کارفرما  {$employer->user->full_name}در سیستم ویرایش  شد",
            "notification_employer",
            ['employer' => $employer->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'کارفرما با موفقیت ویرایش شد',
            'data'    => $employer
        ]);
    }


    // Delete article
    public function destroy($id, NotificationService $notifications)
    {
        $employer = Employer::find($id);

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'کارفرما پیدا نشد',
            ], 404);
        }

        if ($employer->bussines_logo) {
            Storage::disk('public')->delete($employer->bussines_logo);
        }

        $notifications->create(
            " حذف  کارفرما",
            " کارفرما  {$employer->user->full_name}از سیستم حذف  شد",
            "notification_employer",
            ['employer' => $employer->id]
        );
        User::first($employer->user_id)->delete();
        $employer->delete();

        return response()->json([
            'success' => true,
            'message' => 'کارفرما با موفقیت حذف شد',
        ]);
    }
    public function EmployerComment(CommentStoreRequest $request, NotificationService $notifications)
    {
        $data = $request->validated();
        $user = User::where('id', $data['user_id'])->first();
        $hasOld = Comment::where('user_id', $data['user_id'])->where('commentable_type', $data['commentable_type'])->first();
        if ($hasOld) {
            $hasOld->update($data);
            $notifications->create(
                " ثبت نظر کارفرما",
                " نظر کارفرما {$user->full_name}در سیستم ویرایش  شد",
                "notification_employer",
                ['comment' => $hasOld->id]
            );
            return response()->json([
                'success' => true,
                'message' => 'نظر کارفرما با موفقیت ویرایش شد',
                'data'    => $hasOld
            ], 201);
        }
        $savedComment = Comment::create($data);
        $notifications->create(
            " ثبت نظر کارفرما",
            " نظر کارفرما {$user->full_name}در سیستم ثبت  شد",
            "notification_employer",
            ['comment' => $savedComment->id]
        );
        return response()->json([
            'success' => true,
            'message' => 'نظر کارفرما با موفقیت ثبت شد',
            'data'    => $savedComment
        ], 201);
    }
}
