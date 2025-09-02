# 🏫 Module 2 - Spécificité des Écoles Franco-Arabes

## 🎯 **Principe Fondamental**

**Chaque élève doit obligatoirement s'inscrire dans DEUX programmes chaque année scolaire :**

1. **Programme Français** (ex: CP-A Français)
2. **Programme Arabe** (ex: CP-A Arabe)

## 📚 **Exemple Concret**

### **Année 2024-2025 - Élève en CP-A :**
```
Élève ID 1 → CP-A Français  (Programme français)
Élève ID 1 → CP-A Arabe    (Programme arabe)
```

### **Année 2025-2026 - Même élève en CE1-A :**
```
Élève ID 1 → CE1-A Français (Programme français - RÉINSCRIPTION)
Élève ID 1 → CE1-A Arabe   (Programme arabe - RÉINSCRIPTION)
```

## 🏗️ **Structure des Tables**

### **Table `classes`**
- **10 classes** au lieu de 5
- **5 classes françaises** : CP-A, CP-B, CE1-A, CE1-B, 6ème-A
- **5 classes arabes** : CP-A, CP-B, CE1-A, CE1-B, 6ème-A

### **Table `inscriptions_eleves`**
- **Colonne `programme_id`** ajoutée
- **Contrainte unique** : `[eleve_id, classe_id, programme_id, annee_scolaire_id]`
- **2 inscriptions obligatoires** par élève par année

### **Table `classes_niveaux`**
- **Liaison** : classe ↔ niveau ↔ programme ↔ année
- **2 entrées** par classe (une pour chaque programme)

## 🔄 **Types d'Inscription**

### **`type_inscription` :**
- **`nouvelle`** : Première inscription
- **`reinscription`** : Inscription année suivante
- **`redoublement`** : Même niveau, année suivante
- **`transfert`** : Changement de classe

### **`statut` :**
- **`inscrit`** : Inscription active
- **`redoublement`** : Redoublement en cours
- **`transfert`** : Transfert en cours
- **`sortie`** : Sortie de l'école
- **`suspendu`** : Suspension temporaire

## 📊 **Gestion des Effectifs**

### **Capacité par classe :**
- **CP-A Français** : 25 élèves max
- **CP-A Arabe** : 25 élèves max
- **Total CP-A** : 50 élèves max (25 × 2 programmes)

### **Suivi des effectifs :**
- `capacite_actuelle` mise à jour automatiquement
- Contrôle que chaque élève a ses 2 inscriptions
- Validation qu'aucune classe n'est surchargée

## 🚨 **Contraintes de Validation**

### **Obligatoires :**
1. **2 inscriptions par élève** par année scolaire
2. **1 programme français** + **1 programme arabe**
3. **Même niveau** dans les deux programmes
4. **Dates d'inscription** identiques

### **Interdictions :**
1. **Double inscription** dans le même programme
2. **Inscription** dans des niveaux différents
3. **Classe surchargée** (capacité dépassée)

## 💡 **Avantages de cette Structure**

1. **Flexibilité** : Gestion séparée des programmes
2. **Traçabilité** : Historique complet des parcours
3. **Contrôle** : Validation automatique des inscriptions
4. **Évolutivité** : Prêt pour les modules suivants
5. **Conformité** : Respect des exigences franco-arabes

## 🔍 **Requêtes d'Exemple**

### **Vérifier qu'un élève a ses 2 inscriptions :**
```sql
SELECT eleve_id, COUNT(*) as nb_inscriptions
FROM inscriptions_eleves 
WHERE annee_scolaire_id = 1 
GROUP BY eleve_id 
HAVING COUNT(*) = 2;
```

### **Lister les élèves par programme :**
```sql
SELECT e.prenom, e.nom_famille, p.nom as programme, c.nom as classe
FROM inscriptions_eleves ie
JOIN eleves e ON ie.eleve_id = e.id
JOIN programmes p ON ie.programme_id = p.id
JOIN classes c ON ie.classe_id = c.id
WHERE ie.annee_scolaire_id = 1;
```

## 📝 **Notes Importantes**

- **Chaque année** nécessite une nouvelle inscription
- **Réinscription** = processus automatique pour l'année suivante
- **Transfert** = changement de classe dans le même programme
- **Redoublement** = même niveau, année suivante, deux programmes 