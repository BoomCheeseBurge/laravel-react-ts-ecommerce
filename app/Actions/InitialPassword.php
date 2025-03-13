<?php

namespace App\Actions;

use App\Notifications\sendInitialPasswordNotification;
use Closure;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Support\Facades\Notification;

class InitialPassword extends Password
{
    /**
     * The password token repository.
     *
     * @var \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new password broker instance.
     *
     * @param  \Illuminate\Auth\Passwords\TokenRepositoryInterface  $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @param  \Illuminate\Contracts\Events\Dispatcher|null  $dispatcher
     * @return void
     */
    public function __construct(#[\SensitiveParameter] TokenRepositoryInterface $tokens, UserProvider $users, ?Dispatcher $dispatcher = null)
    {
        $this->users = $users;
        $this->tokens = $tokens;
        $this->events = $dispatcher;
    }

    /**
     * Invoke the class instance.
     */
    public function __invoke(#[\SensitiveParameter] array $credentials, ?Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return Password::INVALID_USER;
        }

        if ($this->tokens->recentlyCreatedToken($user)) {
            return Password::RESET_THROTTLED;
        }

        $token = $this->tokens->create($user);

        if ($callback) {
            return $callback($user, $token) ?? static::INITIAL_LINK_SENT;
        }

        // Once we have the reset token, we are ready to send the message out to this
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        Notification::send($user, new sendInitialPasswordNotification($token));
        
        return static::INITIAL_LINK_SENT;
    }
}
