<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendEmailVerification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /**
         * Ensure that the user model implements email verification,
         * and that the user's email has not been verified yet
         */
        if ($this->user instanceof MustVerifyEmail && !$this->user->hasVerifiedEmail()) {

            // Send the email verification
            $this->user->sendEmailVerificationNotification();
        }
    }
}
