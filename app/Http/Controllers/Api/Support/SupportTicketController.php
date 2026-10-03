<?php

namespace App\Http\Controllers\Api\Support;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends BaseApiController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'body' => ['required', 'string', 'min:10', 'max:8000'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), 422, $validator->errors());
        }

        $data = $validator->validated();
        $user = $request->user('sanctum');

        $ticket = SupportTicket::create([
            'user_id' => $user?->id,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => SupportTicketStatus::Open,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return $this->successResponse([
            'id' => $ticket->id,
            'status' => $ticket->status->value,
            'created_at' => $ticket->created_at?->toIso8601String(),
        ], __('messages.support_ticket_created'), 201);
    }
}
