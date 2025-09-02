# 🎯 SCÉNARIO DE GESTION DES COMPTES UTILISATEURS

## 📋 **Vue d'ensemble du Workflow**

### **Acteurs du Système :**
1. **Secrétaire/Personnel Administratif** : Inscrit les élèves, parents et personnel
2. **Super-Admin** : Crée les comptes utilisateurs pour l'authentification
3. **Utilisateurs** : Élèves, parents, personnel qui se connectent

---

## 🔄 **SCÉNARIO COMPLET D'UTILISATION**

### **ÉTAPE 1 : Inscription des Données (Par le Secrétaire)**

#### **1.1 Inscription d'un Élève**
```
Secrétaire → Interface d'inscription élève
├── Saisie des informations personnelles
├── Attribution des classes (FR + AR)
├── Association avec les parents
└── Enregistrement dans la table `eleves`
```

**Résultat :** Élève créé avec `matricule` généré automatiquement, mais **SANS compte utilisateur**

#### **1.2 Inscription d'un Parent**
```
Secrétaire → Interface d'inscription parent
├── Saisie des informations personnelles
├── Association avec l'élève
└── Enregistrement dans la table `parents`
```

**Résultat :** Parent créé, mais **SANS compte utilisateur**

#### **1.3 Inscription d'un Personnel**
```
Secrétaire → Interface d'inscription personnel
├── Saisie des informations personnelles
├── Attribution du poste
└── Enregistrement dans la table `personnels`
```

**Résultat :** Personnel créé avec `matricule` généré automatiquement, mais **SANS compte utilisateur**

---

### **ÉTAPE 2 : Création des Comptes (Par le Super-Admin)**

#### **2.1 Accès à l'Interface de Gestion des Comptes**
```
Super-Admin → Menu "Gestion des Comptes"
├── Vue d'ensemble des utilisateurs sans compte
├── Filtrage par type (élèves, parents, personnel)
└── Actions en lot disponibles
```

#### **2.2 Création de Compte pour un Élève**
```
Super-Admin → Sélection d'un élève sans compte
├── Vérification des informations
├── Génération automatique de l'identifiant (ex: E2024001)
├── Génération automatique du mot de passe temporaire
├── Attribution du profil "ELEVE"
└── Création du compte dans `users` + liaison avec `eleves`
```

**Résultat :**
- Identifiant : `E2024001`
- Mot de passe temporaire : `Ax7Kp9mN`
- Profil : Élève
- Statut : Actif

#### **2.3 Création de Compte pour un Parent**
```
Super-Admin → Sélection d'un parent sans compte
├── Vérification des informations
├── Génération automatique de l'identifiant (ex: P2024001)
├── Génération automatique du mot de passe temporaire
├── Attribution du profil "PARENT"
└── Création du compte dans `users` + liaison avec `parents`
```

**Résultat :**
- Identifiant : `P2024001`
- Mot de passe temporaire : `Bx8Lq0nO`
- Profil : Parent
- Statut : Actif

#### **2.4 Création de Compte pour un Personnel**
```
Super-Admin → Sélection d'un personnel sans compte
├── Vérification des informations
├── Génération automatique de l'identifiant (ex: EMP2024001)
├── Génération automatique du mot de passe temporaire
├── Attribution automatique du profil selon le poste
└── Création du compte dans `users` + liaison avec `personnels`
```

**Résultat :**
- Identifiant : `EMP2024001`
- Mot de passe temporaire : `Cx9Mr1pP`
- Profil : Enseignant/Admin/Personnel (selon le poste)
- Statut : Actif

---

### **ÉTAPE 3 : Utilisation des Comptes (Par les Utilisateurs)**

#### **3.1 Première Connexion**
```
Utilisateur → Page de connexion
├── Saisie de l'identifiant (ex: E2024001)
├── Saisie du mot de passe temporaire
├── Système détecte "première connexion"
└── Redirection vers "Changement de mot de passe obligatoire"
```

#### **3.2 Changement de Mot de Passe Obligatoire**
```
Utilisateur → Interface de changement de mot de passe
├── Saisie du mot de passe temporaire
├── Saisie du nouveau mot de passe
├── Confirmation du nouveau mot de passe
├── Validation des règles de sécurité
└── Enregistrement du nouveau mot de passe
```

#### **3.3 Connexions Suivantes**
```
Utilisateur → Page de connexion
├── Saisie de l'identifiant
├── Saisie du mot de passe personnel
├── Authentification réussie
└── Accès à l'interface selon le profil
```

---

## 🛠️ **INTERFACE SUPER-ADMIN**

### **Écran Principal : "Gestion des Comptes"**

```
┌─────────────────────────────────────────────────────────────┐
│                    GESTION DES COMPTES                      │
├─────────────────────────────────────────────────────────────┤
│ Filtres: [Élèves ▼] [Parents ▼] [Personnel ▼] [Tous ▼]     │
│ Recherche: [________________] [🔍]                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  📊 STATISTIQUES:                                           │
│  • Élèves sans compte: 15                                  │
│  • Parents sans compte: 8                                  │
│  • Personnel sans compte: 3                                │
│                                                             │
│  📋 LISTE DES UTILISATEURS SANS COMPTE:                    │
│                                                             │
│  [✓] Ahmed Ben Ali (E2024001) - CP-A-FR-2024              │
│  [✓] Fatima Ben Ali - Mère d'Ahmed                         │
│  [✓] Marie Dubois (EMP2024001) - Enseignante               │
│  [ ] Sarah Martin (E2024002) - CP-A-FR-2024                │
│  [ ] Jean Dupont - Père de Sarah                           │
│                                                             │
│  ACTIONS:                                                   │
│  [Créer Compte(s) Sélectionné(s)] [Créer Tous]             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 **SÉCURITÉ ET GESTION**

### **Règles de Génération des Identifiants**

#### **Élèves :**
- Format : `E` + année + séquentiel (3 chiffres)
- Exemples : `E2024001`, `E2024002`, `E2024003`
- Unicité garantie par année

#### **Parents :**
- Format : `P` + année + séquentiel (3 chiffres)
- Exemples : `P2024001`, `P2024002`, `P2024003`
- Unicité garantie par année

#### **Personnel :**
- Format : `EMP` + année + séquentiel (3 chiffres)
- Exemples : `EMP2024001`, `EMP2024002`, `EMP2024003`
- Unicité garantie par année

### **Règles de Génération des Mots de Passe Temporaires**

- **Longueur :** 8 caractères
- **Caractères :** Alphanumériques (A-Z, a-z, 0-9)
- **Exemples :** `Ax7Kp9mN`, `Bx8Lq0nO`, `Cx9Mr1pP`
- **Sécurité :** Génération aléatoire cryptographiquement sécurisée

---

## 📱 **API ENDPOINTS**

### **Endpoints de Gestion des Comptes**

```bash
# Liste des utilisateurs sans compte
GET /api/users/without-account?type=all

# Création de comptes
POST /api/users/create-eleve-account
POST /api/users/create-parent-account  
POST /api/users/create-personnel-account

# Gestion des comptes existants
POST /api/users/{id}/reset-password-auto
POST /api/users/{id}/toggle-account-status
```

### **Exemple d'Utilisation API**

```bash
# 1. Récupérer la liste des élèves sans compte
curl -X GET "http://localhost:8000/api/users/without-account?type=eleves" \
  -H "Authorization: Bearer {token}"

# 2. Créer un compte pour un élève
curl -X POST "http://localhost:8000/api/users/create-eleve-account" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"eleve_id": 1}'

# 3. Réinitialiser le mot de passe d'un utilisateur
curl -X POST "http://localhost:8000/api/users/1/reset-password-auto" \
  -H "Authorization: Bearer {token}"
```

---

## 🎯 **AVANTAGES DE CETTE APPROCHE**

### **✅ Séparation des Responsabilités**
- **Secrétaire :** Gestion des données personnelles
- **Super-Admin :** Gestion de la sécurité et des accès
- **Système :** Génération automatique des identifiants

### **✅ Sécurité Renforcée**
- Identifiants générés automatiquement (pas de choix humain)
- Mots de passe temporaires sécurisés
- Changement obligatoire à la première connexion
- Traçabilité complète des actions

### **✅ Flexibilité**
- Possibilité de créer des comptes individuellement ou en lot
- Gestion des profils automatique selon le contexte
- Interface intuitive pour le Super-Admin

### **✅ Conformité**
- Respect des bonnes pratiques de sécurité
- Audit trail complet
- Gestion des permissions granulaires
