<?php

namespace App;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;

/**
 * Class User
 * @package App
 * @mixin Builder
 *
 * @property string name
 * @property string email
 * @property string password
 */
class User extends AuthUser implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens, Notifiable;

    private $token;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name - token name
     * @param array $scopes
     * @return PersonalAccessTokenResult
     * @throws BindingResolutionException
     */
    public function createAndSetToken($name, array $scopes = [])
    {
        $token = Container::getInstance()->make(PersonalAccessTokenFactory::class)->make(
            $this->getKey(), $name, $scopes
        );
        $this->token = $token;

        return $token;
    }

    public function getTokenAttribute()
    {
        return ($this->token) ? $this->token->accessToken : null;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }
}
