<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $messages = collect();
        $stats = [
            'total' => 0,
            'unread' => 0,
            'read' => 0,
        ];

        if (Schema::hasTable('contact_messages')) {
            $query = ContactMessage::query()->latest();

            if ($status === 'unread') {
                $query->where('is_read', false);
            } elseif ($status === 'read') {
                $query->where('is_read', true);
            }

            $messages = $query->paginate(15)->withQueryString();

            $stats = [
                'total' => ContactMessage::count(),
                'unread' => ContactMessage::where('is_read', false)->count(),
                'read' => ContactMessage::where('is_read', true)->count(),
            ];
        }

        return Inertia::render('tenant/contact-messages/Index', [
            'messages' => $messages,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
            ],
            'contact_messages_table_ready' => Schema::hasTable('contact_messages'),
        ]);
    }

    public function markRead(ContactMessage $contactMessage)
    {
        $contactMessage->markRead();

        return back()->with('success', 'Contact message marked as read.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return back()->with('success', 'Contact message deleted.');
    }
}
