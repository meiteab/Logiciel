<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

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
     * Relation avec les profils (many-to-many)
     */
    public function profils()
    {
        return $this->belongsToMany(Profil::class, 'profils_roles', 'role_id', 'profil_id');
    }

    /**
     * Vérifier si le rôle est actif
     */
    public function isActive(): bool
    {
        return $this->statut === true;
    }

    /**
     * Activer le rôle
     */
    public function activate(): void
    {
        $this->update(['statut' => true]);
    }

    /**
     * Désactiver le rôle
     */
    public function deactivate(): void
    {
        $this->update(['statut' => false]);
    }

    /**
     * Scope pour les rôles actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour les rôles inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('statut', false);
    }
}
