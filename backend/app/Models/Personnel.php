<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personnels';

    protected $fillable = [
        'user_id',
        'matricule',
        'nom_famille',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'adresse',
        'telephone',
        'email_professionnel',
        'email_personnel',
        'date_embauche',
        'type_personnel',
        'fonction',
        'departement',
        'diplome',
        'specialite',
        'numero_securite_sociale',
        'numero_cnss',
        'salaire_brut',
        'salaire_net',
        'banque',
        'numero_compte',
        'rib',
        'est_actif',
        'date_depart',
        'motif_depart',
        'photo',
        'observations'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_embauche' => 'date',
        'date_depart' => 'date',
        'salaire_brut' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'est_actif' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec l'utilisateur (One-to-One)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les matières enseignées (Many-to-Many) - pour les enseignants
     */
    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'enseignants_matieres', 'personnel_id', 'matiere_id')
                    ->withPivot(['niveau_competence', 'date_obtention_competence', 'commentaires_competence', 'est_actif'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les classes enseignées (Many-to-Many) - pour les enseignants
     */
    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'enseignants_classes', 'personnel_id', 'classe_id')
                    ->withPivot(['matiere_id', 'annee_scolaire_id', 'role', 'heures_semaine', 'date_debut', 'date_fin', 'statut', 'notes_pedagogiques'])
                    ->withTimestamps();
    }

    /**
     * Relation avec les absences (One-to-Many) - pour les enseignants
     */
    public function absences()
    {
        return $this->hasMany(AbsenceEnseignant::class, 'personnel_id');
    }

    /**
     * Relation avec les remplacements effectués (One-to-Many) - pour les enseignants
     */
    public function remplacementsEffectues()
    {
        return $this->hasMany(Remplacement::class, 'enseignant_remplacant_id');
    }

    /**
     * Relation avec les validations d'absences (One-to-Many)
     */
    public function validationsAbsences()
    {
        return $this->hasMany(AbsenceEnseignant::class, 'valide_par_id');
    }

    /**
     * Relation avec les validations de remplacements (One-to-Many)
     */
    public function validationsRemplacements()
    {
        return $this->hasMany(Remplacement::class, 'valide_par_id');
    }

    /**
     * Relation avec les cours d'emploi du temps (One-to-Many) - pour les enseignants
     */
    public function coursEmploiDuTemps()
    {
        return $this->hasMany(EmploiDuTempsCours::class, 'enseignant_id');
    }

    /**
     * Vérifier si le personnel est actif
     */
    public function isActive(): bool
    {
        return $this->est_actif === true;
    }

    /**
     * Vérifier si le personnel est un enseignant
     */
    public function isEnseignant(): bool
    {
        return $this->type_personnel === 'enseignant';
    }

    /**
     * Vérifier si le personnel est administratif
     */
    public function isAdministratif(): bool
    {
        return $this->type_personnel === 'administratif';
    }

    /**
     * Vérifier si le personnel est technique
     */
    public function isTechnique(): bool
    {
        return $this->type_personnel === 'technique';
    }

    /**
     * Vérifier si le personnel est de direction
     */
    public function isDirection(): bool
    {
        return $this->type_personnel === 'direction';
    }

    /**
     * Activer le personnel
     */
    public function activate(): void
    {
        $this->update(['est_actif' => true]);
    }

    /**
     * Désactiver le personnel
     */
    public function deactivate(): void
    {
        $this->update(['est_actif' => false]);
    }

    /**
     * Obtenir le nom complet
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom_famille);
    }

    /**
     * Obtenir le nom complet avec fonction
     */
    public function getNomCompletAvecFonctionAttribute(): string
    {
        $fonction = $this->fonction ? " ({$this->fonction})" : '';
        return $this->nom_complet . $fonction;
    }

    /**
     * Obtenir le nom complet avec type et fonction
     */
    public function getNomCompletAvecTypeAttribute(): string
    {
        $type = $this->type_personnel ? ucfirst($this->type_personnel) : '';
        $fonction = $this->fonction ? " - {$this->fonction}" : '';
        return $this->nom_complet . " ({$type}{$fonction})";
    }

    /**
     * Scope pour le personnel actif
     */
    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour le personnel inactif
     */
    public function scopeInactifs($query)
    {
        return $query->where('est_actif', false);
    }

    /**
     * Scope pour filtrer par type de personnel
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_personnel', $type);
    }

    /**
     * Scope pour les enseignants
     */
    public function scopeEnseignants($query)
    {
        return $query->where('type_personnel', 'enseignant');
    }

    /**
     * Scope pour le personnel administratif
     */
    public function scopeAdministratifs($query)
    {
        return $query->where('type_personnel', 'administratif');
    }

    /**
     * Scope pour le personnel technique
     */
    public function scopeTechniques($query)
    {
        return $query->where('type_personnel', 'technique');
    }

    /**
     * Scope pour le personnel de direction
     */
    public function scopeDirection($query)
    {
        return $query->where('type_personnel', 'direction');
    }

    /**
     * Scope pour filtrer par fonction
     */
    public function scopeParFonction($query, $fonction)
    {
        return $query->where('fonction', 'like', "%{$fonction}%");
    }

    /**
     * Scope pour filtrer par département
     */
    public function scopeParDepartement($query, $departement)
    {
        return $query->where('departement', 'like', "%{$departement}%");
    }

    /**
     * Scope pour filtrer par année d'embauche
     */
    public function scopeParAnneeEmbauche($query, $annee)
    {
        return $query->whereYear('date_embauche', $annee);
    }

    /**
     * Scope pour filtrer par spécialité
     */
    public function scopeParSpecialite($query, $specialite)
    {
        return $query->where('specialite', 'like', "%{$specialite}%");
    }
}
