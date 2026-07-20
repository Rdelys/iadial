<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'company_name',
    'sector',
    'address',
    'city',
    'plan',
    'plan_label',
    'subscription_status',
    'subscribed_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Paiements associés à ce compte.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Indique si l'abonnement du client est actif.
     */
    public function isSubscribed(): bool
    {
        return $this->subscription_status === 'active';
    }
}