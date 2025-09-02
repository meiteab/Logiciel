<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscriptionFinanciere extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inscriptions_financieres';

    protected $fillable = [
        'eleve_id',
        'annee_scolaire_id',
        'montant_total',
        'montant_paye',
        'date_inscription',
        'statut',
        'mode_paiement',
        'echeances',
        'observations'
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'montant_total' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'echeances' => 'array'
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
     * Relation avec l'année scolaire
     */
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    /**
     * Vérifier si l'inscription est validée
     */
    public function isValidee(): bool
    {
        return $this->statut === 'validee';
    }

    /**
     * Vérifier si l'inscription est en attente
     */
    public function isEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    /**
     * Vérifier si l'inscription est refusée
     */
    public function isRefusee(): bool
    {
        return $this->statut === 'refusee';
    }

    /**
     * Vérifier si l'inscription est annulée
     */
    public function isAnnulee(): bool
    {
        return $this->statut === 'annulee';
    }

    /**
     * Calculer le solde restant
     */
    public function getSoldeRestantAttribute(): float
    {
        return $this->montant_total - $this->montant_paye;
    }

    /**
     * Calculer le pourcentage payé
     */
    public function getPourcentagePayeAttribute(): float
    {
        if ($this->montant_total <= 0) {
            return 0;
        }
        return round(($this->montant_paye / $this->montant_total) * 100, 2);
    }

    /**
     * Obtenir le statut de paiement
     */
    public function getStatutPaiementAttribute(): string
    {
        if ($this->solde_restant <= 0) {
            return 'paye';
        }
        return $this->montant_paye > 0 ? 'partiel' : 'impaye';
    }

    /**
     * Valider l'inscription
     */
    public function valider(): void
    {
        $this->update(['statut' => 'validee']);
    }

    /**
     * Refuser l'inscription
     */
    public function refuser(): void
    {
        $this->update(['statut' => 'refusee']);
    }

    /**
     * Annuler l'inscription
     */
    public function annuler(): void
    {
        $this->update(['statut' => 'annulee']);
    }

    /**
     * Mettre en attente l'inscription
     */
    public function mettreEnAttente(): void
    {
        $this->update(['statut' => 'en_attente']);
    }

    /**
     * Obtenir le statut en français
     */
    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'validee' => 'Validée',
            'refusee' => 'Refusée',
            'annulee' => 'Annulée',
            default => 'Inconnu'
        };
    }

    /**
     * Scope pour les inscriptions validées
     */
    public function scopeValidees($query)
    {
        return $query->where('statut', 'validee');
    }

    /**
     * Scope pour les inscriptions en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les inscriptions refusées
     */
    public function scopeRefusees($query)
    {
        return $query->where('statut', 'refusee');
    }

    /**
     * Scope pour les inscriptions annulées
     */
    public function scopeAnnulees($query)
    {
        return $query->where('statut', 'annulee');
    }

    /**
     * Scope pour une année scolaire spécifique
     */
    public function scopeAnneeScolaire($query, $anneeScolaireId)
    {
        return $query->where('annee_scolaire_id', $anneeScolaireId);
    }

    /**
     * Scope pour un élève spécifique
     */
    public function scopeEleve($query, $eleveId)
    {
        return $query->where('eleve_id', $eleveId);
    }

    /**
     * Scope pour les inscriptions impayées
     */
    public function scopeImpayees($query)
    {
        return $query->whereRaw('montant_paye < montant_total');
    }

    /**
     * Scope pour les inscriptions partiellement payées
     */
    public function scopePartiellementPayees($query)
    {
        return $query->whereRaw('montant_paye > 0 AND montant_paye < montant_total');
    }

    /**
     * Scope pour les inscriptions entièrement payées
     */
    public function scopeEntierementPayees($query)
    {
        return $query->whereRaw('montant_paye >= montant_total');
    }
}
