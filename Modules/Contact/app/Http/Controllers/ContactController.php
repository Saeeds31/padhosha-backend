<?php

namespace Modules\Contact\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Modules\Contact\Models\Contact;
use Modules\Notifications\Services\NotificationService;

class ContactController extends Controller
{
    public function frontContact(Request $request, NotificationService $notifications)
    {
        $user = $request->user();
        $validated_data = $request->validate([
            'full_name' => $user ? 'nullable' : 'required|string|min:6',
            'mobile' => $user ? 'nullable' : 'required|string|size:11',
            'email' =>  'nullable|email',
            'subject' => 'required|string|min:6',
            'body' => 'required|string|min:10',
        ]);
        if ($user) {
            $validated_data['full_name'] = $user->full_name;
            $validated_data['mobile'] = $user->mobile;
        }
        $contact = Contact::create($validated_data);
        $notifications->create(
            " ثبت  فرم ارتباط",
            " یک فرم ارتباط با موضوع  {$contact->subject}در سیستم ثبت  شد",
            "notifications_user",
            ['contact' => $contact->id]
        );
        $smsService = new SmsService();
        $smsService->sendToKavenegar('contact', $validated_data["mobile"], $contact->id, ['token20' => $validated_data["subject"]]);
        return response()->json(
            [
                'message' => 'پیام شما با موفقیت ثبت شد ',
                'success' => true
            ]
        );
    }
    public function index()
    {
        $contacts = Contact::latest()->paginate(15);
        return response()->json([
            'data' => $contacts,
            'success' => true,
            'message' => 'لیست درخواست ها'
        ]);
    }
}
