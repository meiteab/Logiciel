<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'profil_id',
        'statut',
        'email_verified_at',
        'last_login_at',
        'two_factor_enabled',
        'two_factor_secret',
        'locked_until',
        'failed_login_attempts',
    ];

    /**
     * Les attributs qui doivent être cachés lors de la sérialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Obtenir les attributs qui doivent être convertis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Obtenir le profil de l'utilisateur.
     */
    public function profil()
    {
        return $this->belongsTo(Profil::class);
    }

    /**
     * Obtenir l'enregistrement du personnel de l'utilisateur.
     */
    public function personnel()
    {
        return $this->hasOne(Personnel::class, 'user_id');
    }

    /**
     * Vérifier si l'utilisateur est verrouillé.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Vérifier si l'utilisateur est actif.
     */
    public function isActive(): bool
    {
        return $this->statut === 'actif';
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->profil && $this->profil->permissions_specifiques && 
               in_array($permission, $this->profil->permissions_specifiques);
    }
}
