<?php

namespace Modules\Ticket\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Modules\Employer\Models\Cost;
use Modules\Employer\Models\Employer;
use Modules\Notifications\Services\NotificationService;
use Modules\Ticket\Models\Message;
use Modules\Ticket\Models\Ticket;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'message' => 'required|string|min:3',
            'file' => 'nullable|file|max:4096',
            'voice' => 'nullable|file|max:4096',
            'ticket_id' => 'required|integer'
        ]);
        $ticket = Ticket::findOrFail($validated['ticket_id']);
        if (!empty($ticket->doer_id) && ($ticket->doer_id != $user->id)) {
            return response()->json([
                'message' => 'شما اجازه پاسخ دادن به این تیکت را ندارید',
            ], 403);
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
            'status' => 'answered'
        ]);
        return response()->json([
            'message' => 'پیام با موفقیت ثبت شد',
            'data' => $message,
        ]);
    }
    public function index(Request $request)
    {
        $query = Ticket::query()->with('doer', 'sender');
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        $closedTotal = (clone $query)->where('status', 'closed')->count();
        $openedTotal = (clone $query)->where('status', '!=', 'closed')->count();
        $tickets = $query->latest()->paginate(20);
        return response()->json([
            'message' => 'لیست تیکت ها',
            'data' => $tickets,
            'closedTotal' => $closedTotal,
            'openedTotal' => $openedTotal,
        ]);
    }
    public function EmployerIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return  response()->json([
                'message' => 'لطفا ابتدا لاگین کنید',
            ], 401);
        }
        $query = Ticket::query();
        $query->where('sender_id', $user->id);
        if ($dateFrom = $request->get('dateFrom')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($dateTo = $request->get('dateTo')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        $closedTotal = (clone $query)->where('status', 'closed')->count();
        $openedTotal = (clone $query)->where('status', '!=', 'closed')->count();
        $tickets = $query->latest()->paginate(20);
        return response()->json([
            'message' => 'لیست تیکت ها',
            'data' => $tickets,
            'closedTotal' => $closedTotal,
            'openedTotal' => $openedTotal,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function EmployerStoreTicket(Request $request, NotificationService $notifications)
    {
        $user = $request->user();
        $validated = $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:10',
            'file' => 'nullable|file|max:1024',
            'voice' => 'nullable|file|max:4096',

        ]);
        $ticket = Ticket::create([
            'title' => $validated['title'],
            'status' => 'pending',
            'sender_id' => $user->id
        ]);
        $message = Message::create([
            'message' => $validated['description'],
            'attachment' => $validated['file'],
            'voice' => $validated['voice'],
            'sender_side' => 'employer',
            'sender_id' => $user->id,
            'ticket_id' => $ticket->id
        ]);
        $notifications->create(
            "تیکت کارفرما",
            " یک تیکت از سمت کارفرما با موضوع  {$ticket->title}در سیستم ثبت  شد",
            "notification_employer",
            ['ticket' => $ticket->id]
        );
        $smsService = new SmsService();
        $smsText = "کارفرمای گرامی تیکت شما در سیستم ثبت شد\n به زودی کارشناسان ما با شما ارتباط خواهند گرفت\n شرکت پدهوشا";
        $smsService->sendText($user->mobile, $smsText);
        $smsService = new SmsService();
        $smsText = "یک پیامک جدید در سیستم به ثبت رسید\n شرکت پدهوشا";
        $smsService->sendText("09113894304", $smsText);
        return response()->json([
            'message' => 'ثبت تیکت جدید',
            'data' => $ticket,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ticket::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
