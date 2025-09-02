<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profil extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profils';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'statut',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array',
        'statut' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec les rôles (many-to-many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'profils_roles', 'profil_id', 'role_id');
    }

    /**
     * Vérifier si le profil est actif
     */
    public function isActive(): bool
    {
        return $this->statut === true;
    }

    /**
     * Activer le profil
     */
    public function activate(): void
    {
        $this->update(['statut' => true]);
    }

    /**
     * Désactiver le profil
     */
    public function deactivate(): void
    {
        $this->update(['statut' => false]);
    }

    /**
     * Scope pour les profils actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour les profils inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('statut', false);
    }
}
