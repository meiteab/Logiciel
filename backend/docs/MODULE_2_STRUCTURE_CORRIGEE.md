# 🏫 Module 2 - Structure Corrigée (1 Ligne par Élève)

## 🎯 **Principe Clé - CORRIGÉ**

**Chaque élève a 1 SEULE ligne dans la table `inscriptions_eleves` par année scolaire avec :**

- **`classe_francaise_id`** : ID de la classe française
- **`classe_arabe_id`** : ID de la classe arabe

## 📊 **Structure de la Table `inscriptions_eleves`**

### **Colonnes principales :**
```sql
CREATE TABLE inscriptions_eleves (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    eleve_id BIGINT UNSIGNED NOT NULL,
    classe_francaise_id BIGINT UNSIGNED NOT NULL,  -- Classe française
    classe_arabe_id BIGINT UNSIGNED NOT NULL,      -- Classe arabe
    annee_scolaire_id BIGINT UNSIGNED NOT NULL,
    date_inscription DATE NOT NULL,
    date_sortie DATE NULL,
    type_inscription ENUM('nouvelle', 'reinscription', 'redoublement', 'transfert'),
    statut ENUM('inscrit', 'redoublement', 'transfert', 'sortie', 'suspendu'),
    motif_sortie TEXT NULL,
    notes_administratives TEXT NULL,
    timestamps,
    softDeletes
);
```

### **Contrainte unique :**
```sql
UNIQUE KEY uk_eleve_annee (eleve_id, annee_scolaire_id)
```

## 📚 **Exemple Concret - Élève en CP-A (2024-2025)**

### **1 SEULE LIGNE dans la table :**
```sql
INSERT INTO inscriptions_eleves VALUES (
    1,           -- id
    1,           -- eleve_id (élève ID 1)
    1,           -- classe_francaise_id (CP-A Français)
    6,           -- classe_arabe_id (CP-A Arabe)
    1,           -- annee_scolaire_id (2024-2025)
    '2024-09-01', -- date_inscription
    'nouvelle',   -- type_inscription
    'inscrit',    -- statut
    ...
);
```

## 🔍 **Requêtes de Validation**

### **1. Vérifier qu'un élève a ses 2 classes :**
```sql
SELECT 
    e.prenom,
    e.nom_famille,
    cf.nom as classe_francaise,
    ca.nom as classe_arabe,
    ie.date_inscription
FROM inscriptions_eleves ie
JOIN eleves e ON ie.eleve_id = e.id
JOIN classes cf ON ie.classe_francaise_id = cf.id
JOIN classes ca ON ie.classe_arabe_id = ca.id
WHERE ie.annee_scolaire_id = 1;
```

### **2. Trouver les élèves sans inscription :**
```sql
SELECT e.id, e.prenom, e.nom_famille
FROM eleves e
LEFT JOIN inscriptions_eleves ie ON e.id = ie.eleve_id AND ie.annee_scolaire_id = 1
WHERE ie.id IS NULL;
```

### **3. Vérifier les capacités des classes :**
```sql
SELECT 
    c.nom as classe,
    c.capacite_max,
    c.capacite_actuelle,
    COUNT(ie.id) as nb_eleves
FROM classes c
LEFT JOIN inscriptions_eleves ie ON (
    c.id = ie.classe_francaise_id OR c.id = ie.classe_arabe_id
) AND ie.annee_scolaire_id = 1
GROUP BY c.id, c.nom, c.capacite_max, c.capacite_actuelle;
```

## 🚨 **Contraintes de Validation**

### **Obligatoires :**
1. **1 ligne** par élève par année
2. **1 classe française** + **1 classe arabe**
3. **Même niveau** dans les deux classes
4. **Dates d'inscription** identiques

### **Interdictions :**
1. **Double inscription** du même élève la même année
2. **Classe manquante** (française ou arabe)
3. **Niveaux différents** entre français et arabe

## 💡 **Avantages de cette Approche**

1. **Simplicité** : 1 ligne = 1 inscription complète
2. **Formulaire unique** : Un seul formulaire d'inscription
3. **Traçabilité** : Historique clair des parcours
4. **Validation** : Contrôle automatique des inscriptions
5. **Performance** : Moins de lignes, requêtes plus rapides

## 📝 **Processus d'Inscription**

### **Formulaire d'inscription :**
```
Élève : [Sélection]
Année : [2024-2025]
Classe Française : [CP-A Français ▼]
Classe Arabe : [CP-A Arabe ▼]
Date d'inscription : [01/09/2024]
Type : [Nouvelle ▼]
```

### **Résultat en base :**
```
1 ligne avec :
- eleve_id = 1
- classe_francaise_id = 1 (CP-A Français)
- classe_arabe_id = 6 (CP-A Arabe)
- annee_scolaire_id = 1 (2024-2025)
```

## 🎯 **Résumé**

**Structure finale :**
- **1 élève** = **1 ligne** par année
- **1 ligne** = **2 classes** (française + arabe)
- **Formulaire unique** = **Inscription complète**

**C'est beaucoup plus logique et simple !** 🎉 