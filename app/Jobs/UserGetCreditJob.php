<?php

namespace App\Jobs;

use App\Mail\MainMailTemplate;
use App\Mail\Users\NewUserRegisteredForAdmin;
use App\Mail\Users\WelcomeEmailForUser;
use App\Models\Mails\Mail as MailModel;
use App\Models\User as ModelsUser;
use App\Models\Users\User;
use App\Services\API\OneSignalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserGetCreditJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ModelsUser $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // Send Notification to the user when user gets credit from admin.
        // OneSignalService::sendPushByPhone(
        //     $this->user->phone,
        //     'You\'ve Got Credit!',
        //     'Congratulations! You\'ve got a games credit'
        // );

        // Send deactivation email to user.
        if ($this->user->email) {
            // $userMail = MailModel::where('slug', 'user-gets-credit')->first();
            // if ($userMail) {
            //     $variables = [
            //         'user_name' => $this->user->name,
            //         'amount' => $this->amount . ' KWD',
            //         'platform_name' => settings('platform_name'),
            //         'wallet_balance' => $this->user->balanceFloat . ' KWD',
            //         'support_email' => settings('support_email')
            //     ];
            //     Mail::to($this->user->email)->send(new MainMailTemplate($userMail, $variables));
            // }
        }
    }
}
