<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnneeScolaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'annees_scolaires';

    protected $fillable = [
        'annee_debut',
        'annee_fin',
        'nom',
        'date_debut',
        'date_fin',
        'statut',
        'est_courante'
    ];

    protected $casts = [
        'annee_debut' => 'integer',
        'annee_fin' => 'integer',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'statut' => 'boolean',
        'est_courante' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec les classes
     */
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    /**
     * Relation avec les inscriptions
     */
    public function inscriptions()
    {
        return $this->hasMany(InscriptionEleve::class);
    }

    /**
     * Vérifier si l'année scolaire est active
     */
    public function isActive(): bool
    {
        return $this->statut === true;
    }

    /**
     * Vérifier si c'est l'année scolaire courante
     */
    public function isCurrent(): bool
    {
        return $this->est_courante === true;
    }

    /**
     * Activer l'année scolaire
     */
    public function activate(): void
    {
        $this->update(['statut' => true]);
    }

    /**
     * Désactiver l'année scolaire
     */
    public function deactivate(): void
    {
        $this->update(['statut' => false]);
    }

    /**
     * Définir comme année scolaire courante
     */
    public function setAsCurrent(): void
    {
        // Désactiver toutes les autres années scolaires
        static::where('est_courante', true)->update(['est_courante' => false]);
        
        // Activer cette année scolaire
        $this->update(['est_courante' => true]);
    }

    /**
     * Scope pour les années scolaires actives
     */
    public function scopeActives($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour l'année scolaire courante
     */
    public function scopeCourante($query)
    {
        return $query->where('est_courante', true);
    }
}
