<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'nom',
        'code',
        'capacite_max',
        'capacite_actuelle',
        'statut'
    ];

    protected $casts = [
        'capacite_max' => 'integer',
        'capacite_actuelle' => 'integer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec les niveaux (many-to-many via classes_niveaux)
     */
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'classes_niveaux', 'classe_id', 'niveau_id')
                    ->withPivot(['programme_id', 'annee_scolaire_id', 'enseignant_titulaire_id', 'salle_id', 'ordre_affichage'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les programmes (many-to-many via classes_niveaux)
     */
    public function programmes()
    {
        return $this->belongsToMany(Programme::class, 'classes_niveaux', 'classe_id', 'programme_id')
                    ->withPivot(['niveau_id', 'annee_scolaire_id', 'enseignant_titulaire_id', 'salle_id', 'ordre_affichage'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les années scolaires (many-to-many via classes_niveaux)
     */
    public function anneesScolaires()
    {
        return $this->belongsToMany(AnneeScolaire::class, 'classes_niveaux', 'classe_id', 'annee_scolaire_id')
                    ->withPivot(['niveau_id', 'programme_id', 'enseignant_titulaire_id', 'salle_id', 'ordre_affichage'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les enseignants titulaires (many-to-many via classes_niveaux)
     */
    public function enseignantsTitulaires()
    {
        return $this->belongsToMany(Personnel::class, 'classes_niveaux', 'classe_id', 'enseignant_titulaire_id')
                    ->withPivot(['niveau_id', 'programme_id', 'annee_scolaire_id', 'salle_id', 'ordre_affichage'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les élèves (via inscriptions)
     */
    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'inscriptions_eleves', 'classe_id', 'eleve_id');
    }

    /**
     * Vérifier si la classe est active
     */
    public function isActive(): bool
    {
        return $this->statut === 'active';
    }

    /**
     * Activer la classe
     */
    public function activate(): void
    {
        $this->update(['statut' => 'active']);
    }

    /**
     * Désactiver la classe
     */
    public function deactivate(): void
    {
        $this->update(['statut' => 'inactive']);
    }

    /**
     * Scope pour les classes actives
     */
    public function scopeActives($query)
    {
        return $query->where('statut', 'active');
    }

    /**
     * Scope pour les classes inactives
     */
    public function scopeInactives($query)
    {
        return $query->where('statut', 'inactive');
    }

    /**
     * Scope pour filtrer par niveau
     */
    public function scopeByNiveau($query, $niveauId)
    {
        return $query->whereHas('niveaux', function ($q) use ($niveauId) {
            $q->where('niveau_id', $niveauId);
        });
    }

    /**
     * Scope pour filtrer par programme
     */
    public function scopeByProgramme($query, $programmeId)
    {
        return $query->whereHas('programmes', function ($q) use ($programmeId) {
            $q->where('programme_id', $programmeId);
        });
    }
}
