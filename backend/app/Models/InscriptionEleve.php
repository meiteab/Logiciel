<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscriptionEleve extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscriptions_eleves';

    protected $fillable = [
        'eleve_id',
        'classe_francaise_id',
        'classe_arabe_id',
        'annee_scolaire_id',
        'date_inscription',
        'date_sortie',
        'type_inscription',
        'statut',
        'motif_sortie',
        'notes_administratives'
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'date_sortie' => 'date'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec l'élève
     */
    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }

    /**
     * Relation avec la classe française
     */
    public function classeFrancaise()
    {
        return $this->belongsTo(Classe::class, 'classe_francaise_id');
    }

    /**
     * Relation avec la classe arabe
     */
    public function classeArabe()
    {
        return $this->belongsTo(Classe::class, 'classe_arabe_id');
    }

    /**
     * Relation avec l'année scolaire
     */
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    /**
     * Vérifier si l'inscription est active
     */
    public function isActive(): bool
    {
        return $this->statut === 'inscrit';
    }

    /**
     * Vérifier si l'inscription est en redoublement
     */
    public function isRedoublement(): bool
    {
        return $this->statut === 'redoublement';
    }

    /**
     * Vérifier si l'inscription est un transfert
     */
    public function isTransfert(): bool
    {
        return $this->statut === 'transfert';
    }

    /**
     * Vérifier si l'élève est sorti
     */
    public function isSortie(): bool
    {
        return $this->statut === 'sortie';
    }

    /**
     * Vérifier si l'élève est suspendu
     */
    public function isSuspendu(): bool
    {
        return $this->statut === 'suspendu';
    }

    /**
     * Activer l'inscription
     */
    public function activate(): void
    {
        $this->update(['statut' => 'inscrit']);
    }

    /**
     * Marquer comme redoublement
     */
    public function setRedoublement(): void
    {
        $this->update(['statut' => 'redoublement']);
    }

    /**
     * Marquer comme transfert
     */
    public function setTransfert(): void
    {
        $this->update(['statut' => 'transfert']);
    }

    /**
     * Marquer comme sortie
     */
    public function setSortie(): void
    {
        $this->update(['statut' => 'sortie']);
    }

    /**
     * Suspendre l'inscription
     */
    public function suspendre(): void
    {
        $this->update(['statut' => 'suspendu']);
    }

    /**
     * Obtenir le statut en français
     */
    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'inscrit' => 'Inscrit',
            'redoublement' => 'Redoublement',
            'transfert' => 'Transfert',
            'sortie' => 'Sortie',
            'suspendu' => 'Suspendu',
            default => 'Inconnu'
        };
    }

    /**
     * Scope pour les inscriptions actives
     */
    public function scopeActives($query)
    {
        return $query->where('statut', 'inscrit');
    }

    /**
     * Scope pour les redoublements
     */
    public function scopeRedoublements($query)
    {
        return $query->where('statut', 'redoublement');
    }

    /**
     * Scope pour les transferts
     */
    public function scopeTransferts($query)
    {
        return $query->where('statut', 'transfert');
    }

    /**
     * Scope pour les sorties
     */
    public function scopeSorties($query)
    {
        return $query->where('statut', 'sortie');
    }

    /**
     * Scope pour les suspensions
     */
    public function scopeSuspendus($query)
    {
        return $query->where('statut', 'suspendu');
    }

    /**
     * Scope pour une année scolaire spécifique
     */
    public function scopeAnneeScolaire($query, $anneeScolaireId)
    {
        return $query->where('annee_scolaire_id', $anneeScolaireId);
    }

    /**
     * Scope pour une classe française spécifique
     */
    public function scopeClasseFrancaise($query, $classeId)
    {
        return $query->where('classe_francaise_id', $classeId);
    }

    /**
     * Scope pour une classe arabe spécifique
     */
    public function scopeClasseArabe($query, $classeId)
    {
        return $query->where('classe_arabe_id', $classeId);
    }

    /**
     * Scope pour un élève spécifique
     */
    public function scopeEleve($query, $eleveId)
    {
        return $query->where('eleve_id', $eleveId);
    }
}
