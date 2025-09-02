# 📚 Module 4 : Matières et Programmes

## 🎯 **Objectif du Module**
Gérer la structure pédagogique complète : matières, niveaux, programmes et leurs relations. Ce module est **fondamental** car il sera utilisé par tous les autres modules pédagogiques.

## 🗄️ **Structure du Module 4**

### **✅ Tables DÉJÀ existantes (créées précédemment) :**

#### **1. Table `matieres`**
**Objectif** : Définir toutes les matières enseignées dans l'établissement.

**Colonnes principales** :
- `code` : Code unique de la matière (MATHS, FRANCAIS, ARABE, CORAN)
- `nom` : Nom complet de la matière
- `programme_id` : Programme auquel appartient la matière (Français ou Arabe)
- `coefficient` : Coefficient général de la matière
- `est_matiere_principale` : Si c'est une matière principale ou optionnelle
- `est_matiere_notes` : Si la matière est évaluée avec des notes

**Logique** : Chaque matière appartient à un programme spécifique (Français ou Arabe).

#### **2. Table `niveaux`**
**Objectif** : Définir les niveaux scolaires (CP, CE1, CE2, CM1, CM2, etc.).

**Colonnes principales** :
- `code` : Code du niveau (CP, CE1, CE2, etc.)
- `nom` : Nom complet du niveau
- `programme_id` : Programme auquel appartient le niveau
- `ordre` : Ordre d'affichage et de progression
- `capacite_max` : Nombre maximum d'élèves par classe

**Logique** : Chaque niveau appartient à un programme spécifique.

#### **3. Table `programmes`**
**Objectif** : Définir les programmes d'enseignement (Français, Arabe, Bilingue).

**Colonnes principales** :
- `code` : Code du programme (FRANCAIS, ARABE, BILINGUE)
- `categorie` : Catégorie (francais, arabe, bilingue, international)
- `langue_principale` : Langue principale d'enseignement
- `est_programme_bilingue` : Si c'est un programme bilingue
- `nombre_heures_semaine` : Nombre total d'heures par semaine

**Logique** : Distinction claire entre programmes français et arabes.

#### **4. Table `annees_scolaires`**
**Objectif** : Gérer les années scolaires (2024-2025, 2025-2026, etc.).

**Colonnes principales** :
- `annee_debut` et `annee_fin` : Période de l'année scolaire
- `date_debut` et `date_fin` : Dates exactes de début et fin
- `statut` : Statut de l'année (en_preparation, active, archivee)

### **🆕 Tables NOUVELLES créées dans ce module :**

#### **5. Table `matieres_niveaux`**
**Objectif** : Définir quelles matières sont enseignées à quels niveaux avec leurs spécificités.

**Colonnes principales** :
- `matiere_id`, `niveau_id`, `programme_id`, `annee_scolaire_id` : Clés de liaison
- `coefficient_niveau` : Coefficient spécifique au niveau (peut différer du coefficient général)
- `heures_semaine` : Heures par semaine pour ce niveau
- `heures_annee` : Heures totales par année
- `ordre_matiere` : Ordre d'affichage de la matière dans le niveau
- `est_obligatoire` : Si la matière est obligatoire à ce niveau
- `est_evaluee` : Si la matière est évaluée à ce niveau

**Logique** : Une matière peut avoir des coefficients et heures différents selon le niveau.

#### **6. Table `programmes_niveaux`**
**Objectif** : Définir la structure des programmes par niveau avec progression.

**Colonnes principales** :
- `programme_id`, `niveau_id`, `annee_scolaire_id` : Clés de liaison
- `ordre_progression` : Ordre de progression dans le programme
- `duree_niveau` : Durée en années (1 = 1 an, 2 = 2 ans)
- `niveau_precedent_id` et `niveau_suivant_id` : Liens de progression
- `nombre_eleves_max` et `nombre_eleves_min` : Capacités par niveau
- `heures_total_semaine` et `heures_total_annee` : Totaux d'heures
- `est_niveau_obligatoire` : Si le niveau est obligatoire dans le programme

**Logique** : Structure complète de progression des programmes par niveau.

## 🔗 **Relations entre les tables**

### **Schéma relationnel :**
```
programmes (1) ←→ (n) programmes_niveaux (n) ←→ (1) niveaux
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
matieres (1) ←→ (n) matieres_niveaux (n) ←→ (1) niveaux
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
classes_niveaux (n) ←→ (1) niveaux
     ↓
     ↓
classes (1) ←→ (n) inscriptions_eleves
```

### **Relations clés :**
1. **Un programme** peut avoir **plusieurs niveaux** (via `programmes_niveaux`)
2. **Un niveau** peut appartenir à **plusieurs programmes** (Français et Arabe)
3. **Une matière** peut être enseignée à **plusieurs niveaux** (via `matieres_niveaux`)
4. **Un niveau** peut avoir **plusieurs matières** (via `matieres_niveaux`)
5. **Les classes** utilisent les niveaux et programmes (via `classes_niveaux`)

## 📊 **Exemples d'utilisation**

### **Scénario 1 : Configuration d'une matière par niveau**
```sql
-- Mathématiques en CP-A Français
INSERT INTO matieres_niveaux (
    matiere_id, niveau_id, programme_id, annee_scolaire_id,
    coefficient_niveau, heures_semaine, heures_annee, ordre_matiere
) VALUES (1, 1, 1, 1, 2, 5, 180, 1);
```

### **Scénario 2 : Structure d'un programme**
```sql
-- Programme Français avec progression CP → CE1 → CE2
INSERT INTO programmes_niveaux (
    programme_id, niveau_id, annee_scolaire_id,
    ordre_progression, niveau_precedent_id, niveau_suivant_id
) VALUES (1, 2, 1, 2, 1, 3);
```

### **Scénario 3 : Récupération des matières d'un niveau**
```sql
-- Toutes les matières du CP-A Français
SELECT m.nom, mn.coefficient_niveau, mn.heures_semaine
FROM matieres_niveaux mn
JOIN matieres m ON mn.matiere_id = m.id
WHERE mn.niveau_id = 1 AND mn.programme_id = 1;
```

## 🎯 **Avantages de cette structure**

### **1. Flexibilité pédagogique :**
- **Coefficients variables** : Une matière peut avoir des coefficients différents selon le niveau
- **Heures adaptatives** : Les heures peuvent varier selon le niveau et le programme
- **Progression logique** : Structure claire de progression des niveaux

### **2. Gestion Franco-Arabe :**
- **Séparation claire** : Programmes français et arabes distincts
- **Niveaux parallèles** : CP-A Français et CP-A Arabe peuvent coexister
- **Matieres spécifiques** : Chaque programme a ses matières spécifiques

### **3. Évolutivité :**
- **Ajout facile** de nouveaux niveaux ou matières
- **Modification** des coefficients et heures sans affecter la structure
- **Historisation** des changements via soft deletes

## ⚠️ **Contraintes et bonnes pratiques**

### **Contraintes de clés étrangères :**
- `onDelete('cascade')` pour les relations logiques (si une matière est supprimée, ses attributions par niveau sont supprimées)
- `onDelete('restrict')` pour les relations critiques (empêche la suppression d'un niveau utilisé)

### **Index de performance :**
- Index sur les combinaisons fréquemment utilisées
- Index sur les coefficients et heures pour les calculs
- Index sur les ordres pour l'affichage

### **Soft Deletes :**
- Toutes les tables utilisent `softDeletes()` pour conserver l'historique
- Permet de récupérer des données supprimées si nécessaire

## 🚀 **Prochaines étapes**

Le Module 4 est maintenant **COMPLET** avec :
- ✅ 6 tables bien structurées (4 existantes + 2 nouvelles)
- ✅ Relations claires et logiques
- ✅ Seeder avec données d'exemple
- ✅ Documentation complète

**Modules suivants à développer** :
- **Module 5** : Emplois du temps (utilisera les données du Module 4)
- **Module 6** : Notes et évaluations (utilisera les coefficients du Module 4)
- **Module 7** : Examens et contrôles (utilisera la structure du Module 4)

## 📋 **Résumé des tables du Module 4**

| Table | Objectif | Relations |
|-------|----------|-----------|
| `matieres` | Définition des matières | → `programmes` |
| `niveaux` | Définition des niveaux | → `programmes` |
| `programmes` | Définition des programmes | ← `matieres`, `niveaux` |
| `annees_scolaires` | Gestion temporelle | ← Toutes les tables |
| `matieres_niveaux` | Liaison matières ↔ niveaux | → `matieres`, `niveaux`, `programmes`, `annees_scolaires` |
| `programmes_niveaux` | Structure des programmes | → `programmes`, `niveaux`, `annees_scolaires` |

Le Module 4 est maintenant **prêt à être utilisé** par tous les autres modules pédagogiques !
