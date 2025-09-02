<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploiDuTempsCours extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emplois_du_temps_cours';

    protected $fillable = [
        'classe_id',
        'jour_semaine_id',
        'plage_horaire_id',
        'matiere_id',
        'enseignant_id',
        'salle_id',
        'annee_scolaire_id',
        'type_cours',
        'statut',
        'commentaires',
        'est_exception',
        'date_exception',
        'motif_exception',
        'valide_par_id',
        'date_validation',
        'notes_validation'
    ];

    protected $casts = [
        'est_exception' => 'boolean',
        'date_exception' => 'date',
        'date_validation' => 'datetime'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec la classe
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Relation avec le jour de la semaine
     */
    public function jourSemaine()
    {
        return $this->belongsTo(JourSemaine::class, 'jour_semaine_id');
    }

    /**
     * Relation avec la plage horaire
     */
    public function plageHoraire()
    {
        return $this->belongsTo(PlageHoraire::class, 'plage_horaire_id');
    }

    /**
     * Relation avec la matière
     */
    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }

    /**
     * Relation avec l'enseignant
     */
    public function enseignant()
    {
        return $this->belongsTo(Personnel::class, 'enseignant_id');
    }

    /**
     * Relation avec la salle
     */
    public function salle()
    {
        return $this->belongsTo(Salle::class, 'salle_id');
    }

    /**
     * Relation avec l'année scolaire
     */
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'annee_scolaire_id');
    }

    /**
     * Relation avec le personnel qui valide
     */
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par_id');
    }

    /**
     * Vérifier si le cours est planifié
     */
    public function isPlanifie(): bool
    {
        return $this->statut === 'planifie';
    }

    /**
     * Vérifier si le cours est confirmé
     */
    public function isConfirme(): bool
    {
        return $this->statut === 'confirme';
    }

    /**
     * Vérifier si le cours est annulé
     */
    public function isAnnule(): bool
    {
        return $this->statut === 'annule';
    }

    /**
     * Vérifier si le cours est terminé
     */
    public function isTermine(): bool
    {
        return $this->statut === 'termine';
    }

    /**
     * Vérifier si c'est une exception
     */
    public function isException(): bool
    {
        return $this->est_exception === true;
    }

    /**
     * Confirmer le cours
     */
    public function confirmer(int $valideParId, ?string $notes = null): void
    {
        $this->update([
            'statut' => 'confirme',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'notes_validation' => $notes
        ]);
    }

    /**
     * Annuler le cours
     */
    public function annuler(int $valideParId, ?string $notes = null): void
    {
        $this->update([
            'statut' => 'annule',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'notes_validation' => $notes
        ]);
    }

    /**
     * Marquer comme terminé
     */
    public function terminer(int $valideParId, ?string $notes = null): void
    {
        $this->update([
            'statut' => 'termine',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'notes_validation' => $notes
        ]);
    }

    /**
     * Obtenir la durée du cours en minutes
     */
    public function getDureeMinutesAttribute(): ?int
    {
        if (!$this->plageHoraire) {
            return null;
        }

        $debut = \Carbon\Carbon::parse($this->plageHoraire->heure_debut);
        $fin = \Carbon\Carbon::parse($this->plageHoraire->heure_fin);
        
        return $debut->diffInMinutes($fin);
    }

    /**
     * Scope pour les cours planifiés
     */
    public function scopePlanifies($query)
    {
        return $query->where('statut', 'planifie');
    }

    /**
     * Scope pour les cours confirmés
     */
    public function scopeConfirmes($query)
    {
        return $query->where('statut', 'confirme');
    }

    /**
     * Scope pour les cours annulés
     */
    public function scopeAnnules($query)
    {
        return $query->where('statut', 'annule');
    }

    /**
     * Scope pour les cours terminés
     */
    public function scopeTermines($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope pour les exceptions
     */
    public function scopeExceptions($query)
    {
        return $query->where('est_exception', true);
    }

    /**
     * Scope pour les cours normaux (non-exceptions)
     */
    public function scopeNormaux($query)
    {
        return $query->where('est_exception', false);
    }

    /**
     * Scope pour filtrer par classe
     */
    public function scopeParClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Scope pour filtrer par enseignant
     */
    public function scopeParEnseignant($query, $enseignantId)
    {
        return $query->where('enseignant_id', $enseignantId);
    }

    /**
     * Scope pour filtrer par matière
     */
    public function scopeParMatiere($query, $matiereId)
    {
        return $query->where('matiere_id', $matiereId);
    }

    /**
     * Scope pour filtrer par salle
     */
    public function scopeParSalle($query, $salleId)
    {
        return $query->where('salle_id', $salleId);
    }

    /**
     * Scope pour filtrer par année scolaire
     */
    public function scopeParAnneeScolaire($query, $anneeScolaireId)
    {
        return $query->where('annee_scolaire_id', $anneeScolaireId);
    }

    /**
     * Scope pour filtrer par jour de la semaine
     */
    public function scopeParJour($query, $jourSemaineId)
    {
        return $query->where('jour_semaine_id', $jourSemaineId);
    }

    /**
     * Scope pour filtrer par type de cours
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_cours', $type);
    }

    /**
     * Scope pour filtrer par date d'exception
     */
    public function scopeParDateException($query, $date)
    {
        return $query->where('date_exception', $date);
    }
}
