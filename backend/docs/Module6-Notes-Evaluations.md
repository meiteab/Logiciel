# 📚 Module 6 : Notes & Évaluations

## 🎯 **Objectif du Module**

Le Module 6 gère l'ensemble du système de notation et d'évaluation des élèves dans le contexte franco-arabe :
- **Création et gestion des évaluations** (contrôles, examens, devoirs)
- **Saisie et validation des notes** par les enseignants
- **Calcul automatique des moyennes** par matière et période
- **Génération des bulletins** avec séparation Français/Arabe
- **Suivi des performances** et classements

## 🏗️ **Architecture des Tables**

### **1. Table `evaluations`** - Types d'évaluations

**Objectif** : Définir les différents types d'évaluations (contrôles, examens, devoirs, etc.)

**Colonnes principales** :
- `code` : Code unique (ex: CT1_FR_CP, EXAM1_AR_CP)
- `nom` : Nom de l'évaluation
- `type` : Type (controle, examen, devoir, interrogation, tp, autre)
- `categorie` : Catégorie (ecrit, oral, pratique, mixte)
- `coefficient` : Coefficient de pondération
- `note_maximale` : Note maximale (20, 10, etc.)
- `periode_id` : Période (trimestre, semestre)
- `matiere_id` + `niveau_id` + `programme_id` : Contexte pédagogique
- `enseignant_id` : Enseignant responsable
- `statut` : Statut (planifie, en_cours, termine, annule)

**Logique** : Chaque évaluation est liée à une matière spécifique, un niveau, un programme (FR/AR) et une période.

---

### **2. Table `notes_eleves`** - Notes individuelles

**Objectif** : Stocker les notes obtenues par chaque élève pour chaque évaluation

**Colonnes principales** :
- `eleve_id` + `evaluation_id` : Élève et évaluation
- `classe_id` + `matiere_id` + `programme_id` : Contexte de classe
- `note_obtenue` : Note obtenue par l'élève
- `note_maximale` : Note maximale de l'évaluation
- `note_ponderee` : Note × coefficient de l'évaluation
- `statut` : Statut de la note (saisie, validee, modifiee, annulee)
- `est_absente` / `est_excuse` / `est_retard` : Gestion des absences
- `commentaires_enseignant` : Commentaires du professeur
- `enseignant_id` : Enseignant qui a saisi la note
- `historique_notes` : Historique des modifications (JSON)

**Logique** : Un élève ne peut avoir qu'une seule note par évaluation (contrainte unique).

---

### **3. Table `moyennes_eleves`** - Moyennes calculées

**Objectif** : Stocker les moyennes calculées par élève, matière, période et programme

**Colonnes principales** :
- `eleve_id` + `matiere_id` + `periode_id` + `programme_id` : Contexte
- `moyenne_notes` : Moyenne simple des notes
- `moyenne_ponderee` : Moyenne pondérée par coefficient
- `moyenne_coefficient` : Moyenne × coefficient de la matière
- `nombre_evaluations` : Nombre total d'évaluations
- `note_minimale` / `note_maximale` : Notes extrêmes
- `rang_classe` / `rang_niveau` : Classements
- `appreciation_generale` : Appréciation du professeur
- `est_calculee` / `est_validee` : Statut de validation

**Logique** : Les moyennes sont calculées automatiquement à partir des notes et peuvent être validées par l'administration.

---

### **4. Table `bulletins_eleves`** - Bulletins générés

**Objectif** : Gérer les bulletins de notes par élève et période

**Colonnes principales** :
- `eleve_id` + `periode_id` : Élève et période
- `numero_bulletin` : Numéro unique du bulletin
- `type_bulletin` : Type (trimestre, semestre, annuel, intermediaire)
- `moyenne_generale_francais` : Moyenne générale programme français
- `moyenne_generale_arabe` : Moyenne générale programme arabe
- `moyenne_generale_totale` : Moyenne générale tous programmes
- `rang_classe_francais` / `rang_classe_arabe` : Classements par programme
- `taux_presence` : Pourcentage de présence
- `appreciation_generale_francais` / `appreciation_generale_arabe` : Appréciations par programme
- `fichier_pdf` : Chemin vers le PDF généré
- `statut` : Statut (en_preparation, valide, publie, archive)

**Logique** : Chaque élève a un bulletin par période, avec séparation claire des programmes français et arabe.

## 🔗 **Relations entre Tables**

```
evaluations (1) ←→ (n) notes_eleves
     ↓                    ↓
periodes (1) ←→ (n) moyennes_eleves
     ↓                    ↓
bulletins_eleves ←→ (n) moyennes_eleves
```

**Flux de données** :
1. **Évaluations** créées par les enseignants
2. **Notes** saisies pour chaque élève
3. **Moyennes** calculées automatiquement
4. **Bulletins** générés à partir des moyennes

## 🎓 **Logique Franco-Arabe**

### **Séparation des Programmes**
- Chaque évaluation est liée à un programme spécifique (FR ou AR)
- Les notes sont séparées par programme
- Les moyennes sont calculées indépendamment pour chaque programme
- Les bulletins affichent les deux programmes séparément

### **Exemple Concret**
```
Élève ID 1 - CP-A (2024-2025)
├── Programme Français (classe_id = 1)
│   ├── Contrôle 1 : 16.5/20
│   ├── Examen 1er Trimestre : 18/20
│   └── Moyenne : 17.25/20
└── Programme Arabe (classe_id = 6)
    ├── Contrôle 1 : 14/20
    ├── Examen 1er Trimestre : 15.5/20
    └── Moyenne : 14.75/20
```

## 📊 **Fonctionnalités Clés**

### **Pour les Enseignants**
- Création d'évaluations avec coefficients et dates limites
- Saisie des notes avec commentaires
- Gestion des absences et retards
- Validation des notes avant publication

### **Pour l'Administration**
- Validation des moyennes calculées
- Génération et publication des bulletins
- Suivi des performances par classe/niveau
- Gestion des périodes d'évaluation

### **Pour les Parents/Élèves**
- Consultation des notes en temps réel
- Accès aux bulletins publiés
- Suivi des progrès par programme
- Consultation des appréciations

## 🚀 **Avantages du Design**

### **Normalisation**
- Pas de redondance de données
- Relations claires entre les entités
- Intégrité référentielle maintenue

### **Évolutivité**
- Support de différents types d'évaluations
- Gestion flexible des coefficients
- Extension facile pour de nouveaux programmes

### **Performance**
- Index optimisés pour les requêtes fréquentes
- Calculs de moyennes automatisés
- Historique des modifications tracé

### **Sécurité**
- Validation des notes par les enseignants
- Traçabilité des modifications
- Gestion des droits d'accès

## 🔧 **Utilisation Technique**

### **Calcul des Moyennes**
```sql
-- Moyenne simple par matière et période
SELECT AVG(note_obtenue) as moyenne
FROM notes_eleves 
WHERE eleve_id = ? AND matiere_id = ? AND periode_id = ?;

-- Moyenne pondérée par coefficient
SELECT SUM(note_ponderee) / SUM(coefficient) as moyenne_ponderee
FROM notes_eleves ne
JOIN evaluations e ON ne.evaluation_id = e.id
WHERE ne.eleve_id = ? AND ne.matiere_id = ? AND ne.periode_id = ?;
```

### **Génération des Bulletins**
```sql
-- Récupération des moyennes pour un bulletin
SELECT 
    me.moyenne_notes,
    me.rang_classe,
    me.appreciation_generale
FROM moyennes_eleves me
WHERE me.eleve_id = ? AND me.periode_id = ?
ORDER BY me.programme_id, me.matiere_id;
```

## 📋 **Prochaines Étapes**

Le Module 6 est maintenant **prêt à être utilisé** pour :
1. **Créer des évaluations** par matière et période
2. **Saisir les notes** des élèves
3. **Calculer automatiquement** les moyennes
4. **Générer des bulletins** complets
5. **Suivre les performances** par programme

**Modules suivants recommandés** :
- **Module 7** : Documents & Génération (templates de bulletins, PDF)
- **Module 8** : Finances (frais de scolarité, paiements)
- **Module 9** : Communication (notifications aux parents)
