# 📝 EXEMPLE D'UTILISATION - INSCRIPTION COMPLÈTE ÉLÈVE + PARENTS

## 🎯 **Nouvelle Approche Optimisée**

### **Workflow Simplifié :**
1. **Inscription en une seule fois** : Élève + Parents simultanément
2. **Super-Admin crée les comptes** : Interface unifiée pour tous
3. **Utilisateurs se connectent** : Avec leurs identifiants respectifs

---

## 📡 **API ENDPOINT**

### **Inscription Complète**
```bash
POST /api/eleves/inscription-complete
```

### **Headers requis :**
```bash
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 📋 **EXEMPLES D'UTILISATION**

### **Exemple 1 : Inscription avec Père et Mère**

```json
{
  "eleve": {
    "prenom": "Ahmed",
    "nom_famille": "Ben Ali",
    "date_naissance": "2018-03-15",
    "lieu_naissance": "Paris",
    "sexe": "M",
    "infos_sante": "Aucun problème de santé",
    "observations_pedagogiques": "Élève motivé, bon niveau en français et arabe"
  },
  "parents": [
    {
      "prenom": "Mohammed",
      "nom_famille": "Ben Ali",
      "civilite": "M",
      "date_naissance": "1980-05-20",
      "lieu_naissance": "Tunis",
      "telephone": "0123456789",
      "telephone_urgence": "0123456789",
      "email": "mohammed.benali@email.com",
      "adresse": "123 Rue de la Paix",
      "ville": "Paris",
      "code_postal": "75001",
      "pays": "France",
      "profession": "Ingénieur",
      "employeur": "TechCorp",
      "telephone_bureau": "0123456790",
      "role": "pere",
      "est_responsable_legal": true,
      "ordre_priorite": 1
    },
    {
      "prenom": "Fatima",
      "nom_famille": "Ben Ali",
      "civilite": "Mme",
      "date_naissance": "1985-08-12",
      "lieu_naissance": "Tunis",
      "telephone": "0123456791",
      "telephone_urgence": "0123456791",
      "email": "fatima.benali@email.com",
      "adresse": "123 Rue de la Paix",
      "ville": "Paris",
      "code_postal": "75001",
      "pays": "France",
      "profession": "Médecin",
      "employeur": "Hôpital Central",
      "telephone_bureau": "0123456792",
      "role": "mere",
      "est_responsable_legal": true,
      "ordre_priorite": 2
    }
  ]
}
```

### **Exemple 2 : Inscription avec Tuteur Unique**

```json
{
  "eleve": {
    "prenom": "Sarah",
    "nom_famille": "Martin",
    "date_naissance": "2017-11-08",
    "lieu_naissance": "Lyon",
    "sexe": "F",
    "infos_sante": "Allergie aux arachides",
    "observations_pedagogiques": "Élève créative, excellente en arts plastiques"
  },
  "parents": [
    {
      "prenom": "Jean",
      "nom_famille": "Martin",
      "civilite": "M",
      "date_naissance": "1975-12-03",
      "lieu_naissance": "Lyon",
      "telephone": "0123456793",
      "telephone_urgence": "0123456793",
      "email": "jean.martin@email.com",
      "adresse": "456 Avenue des Fleurs",
      "ville": "Lyon",
      "code_postal": "69001",
      "pays": "France",
      "profession": "Avocat",
      "employeur": "Cabinet Martin & Associés",
      "telephone_bureau": "0123456794",
      "role": "tuteur",
      "est_responsable_legal": true,
      "ordre_priorite": 1
    }
  ]
}
```

### **Exemple 3 : Inscription avec Informations Minimales**

```json
{
  "eleve": {
    "prenom": "Léa",
    "nom_famille": "Dubois",
    "date_naissance": "2018-06-22",
    "lieu_naissance": "Marseille",
    "sexe": "F"
  },
  "parents": [
    {
      "prenom": "Marie",
      "nom_famille": "Dubois",
      "civilite": "Mme",
      "telephone": "0123456795",
      "email": "marie.dubois@email.com",
      "role": "mere",
      "est_responsable_legal": true,
      "ordre_priorite": 1
    }
  ]
}
```

---

## 📤 **RÉPONSE DE L'API**

### **Succès (201 Created)**

```json
{
  "success": true,
  "message": "Inscription complète réussie",
  "data": {
    "eleve": {
      "id": 1,
      "matricule": "E2024001",
      "nom_complet": "Ahmed Ben Ali",
      "date_naissance": "2018-03-15",
      "parents": [
        {
          "id": 1,
          "nom_complet": "Mohammed Ben Ali",
          "role": "pere",
          "est_responsable_legal": true,
          "telephone": "0123456789",
          "email": "mohammed.benali@email.com"
        },
        {
          "id": 2,
          "nom_complet": "Fatima Ben Ali",
          "role": "mere",
          "est_responsable_legal": true,
          "telephone": "0123456791",
          "email": "fatima.benali@email.com"
        }
      ]
    },
    "message_compte": "L'élève et ses parents ont été inscrits. Le Super-Admin peut maintenant créer leurs comptes utilisateurs."
  }
}
```

### **Erreur de Validation (422 Unprocessable Entity)**

```json
{
  "success": false,
  "message": "Données de validation invalides",
  "errors": {
    "eleve.prenom": ["Le prénom de l'élève est requis."],
    "parents.0.email": ["L'email du parent doit être une adresse email valide."]
  }
}
```

---

## 🔄 **ÉTAPES SUIVANTES**

### **1. Super-Admin crée les comptes**

Après l'inscription réussie, le Super-Admin peut créer les comptes utilisateurs :

```bash
# Créer le compte de l'élève
curl -X POST "http://localhost:8000/api/users/create-eleve-account" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"eleve_id": 1}'

# Créer le compte du père
curl -X POST "http://localhost:8000/api/users/create-parent-account" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"parent_id": 1}'

# Créer le compte de la mère
curl -X POST "http://localhost:8000/api/users/create-parent-account" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"parent_id": 2}'
```

### **2. Identifiants générés**

- **Élève** : `E2024001` + mot de passe temporaire
- **Père** : `P2024001` + mot de passe temporaire  
- **Mère** : `P2024002` + mot de passe temporaire

### **3. Utilisateurs se connectent**

Chaque utilisateur peut maintenant se connecter avec son identifiant et mot de passe temporaire, puis changer son mot de passe.

---

## 🎯 **AVANTAGES DE CETTE APPROCHE**

### **✅ Simplicité**
- Une seule interface pour l'inscription
- Moins de clics pour le secrétaire
- Workflow linéaire et logique

### **✅ Cohérence des Données**
- Élève et parents créés en même temps
- Associations automatiques
- Pas de risque d'oubli

### **✅ Expérience Utilisateur**
- Interface plus intuitive
- Moins de frustration
- Processus plus rapide

### **✅ Maintenance**
- Code plus simple
- Moins de bugs potentiels
- Tests plus faciles

---

## 🛠️ **TEST DE L'API**

### **Commande curl complète :**

```bash
curl -X POST "http://localhost:8000/api/eleves/inscription-complete" \
  -H "Authorization: Bearer {votre_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "eleve": {
      "prenom": "Test",
      "nom_famille": "Élève",
      "date_naissance": "2018-01-01",
      "lieu_naissance": "Test",
      "sexe": "M"
    },
    "parents": [
      {
        "prenom": "Test",
        "nom_famille": "Parent",
        "civilite": "M",
        "telephone": "0123456789",
        "email": "test@example.com",
        "role": "pere",
        "est_responsable_legal": true,
        "ordre_priorite": 1
      }
    ]
  }'
```

Cette approche est beaucoup plus pratique et logique pour les utilisateurs finaux !
