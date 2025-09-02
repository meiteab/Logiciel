<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'prenom',
        'nom_famille',
        'date_naissance',
        'lieu_naissance',
        'genre',
        'adresse',
        'telephone',
        'email',
        'profession',
        'statut',
        'photo',
        'informations_supplementaires'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'statut' => 'boolean',
        'informations_supplementaires' => 'array'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec les élèves (many-to-many)
     */
    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'eleves_parents', 'parent_id', 'eleve_id');
    }

    /**
     * Vérifier si le parent est actif
     */
    public function isActive(): bool
    {
        return $this->statut === true;
    }

    /**
     * Activer le parent
     */
    public function activate(): void
    {
        $this->update(['statut' => true]);
    }

    /**
     * Désactiver le parent
     */
    public function deactivate(): void
    {
        $this->update(['statut' => false]);
    }

    /**
     * Obtenir le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom_famille);
    }

    /**
     * Scope pour les parents actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour les parents inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('statut', false);
    }
}
