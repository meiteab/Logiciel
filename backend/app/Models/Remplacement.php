<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remplacement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'remplacements';

    protected $fillable = [
        'absence_enseignant_id',
        'enseignant_remplacant_id',
        'classe_id',
        'matiere_id',
        'date_remplacement',
        'heure_debut',
        'heure_fin',
        'statut',
        'notes_remplacement',
        'contenu_cours',
        'valide_par_id',
        'date_validation',
        'commentaires_validation'
    ];

    protected $casts = [
        'date_remplacement' => 'date',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
        'date_validation' => 'datetime'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec l'absence de l'enseignant
     */
    public function absenceEnseignant()
    {
        return $this->belongsTo(AbsenceEnseignant::class, 'absence_enseignant_id');
    }

    /**
     * Relation avec l'enseignant remplaçant
     */
    public function enseignantRemplacant()
    {
        return $this->belongsTo(Personnel::class, 'enseignant_remplacant_id');
    }

    /**
     * Relation avec la classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Relation avec la matière
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }

    /**
     * Relation avec le personnel qui valide le remplacement
     */
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'validePar_id');
    }

    /**
     * Vérifier si le remplacement est planifié
     */
    public function isPlanifie(): bool
    {
        return $this->statut === 'planifie';
    }

    /**
     * Vérifier si le remplacement est confirmé
     */
    public function isConfirme(): bool
    {
        return $this->statut === 'confirme';
    }

    /**
     * Vérifier si le remplacement est annulé
     */
    public function isAnnule(): bool
    {
        return $this->statut === 'annule';
    }

    /**
     * Vérifier si le remplacement est terminé
     */
    public function isTermine(): bool
    {
        return $this->statut === 'termine';
    }

    /**
     * Confirmer le remplacement
     */
    public function confirmer(int $valideParId, ?string $commentaires = null): void
    {
        $this->update([
            'statut' => 'confirme',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'commentaires_validation' => $commentaires
        ]);
    }

    /**
     * Annuler le remplacement
     */
    public function annuler(int $valideParId, ?string $commentaires = null): void
    {
        $this->update([
            'statut' => 'annule',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'commentaires_validation' => $commentaires
        ]);
    }

    /**
     * Marquer comme terminé
     */
    public function terminer(int $valideParId, ?string $commentaires = null): void
    {
        $this->update([
            'statut' => 'termine',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'commentaires_validation' => $commentaires
        ]);
    }

    /**
     * Obtenir la durée du remplacement en heures
     */
    public function getDureeHeuresAttribute(): ?float
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return null;
        }

        $debut = \Carbon\Carbon::parse($this->heure_debut);
        $fin = \Carbon\Carbon::parse($this->heure_fin);
        
        return $debut->diffInMinutes($fin) / 60;
    }

    /**
     * Scope pour les remplacements planifiés
     */
    public function scopePlanifies($query)
    {
        return $query->where('statut', 'planifie');
    }

    /**
     * Scope pour les remplacements confirmés
     */
    public function scopeConfirmes($query)
    {
        return $query->where('statut', 'confirme');
    }

    /**
     * Scope pour les remplacements annulés
     */
    public function scopeAnnules($query)
    {
        return $query->where('statut', 'annule');
    }

    /**
     * Scope pour les remplacements terminés
     */
    public function scopeTermines($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope pour filtrer par date
     */
    public function scopeParDate($query, $date)
    {
        return $query->where('date_remplacement', $date);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_remplacement', [$dateDebut, $dateFin]);
    }

    /**
     * Scope pour filtrer par enseignant remplaçant
     */
    public function scopeParEnseignantRemplacant($query, $personnelId)
    {
        return $query->where('enseignant_remplacant_id', $personnelId);
    }

    /**
     * Scope pour filtrer par classe
     */
    public function scopeParClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Scope pour filtrer par matière
     */
    public function scopeParMatiere($query, $matiereId)
    {
        return $query->where('matiere_id', $matiereId);
    }
}
