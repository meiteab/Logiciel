# ⏰ Module 5 : Emplois du temps

## 🎯 **Objectif du Module**
Gérer complètement les emplois du temps de toutes les classes en utilisant les tables existantes et en ajoutant la table de liaison manquante. Ce module permet de planifier, gérer et publier les emplois du temps.

## 🗄️ **Structure du Module 5**

### **✅ Tables DÉJÀ existantes (créées précédemment) :**

#### **1. Table `plages_horaires`**
**Objectif** : Définir les périodes de cours dans la journée.

**Colonnes principales** :
- `code` : Code de la plage (P1, P2, RECRE, DEJEUNER)
- `nom` : Nom de la plage ("1ère Période", "Récréation")
- `heure_debut` et `heure_fin` : Heures de début et fin
- `type` : Type de plage (cours, recreation, pause_dejeuner)
- `ordre` : Ordre dans la journée


**Exemple** :
- P1 : 8h00 - 9h00 (cours)
- RECRE : 9h00 - 9h15 (recreation)
- P2 : 9h15 - 10h15 (cours)
- DEJEUNER : 12h00 - 13h00 (pause_dejeuner)

#### **2. Table `jours_semaine`**
**Objectif** : Définir les jours de la semaine avec leurs spécificités.

**Colonnes principales** :
- `code` : Code du jour (LUNDI, MARDI, MERCREDI...)
- `numero_jour` : Numéro du jour (1=Lundi, 2=Mardi...)
- `est_jour_cours` : Si le jour a des cours
- `heure_debut_cours` et `heure_fin_cours` : Heures de cours
- `heure_debut_pause` et `heure_fin_pause` : Heures de pause

#### **3. Table `salles`**
**Objectif** : Définir toutes les salles de l'établissement.

**Colonnes principales** :
- `code` : Code de la salle (A1, A2, LABO, SPORT)
- `nom` : Nom de la salle ("Salle A1", "Laboratoire Sciences")
- `type` : Type de salle (classe, laboratoire, gymnase)
- `capacite_max` : Nombre maximum d'élèves
- `batiment` et `etage` : Localisation

#### **4. Table `classes`**
**Objectif** : Définir les classes de l'établissement.

**Colonnes principales** :
- `nom` : Nom de la classe ("CP-A Français", "CP-A Arabe")
- `code` : Code unique de la classe
- `capacite_max` et `capacite_actuelle` : Gestion des effectifs

#### **5. Table `enseignants_classes`**
**Objectif** : Définir quels enseignants enseignent quelles matières dans quelles classes.

**Colonnes principales** :
- `personnel_id` : L'enseignant
- `classe_id` : La classe
- `matiere_id` : La matière enseignée
- `annee_scolaire_id` : L'année scolaire
- `role` : Titulaire, suppléant, remplaçant

### **🆕 Table NOUVELLE créée dans ce module :**

#### **6. Table `emplois_du_temps_cours`**
**Objectif** : **LIAISON COMPLÈTE** entre tous les éléments pour créer des emplois du temps.

**Colonnes principales** :
- **Clés de liaison** : `classe_id`, `jour_semaine_id`, `plage_horaire_id`, `matiere_id`, `enseignant_id`, `salle_id`, `annee_scolaire_id`
- **Informations sur le cours** : `type_cours`, `statut`, `commentaires`
- **Gestion des exceptions** : `est_exception`, `date_exception`, `motif_exception`
- **Validation** : `valide_par_id`, `date_validation`, `notes_validation`

**Logique** : Cette table **LIE TOUT** ensemble pour créer un emploi du temps complet.

## 🔗 **Relations entre les tables**

### **Schéma relationnel :**
```
emplois_du_temps_cours (1) ←→ (1) classes
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) jours_semaine
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) plages_horaires
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) matieres
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) personnels (enseignants)
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) salles
     ↓                              ↓
     ↓                              ↓
     ↓                              ↓
emplois_du_temps_cours (1) ←→ (1) annees_scolaires
```

### **Relations clés :**
1. **Un cours** est défini par la combinaison classe + jour + plage + matière + enseignant + salle + année
2. **Une classe** peut avoir plusieurs cours dans la semaine
3. **Un enseignant** peut enseigner dans plusieurs classes
4. **Une salle** peut être utilisée par plusieurs classes à des moments différents

## 📊 **Exemples d'utilisation**

### **Scénario 1 : Création d'un cours**
```sql
-- CP-A Français a Mathématiques le Lundi de 8h à 9h avec M. Dupont en Salle A1
INSERT INTO emplois_du_temps_cours (
    classe_id, jour_semaine_id, plage_horaire_id, matiere_id, 
    enseignant_id, salle_id, annee_scolaire_id, type_cours, statut
) VALUES (1, 1, 1, 1, 1, 1, 1, 'cours', 'planifie');
```

### **Scénario 2 : Récupération de l'emploi du temps d'une classe**
```sql
-- Emploi du temps complet de CP-A Français
SELECT 
    j.nom as jour,
    ph.nom as plage,
    m.nom as matiere,
    p.prenom || ' ' || p.nom_famille as enseignant,
    s.nom as salle
FROM emplois_du_temps_cours edt
JOIN jours_semaine j ON edt.jour_semaine_id = j.id
JOIN plages_horaires ph ON edt.plage_horaire_id = ph.id
JOIN matieres m ON edt.matiere_id = m.id
JOIN personnels p ON edt.enseignant_id = p.id
JOIN salles s ON edt.salle_id = s.id
WHERE edt.classe_id = 1 AND edt.annee_scolaire_id = 1
ORDER BY j.numero_jour, ph.ordre;
```

### **Scénario 3 : Vérification des conflits**
```sql
-- Vérifier si un enseignant a des conflits
SELECT 
    edt1.classe_id as classe1,
    edt2.classe_id as classe2,
    j.nom as jour,
    ph.nom as plage
FROM emplois_du_temps_cours edt1
JOIN emplois_du_temps_cours edt2 ON (
    edt1.enseignant_id = edt2.enseignant_id AND
    edt1.jour_semaine_id = edt2.jour_semaine_id AND
    edt1.plage_horaire_id = edt2.plage_horaire_id AND
    edt1.annee_scolaire_id = edt2.annee_scolaire_id AND
    edt1.id != edt2.id
)
JOIN jours_semaine j ON edt1.jour_semaine_id = j.id
JOIN plages_horaires ph ON edt1.plage_horaire_id = ph.id
WHERE edt1.enseignant_id = 1;
```

## 🎯 **Avantages de cette structure**

### **1. Flexibilité maximale :**
- **Ajout facile** de nouveaux cours
- **Modification simple** des horaires
- **Gestion des exceptions** (remplacements, annulations)
- **Validation des emplois** du temps

### **2. Prévention des conflits :**
- **Contraintes uniques** empêchent les conflits de classes, enseignants et salles
- **Détection automatique** des problèmes
- **Validation** avant activation

### **3. Intégration complète :**
- **Utilise toutes** les tables existantes
- **Pas de redondance** de données
- **Cohérence** garantie

## ⚠️ **Contraintes et bonnes pratiques**

### **Contraintes de clés étrangères :**
- `onDelete('cascade')` pour les relations logiques (si une classe est supprimée, ses emplois du temps sont supprimés)
- `onDelete('restrict')` pour les relations critiques (empêche la suppression d'un jour ou d'une plage utilisée)

### **Contraintes uniques :**
1. **Une classe** ne peut avoir qu'un cours par jour/plage/année
2. **Un enseignant** ne peut avoir qu'un cours par jour/plage/année
3. **Une salle** ne peut avoir qu'un cours par jour/plage/année

### **Index de performance :**
- Index sur toutes les clés de liaison
- Index composites pour les requêtes fréquentes
- Index sur les statuts et types de cours

### **Soft Deletes :**
- Utilise `softDeletes()` pour conserver l'historique
- Permet de récupérer des emplois du temps supprimés

## 🚀 **Prochaines étapes**

Le Module 5 est maintenant **COMPLET** avec :
- ✅ 1 table nouvelle bien structurée
- ✅ Utilisation de toutes les tables existantes
- ✅ Seeder avec données d'exemple
- ✅ Documentation complète

**Modules suivants à développer** :
- **Module 6** : Notes et évaluations (utilisera les cours du Module 5)
- **Module 7** : Documents et bulletins
- **Module 8** : Communication (publication des emplois du temps)

## 📋 **Résumé des tables du Module 5**

| Table | Objectif | Statut |
|-------|----------|---------|
| `plages_horaires` | Définition des périodes | ✅ Existant |
| `jours_semaine` | Définition des jours | ✅ Existant |
| `salles` | Définition des salles | ✅ Existant |
| `classes` | Définition des classes | ✅ Existant |
| `enseignants_classes` | Attribution enseignants | ✅ Existant |
| `emplois_du_temps_cours` | Liaison complète | 🆕 Nouveau |

Le Module 5 est maintenant **prêt à être utilisé** pour créer, gérer et publier des emplois du temps complets !
