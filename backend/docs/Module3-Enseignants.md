# 📚 Module 3 : Enseignants

## 🎯 **Objectif du Module**
Gérer toutes les fonctionnalités liées aux enseignants : attribution des matières, gestion des classes, absences, remplacements et historique d'enseignement.

## 🗄️ **Tables du Module 3**

### **1. Table `enseignants_matieres`**
**Objectif** : Définir quelles matières chaque enseignant peut enseigner.

**Colonnes principales** :
- `personnel_id` : Référence vers la table `personnels` (Module 1)
- `matiere_id` : Référence vers la table `matieres` (à créer dans Module 4)
- `niveau_competence` : Niveau de maîtrise de la matière (débutant, intermédiaire, expert)
- `est_actif` : Si l'enseignant peut encore enseigner cette matière

**Logique** : Un enseignant peut être compétent dans plusieurs matières, et une matière peut être enseignée par plusieurs enseignants.

### **2. Table `enseignants_classes`**
**Objectif** : Attribuer les enseignants aux classes et matières pour une année scolaire donnée.

**Colonnes principales** :
- `personnel_id` : L'enseignant
- `classe_id` : La classe (Module 2)
- `matiere_id` : La matière enseignée
- `annee_scolaire_id` : L'année scolaire
- `role` : Titulaire, suppléant ou remplaçant
- `heures_semaine` : Nombre d'heures par semaine

**Logique** : Un enseignant peut enseigner plusieurs matières dans plusieurs classes, mais une seule entrée par combinaison enseignant-classe-matière-année.

### **3. Table `absences_enseignants`**
**Objectif** : Gérer les absences des enseignants (maladie, congés, formation, etc.).

**Colonnes principales** :
- `personnel_id` : L'enseignant absent
- `date_absence` : Date de l'absence
- `type_absence` : Maladie, congés, formation, personnel, autre
- `statut` : En attente, validée, refusée
- `valide_par_id` : Qui a validé l'absence

**Logique** : Chaque absence doit être validée par un responsable (chef d'établissement, directeur).

### **4. Table `remplacements`**
**Objectif** : Planifier et gérer les remplacements d'enseignants absents.

**Colonnes principales** :
- `absence_enseignant_id` : Référence vers l'absence
- `enseignant_remplacant_id` : L'enseignant qui remplace
- `classe_id` et `matiere_id` : La classe et matière concernées
- `date_remplacement` : Date du remplacement
- `statut` : Planifié, confirmé, annulé, terminé

**Logique** : Un remplacement est lié à une absence spécifique et implique un enseignant remplaçant.

### **5. Table `historique_enseignement`**
**Objectif** : Suivre le parcours d'enseignement de chaque enseignant au fil des années.

**Colonnes principales** :
- `personnel_id` : L'enseignant
- `classe_id`, `matiere_id`, `annee_scolaire_id` : Contexte d'enseignement
- `date_debut` et `date_fin` : Période d'enseignement
- `heures_total` : Total des heures enseignées
- `statut_fin` : Comment s'est terminée cette période

**Logique** : Archive complète de l'expérience d'enseignement pour chaque enseignant.

## 🔗 **Relations avec les autres modules**

### **Module 1** : `personnels`
- Les enseignants sont des personnels avec des profils spécifiques
- Chaque enseignant a un `user_id` pour l'authentification

### **Module 2** : `classes`, `inscriptions_eleves`
- Les enseignants sont attribués aux classes
- Les élèves sont inscrits dans ces classes

### **Module 4** (futur) : `matieres`
- Les enseignants enseignent des matières spécifiques
- Distinction entre matières françaises et arabes

## 📊 **Exemples d'utilisation**

### **Scénario 1 : Attribution d'un enseignant**
1. L'enseignant est ajouté dans `enseignants_matieres` avec ses compétences
2. Il est attribué à une classe via `enseignants_classes`
3. Son parcours est suivi dans `historique_enseignement`

### **Scénario 2 : Gestion d'une absence**
1. L'absence est déclarée dans `absences_enseignants`
2. Un remplacement est planifié dans `remplacements`
3. L'enseignant remplaçant prend en charge la classe

### **Scénario 3 : Suivi des performances**
1. Les heures enseignées sont comptabilisées dans `enseignants_classes`
2. L'historique est mis à jour dans `historique_enseignement`
3. Les évaluations sont enregistrées pour analyse

## ⚠️ **Contraintes et bonnes pratiques**

### **Contraintes de clés étrangères** :
- `onDelete('cascade')` pour les relations logiques (si un enseignant part, ses attributions sont supprimées)
- `onDelete('restrict')` pour les relations critiques (empêche la suppression d'une classe si des enseignants y sont assignés)

### **Index de performance** :
- Index sur les combinaisons fréquemment utilisées
- Index sur les dates pour les requêtes temporelles
- Index sur les statuts pour les filtres

### **Soft Deletes** :
- Toutes les tables utilisent `softDeletes()` pour conserver l'historique
- Permet de récupérer des données supprimées si nécessaire

## 🚀 **Prochaines étapes**

Le Module 3 est maintenant prêt avec :
- ✅ 5 tables bien structurées
- ✅ Relations claires avec les modules existants
- ✅ Seeder avec données d'exemple
- ✅ Documentation complète

**Modules suivants à développer** :
- **Module 4** : Matières et programmes
- **Module 5** : Emplois du temps
- **Module 6** : Notes et évaluations
