<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Eleve extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eleves';

    protected $fillable = [
        'matricule',
        'prenom',
        'nom_famille',
        'date_naissance',
        'lieu_naissance',
        'genre',
        'adresse',
        'telephone',
        'email',
        'photo',
        'statut',
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
     * Relation avec les parents (many-to-many)
     */
    public function parents()
    {
        return $this->belongsToMany(Parent::class, 'eleves_parents', 'eleve_id', 'parent_id');
    }

    /**
     * Relation avec les classes (via inscriptions)
     */
    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'inscriptions_eleves', 'eleve_id', 'classe_id');
    }

    /**
     * Relation avec les inscriptions
     */
    public function inscriptions()
    {
        return $this->hasMany(InscriptionEleve::class);
    }

    /**
     * Vérifier si l'élève est actif
     */
    public function isActive(): bool
    {
        return $this->statut === true;
    }

    /**
     * Activer l'élève
     */
    public function activate(): void
    {
        $this->update(['statut' => true]);
    }

    /**
     * Désactiver l'élève
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
     * Scope pour les élèves actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour les élèves inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('statut', false);
    }

    /**
     * Scope pour filtrer par niveau
     */
    public function scopeParNiveau($query, $niveauId)
    {
        return $query->whereHas('classes', function ($q) use ($niveauId) {
            $q->where('niveau_id', $niveauId);
        });
    }
}
