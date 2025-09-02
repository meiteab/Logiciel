<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matiere extends Model
{
    use HasFactory;

    protected $table = 'matieres';

    protected $fillable = [
        'nom',
        'code',
        'description',
        'couleur',
        'icone',
        'coefficient',
        'ordre',
        'est_matiere_principale',
        'est_matiere_notes',
        'programme_id',
        'est_actif'
    ];

    protected $casts = [
        'coefficient' => 'float',
        'ordre' => 'integer',
        'est_matiere_principale' => 'boolean',
        'est_matiere_notes' => 'boolean',
        'est_actif' => 'boolean'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relation avec les programmes (many-to-many)
     */
    public function programmes()
    {
        return $this->belongsToMany(Programme::class, 'matieres_programmes', 'matiere_id', 'programme_id');
    }

    /**
     * Relation avec les niveaux (many-to-many)
     */
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'matieres_niveaux', 'matiere_id', 'niveau_id');
    }

    /**
     * Vérifier si la matière est active
     */
    public function isActive(): bool
    {
        return $this->est_actif === true;
    }

    /**
     * Activer la matière
     */
    public function activate(): void
    {
        $this->update(['est_actif' => true]);
    }

    /**
     * Désactiver la matière
     */
    public function deactivate(): void
    {
        $this->update(['est_actif' => false]);
    }

    /**
     * Scope pour les matières actives
     */
    public function scopeActives($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les matières inactives
     */
    public function scopeInactives($query)
    {
        return $query->where('est_actif', false);
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
