<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use HasFactory;

    protected $table = 'programmes';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'categorie',
        'langue_principale',
        'langue_secondaire',
        'est_programme_par_defaut',
        'est_programme_bilingue',
        'nombre_heures_semaine',
        'matieres_obligatoires',
        'matieres_optionnelles',
        'conditions_admission',
        'notes',
        'est_actif'
    ];

    protected $casts = [
        'est_programme_par_defaut' => 'boolean',
        'est_programme_bilingue' => 'boolean',
        'nombre_heures_semaine' => 'integer',
        'matieres_obligatoires' => 'array',
        'matieres_optionnelles' => 'array',
        'est_actif' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relation avec les niveaux
     */
    public function niveaux()
    {
        return $this->hasMany(Niveau::class);
    }

    /**
     * Relation avec les matières
     */
    public function matieres()
    {
        return $this->belongsToMany(Matiere::class, 'matieres_programmes', 'programme_id', 'matiere_id');
    }

    /**
     * Relation avec les classes
     */
    public function classes()
    {
        return $this->hasManyThrough(Classe::class, Niveau::class);
    }

    /**
     * Vérifier si le programme est actif
     */
    public function isActive(): bool
    {
        return $this->est_actif === true;
    }

    /**
     * Activer le programme
     */
    public function activate(): void
    {
        $this->update(['est_actif' => true]);
    }

    /**
     * Désactiver le programme
     */
    public function deactivate(): void
    {
        $this->update(['est_actif' => false]);
    }

    /**
     * Scope pour les programmes actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les programmes inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('est_actif', false);
    }
}
