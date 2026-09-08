<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all notifications for the authenticated user (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            data: $notifications,
            message: 'Notifications retrieved successfully.'
        );
    }

    /**
     * List only unread notifications for the authenticated user.
     */
    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();

        return $this->successResponse(
            data: [
                'unread_count' => $notifications->count(),
                'notifications' => $notifications,
            ],
            message: 'Unread notifications retrieved successfully.'
        );
    }

    /**
     * Mark a specific notification as read.
     * Ensures the notification belongs to the authenticated user.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->find($id);

        if (! $notification) {
            return $this->errorResponse(
                message: 'Notification not found.',
                code: 404
            );
        }

        $notification->markAsRead();

        return $this->successResponse(
            data: $notification,
            message: 'Notification marked as read.'
        );
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->successResponse(
            message: 'All notifications marked as read.'
        );
    }

    /**
     * Delete a specific notification.
     * Ensures the notification belongs to the authenticated user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->find($id);

        if (! $notification) {
            return $this->errorResponse(
                message: 'Notification not found.',
                code: 404
            );
        }

        $notification->delete();

        return $this->successResponse(
            message: 'Notification deleted successfully.'
        );
    }
}
