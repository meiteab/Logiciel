<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Niveau extends Model
{
    use HasFactory;

    protected $table = 'niveaux';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'ordre',
        'age_min',
        'age_max',
        'capacite_max',
        'programme_id',
        'est_actif'
    ];

    protected $casts = [
        'ordre' => 'integer',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'capacite_max' => 'integer',
        'est_actif' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relation avec le programme
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    /**
     * Relation avec les classes
     */
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    /**
     * Relation avec les élèves (via inscriptions)
     */
    public function eleves()
    {
        return $this->hasManyThrough(Eleve::class, Classe::class);
    }

    /**
     * Vérifier si le niveau est actif
     */
    public function isActive(): bool
    {
        return $this->est_actif === true;
    }

    /**
     * Activer le niveau
     */
    public function activate(): void
    {
        $this->update(['est_actif' => true]);
    }

    /**
     * Désactiver le niveau
     */
    public function deactivate(): void
    {
        $this->update(['est_actif' => false]);
    }

    /**
     * Scope pour les niveaux actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les niveaux inactifs
     */
    public function scopeInactifs($query)
    {
        return $query->where('est_actif', false);
    }

    /**
     * Scope pour filtrer par programme
     */
    public function scopeByProgramme($query, $programmeId)
    {
        return $query->where('programme_id', $programmeId);
    }
}
