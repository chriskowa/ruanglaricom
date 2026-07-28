<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationApiController extends BaseApiController
{
    /**
     * Register device FCM/APNS push notification token for mobile runner
     */
    public function registerDeviceToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string|max:500',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi device token gagal', 422, $validator->errors());
        }

        $audit = $user->audit_history ?? [];
        $audit['fcm_device_tokens'] = array_values(array_unique(array_merge(
            $audit['fcm_device_tokens'] ?? [],
            [$request->device_token]
        )));

        $user->update(['audit_history' => $audit]);

        return $this->successResponse([
            'platform' => $request->get('platform', 'android'),
            'registered' => true,
        ], 'Device Token FCM/APNS berhasil terdaftar');
    }

    /**
     * Get in-app notifications for runner
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ], 'Daftar notifikasi berhasil dimuat');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse(null, 'Semua notifikasi ditandai dibaca');
    }

    /**
     * Mark single notification as read
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notif = Notification::where('user_id', $user->id)->find($id);

        if (! $notif) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404);
        }

        $notif->update(['is_read' => true, 'read_at' => now()]);

        return $this->successResponse(null, 'Notifikasi ditandai dibaca');
    }
}
