<?php

namespace App\Jobs;

use App\Enums\NotificationRecipientsTypesEnum;
use App\Models\PushNotification;
use App\Services\OneSignalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected PushNotification $notification)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->notification->is_sent) {
                return;
            }
            $is_sent = false;
            // Selected users
            if (is_array($this->notification->recipients) && $this->notification->recipients_type == NotificationRecipientsTypesEnum::SELECTED->value) {
                $onesignal_push = OneSignalService::sendPushByUserIds(
                    $this->notification->recipients,
                    $this->notification->title,
                    $this->notification->content,
                    $this->notification->image
                );
                if (!isset($onesignal_push['errors'])) {
                    $is_sent = true;
                }
            }
            // All users
            if ($this->notification->recipients_type == NotificationRecipientsTypesEnum::ALL->value) {
                $onesignal_push = OneSignalService::sendPushToAll(
                    $this->notification->title,
                    $this->notification->content,
                    $this->notification->image
                );

                if (!isset($onesignal_push['errors'])) {
                    $is_sent = true;
                }
            }

            $this->notification->update(['is_sent' => $is_sent]);
        } catch (\Throwable $th) {
            Log::channel('push_notifications')->error('Push Notification Error: ' . $th->getMessage(), [
                'exception' => $th,
                '$push' => $this->notification,
            ]);
        }
    }
}
