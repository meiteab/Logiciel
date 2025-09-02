<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsenceEnseignant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absences_enseignants';

    protected $fillable = [
        'personnel_id',
        'date_absence',
        'heure_debut',
        'heure_fin',
        'type_absence',
        'statut',
        'motif',
        'justificatif',
        'valide_par_id',
        'date_validation',
        'commentaires_validation'
    ];

    protected $casts = [
        'date_absence' => 'date',
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
     * Relation avec le personnel absent
     */
    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    /**
     * Relation avec le personnel qui valide l'absence
     */
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par_id');
    }

    /**
     * Relation avec les remplacements
     */
    public function remplacements()
    {
        return $this->hasMany(Remplacement::class, 'absence_enseignant_id');
    }

    /**
     * Vérifier si l'absence est validée
     */
    public function isValidee(): bool
    {
        return $this->statut === 'validee';
    }

    /**
     * Vérifier si l'absence est en attente
     */
    public function isEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    /**
     * Vérifier si l'absence est refusée
     */
    public function isRefusee(): bool
    {
        return $this->statut === 'refusee';
    }

    /**
     * Valider l'absence
     */
    public function valider(int $valideParId, ?string $commentaires = null): void
    {
        $this->update([
            'statut' => 'validee',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'commentaires_validation' => $commentaires
        ]);
    }

    /**
     * Refuser l'absence
     */
    public function refuser(int $valideParId, ?string $commentaires = null): void
    {
        $this->update([
            'statut' => 'refusee',
            'valide_par_id' => $valideParId,
            'date_validation' => now(),
            'commentaires_validation' => $commentaires
        ]);
    }

    /**
     * Obtenir la durée de l'absence en heures
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
     * Scope pour les absences validées
     */
    public function scopeValidees($query)
    {
        return $query->where('statut', 'validee');
    }

    /**
     * Scope pour les absences en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les absences refusées
     */
    public function scopeRefusees($query)
    {
        return $query->where('statut', 'refusee');
    }

    /**
     * Scope pour filtrer par type d'absence
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_absence', $type);
    }

    /**
     * Scope pour filtrer par date
     */
    public function scopeParDate($query, $date)
    {
        return $query->where('date_absence', $date);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_absence', [$dateDebut, $dateFin]);
    }

    /**
     * Scope pour filtrer par enseignant
     */
    public function scopeParEnseignant($query, $personnelId)
    {
        return $query->where('personnel_id', $personnelId);
    }
}
