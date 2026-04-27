<?php

namespace Modules\Employer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Comments\Http\Requests\CommentStoreRequest;
use Modules\Comments\Models\Comment;
use Modules\Employer\Http\Requests\CostStoreRequest;
use Modules\Employer\Http\Requests\DepositStoreRequest;
use Modules\Employer\Http\Requests\EmployerStoreRequest;
use Modules\Employer\Http\Requests\EmployerUpdateRequest;
use Modules\Employer\Models\Cost;
use Modules\Employer\Models\Deposit;
use Modules\Employer\Models\Employer;
use Modules\Notifications\Services\NotificationService;
use Modules\Ticket\Models\Message;
use Modules\Ticket\Models\Subscription;
use Modules\Ticket\Models\Ticket;
use Modules\Users\Models\Role;
use Modules\Users\Models\User;
use Modules\Wallet\Models\Wallet;

class EmployerController extends Controller
{
    public function messageStore(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'message' => 'required|string|min:3',
            'file' => 'nullable|file|max:4096',
            'voice' => 'nullable|file|max:4096',
            'ticket_id' => 'required|integer'
        ]);
        $ticket = Ticket::findOrFail($validated['ticket_id']);
        if ($ticket->sender_id != $user->id) {
            return response()->json([
                'message' => 'شما اجازه پاسخ دادن به این تیکت را ندارید',
            ], 403);
        }
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('tickets', 'public');
            $validated['file'] = $path;
        }
        
        if ($request->hasFile('voice')) {
            $path = $request->file('voice')->store('tickets', 'public');
            $validated['voice'] = $path;
        }
        $message = Message::create([
            'message' => $validated['message'],
            'attachment' => $validated['file'],
            'voice' => $validated['voice'],
            'sender_side' => 'padhosha',
            'sender_id' => $user->id,
            'ticket_id' => $ticket->id
        ]);
        $ticket->update([
            'status' => 'pending'
        ]);
        return response()->json([
            'message' => 'پیام با موفقیت ثبت شد',
            'data' => $message,
        ]);
    }
    public function ticketDetail(Request $request, $id)
    {
        $ticket = Ticket::with(['doer', 'sender'])->findOrFail($id);
        $messages = Message::with(['sender'])->where('ticket_id', $ticket->id)->latest()->get();
        $cost = Cost::where('costable_type', 'Ticket')->where('costable_id', $ticket->id)->first();
        return response()->json([
            'message' => ' اطلاعات تیکت ',
            'data' => [
                'ticket' => $ticket,
                'cost' => $cost,
                'messages' => $messages
            ],
        ]);
    }
    public function receipt(DepositStoreRequest $request, NotificationService $notifications)
    {
        $validated = $request->validated();
        $user = $request->user();
        $employer = Employer::where('user_id', $user->id)->first();
        $validated['status'] = 'pending';
        $validated['employer_id'] = $employer->id;
        $validated['admin_note'] = '';

        $deposit = Deposit::create($validated);
        $notifications->create(
            " پرداختی  کارفرما",
            " یک رسید از کارفرما  {$employer->bussines_label}در سیستم ثبت  شد",
            "notification_employer",
            ['employer' => $employer->id]
        );
        return response()->json([
            'message' => 'با موفقیت ثبت شد',
            'deposit' => $deposit,
        ]);
    }
    public function EmployerCostStore(CostStoreRequest $request, NotificationService $notifications)
    {
        $validated = $request->validated();
        $cost = Cost::create($validated);
        $notifications->create(
            " هزینه  کارفرما",
            " یک هزینه برای کارفرما  {$cost->employer_id}در سیستم ثبت  شد",
            "notification_employer",
            ['employer' => $cost->employer_id]
        );
        return response()->json([
            'message' => 'با موفقیت ثبت شد',
            'cost' => $cost,
        ]);
    }
    public function EmployerUpdateDeposit(Request $request, $id, NotificationService $notifications)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'admin_note' => 'nullable|string',
        ]);
        $deposit = Deposit::findOrFail($id);
        $deposit->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'],
        ]);
        $notifications->create(
            " ویرایش رسید کارفرما",
            " یک رسید برای کارفرما با عنوان  {$deposit->title}در سیستم ویرایش  شد",
            "notification_employer",
            ['employer' => $deposit->employer_id]
        );
        return response()->json([
            'message' => 'با موفقیت ویرایش شد',
            'cost' => $deposit,
        ]);
    }

    public function EmployerDepositIndex(Request $request)
    {

        $query = Deposit::query();
        if ($amount = $request->get('amount')) {
            $query->where('amount', '>=', $amount);
        }
        if ($employer_id = $request->get('employer_id')) {
            $query->where('employer_id',  $employer_id);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        $deposit = $query->latest()->paginate(10);
        return response()->json([
            'message' => 'با موفقیت لیست شد',
            'data' => $deposit,
        ]);
    }

    public function EmployerCost(Request $request, $employerId)
    {
        $employer = Employer::findOrFail($employerId);
        $query = Cost::query();
        if ($amount = $request->get('amount')) {
            $query->where('amount', '>=', $amount);
        }
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        $query->where('employer_id',$employerId);
        $total = $query->sum('amount');
        $data = $query->paginate(20);
        return response()->json([
            'message' => 'اطلاعات مالی کارفرما',
            'employer' => $employer,
            'data' => $data,
            'total' => $total
        ]);
    }
    public function cost(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }

        $employer = Employer::where('user_id', $user->id)->first();
        if (!$employer) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }
        $query = Cost::query();
        if ($amount = $request->get('amount')) {
            $query->where('amount', '>=', $amount);
        }
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        $total = $query->sum('amount');
        $data = $query->latest()->paginate(20);
        return response()->json([
            'message' => 'اطلاعات مالی کارفرما',
            'total' => $total,
            'data' => $data
        ]);
    }
    public function deposit(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }

        $employer = Employer::where('user_id', $user->id)->first();
        if (!$employer) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }
        $query = Deposit::query();
        if ($amount = $request->get('amount')) {
            $query->where('amount', '>=', $amount);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $total = (clone $query)->where('status', 'accepted')->sum('amount');
        $data = $query->latest()->paginate(20);
        return response()->json([
            'message' => 'اطلاعات مالی کارفرما',
            'total' => $total,
            'data' => $data
        ]);
    }
    public function dashboard(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }

        $employer = Employer::where('user_id', $user->id)->first();
        if (!$employer) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }
        $subscription = Subscription::where('employer_id', $employer->id)->first();
        $active = $subscription && $subscription->expiration_date->greaterThanOrEqualTo(now()->today());
        $totalCost = Cost::where('employer_id', $employer->id)->sum('amount');
        $totalPay = Deposit::where('employer_id', $employer->id)->where('status', 'accpeted')->sum('amount');
        $today = Carbon::now();
        $oneMonthAgo = $today->subMonth();
        $totalMonth = Deposit::where('employer_id', $employer->id)
            ->where('status', 'accepted')
            ->whereDate('created_at', '>=', $oneMonthAgo)
            ->whereDate('created_at', '<=', $today)
            ->sum('amount');
        $totalDebt = $totalPay - $totalCost;
        $totalTicket = Ticket::where('sender_id', $user->id)->count();
        $openTicket = Ticket::where('sender_id', $user->id)->where('status', '!=', 'closed')->count();
        $closeTicket = Ticket::where('sender_id', $user->id)->where('status', 'closed')->count();
        $ticket = [
            'totalTicket' => $totalTicket,
            'closeTicket' => $closeTicket,
            'openTicket' => $openTicket,
        ];
        return response()->json([
            'message' => 'اطلاعات کارفرما',
            'user' => $user,
            'active' => $active,
            'totalDebt' => $totalDebt,
            'totalMonth' => $totalMonth,
            'ticket' => $ticket,
            'totalPay' => $totalPay,
            'employer' => $employer,
            'subscription' => $subscription
        ]);
    }
    public function info(Request $request)
    {
        $user = $request->user();
        $user['employer'] = Employer::where('user_id', $user->id)->first();
        $permissions = $user->permissions;

        return response()->json([
            'message' => 'اطلاعات کارفرما',
            'user' => $user,
            'permissions' => $permissions
        ]);
    }
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 1000);

        $employers = Employer::with(['user', 'subscription'])->paginate($perPage);

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
    public function EmployerSubscription(Request $request)
    {
        $validated_data = $request->validate([
            'start_date' => 'required|date',
            'expiration_date' => 'required|date',
            'level_type' => 'required|string',
            'employer_id' => 'required|integer'
        ]);
        $exist = Subscription::where('employer_id', $validated_data['employer_id'])->first();
        if ($exist) {
            $exist->update([
                'expiration_date' => $validated_data['expiration_date'],
                'level_type' => $validated_data['level_type'],
            ]);
            return response()->json([
                'success' => true,
                'message' => 'پشتیبانی کارفرما با موفقیت ویرایش شد',
            ]);
        } else {
            Subscription::create($validated_data);
            return response()->json([
                'success' => true,
                'message' => 'پشتیبانی کارفرما با موفقیت ثبت شد',
            ]);
        }
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
