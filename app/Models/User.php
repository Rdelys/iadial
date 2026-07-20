<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
    'vapi_public_key',
    'vapi_assistant_id',
    'booking_slug',
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
     * Rendez-vous réels pris via l'assistant du client.
     */
    public function appointments()
    {
        return $this->hasMany(IarecepAppointment::class);
    }

    /**
     * Indique si l'abonnement du client est actif.
     */
    public function isSubscribed(): bool
    {
        return $this->subscription_status === 'active';
    }

    /**
     * Indique si l'admin a configuré un assistant Vapi pour ce client.
     */
    public function hasVapiAssistant(): bool
    {
        return filled($this->vapi_assistant_id) && filled($this->vapi_public_key);
    }

    /**
     * Génère (si nécessaire) et retourne le slug public de réservation.
     */
    public function bookingSlug(): string
    {
        if (! $this->booking_slug) {
            $base = Str::slug($this->company_name ?: $this->name) ?: 'client';
            $this->booking_slug = $base . '-' . Str::lower(Str::random(6));
            $this->save();
        }

        return $this->booking_slug;
    }

    /**
     * URL publique de réservation en lecture seule.
     */
    public function bookingUrl(): string
    {
        return route('public.booking', $this->bookingSlug());
    }
}