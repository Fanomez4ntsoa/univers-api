# CLAUDE.md — AbracadaBati v2

> **Lis ce fichier entièrement avant de faire quoi que ce soit.**
> Il contient toutes les décisions d'architecture, les règles du projet, et le contexte nécessaire pour travailler correctement.

---

## 🧠 Règle fondamentale — À ne jamais oublier

**Avant de créer ou modifier quoi que ce soit :**

1. **Vérifier d'abord** ce que le projet Emergent (`AbracadaBati`) a déjà fait
2. **Extraire** ce qui est pertinent
3. **Adapter** à notre stack Laravel + DDD
4. **Enrichir** seulement si nécessaire

> Ne jamais inventer une structure, un champ, un endpoint ou un rôle sans avoir vérifié s'il existe déjà dans le projet Emergent de référence (`~/project/AbracadaBati/`).

---

## 📌 Contexte du projet

### Vision
**AbracadaBati** est un univers du système AbracadaWorld — une plateforme dédiée aux artisans du bâtiment.

```
AbracadaWorld Core (auth centrale — port 8000)
└── abracadabativ2 (univers Bati — port 8001)
    ├── CRM Pro : prospects, clients, devis, factures, chantiers
    ├── Ecosystem social : posts, shops, listings, jobs
    └── Matching : demandes de travaux → artisans
```

### Ce repo : `abracadabativ2`
- Univers Laravel 12 indépendant
- Auth déléguée au Core via `GET /api/me`
- Base de données propre : `abracadabativ2`
- Chaque artisan possède ses propres données CRM (isolation par `owner_id`)

### Projet de référence : `AbracadaBati`
- Chemin local : `~/project/AbracadaBati/`
- Stack : FastAPI + React + MongoDB
- Généré par Emergent Agent
- **C'est la référence métier** — tout ce qu'on construit doit s'en inspirer

---

## ✅ Stack technique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 12 |
| Base de données | MySQL — `abracadabativ2` |
| Auth | Délégée au Core via `/api/me` (pas de JWT local) |
| Port local | 8001 |
| Déploiement | Railway |

---

## 🔐 Authentification — Comment ça fonctionne

### Flux complet
```
Client → POST /api/auth/login sur le Core (port 8000)
       ← reçoit un token JWT

Client → GET /api/batiment/prospects sur abracadabativ2 (port 8001)
         Authorization: Bearer <token_jwt>

CoreAuthMiddleware → GET http://localhost:8000/api/me
                     Authorization: Bearer <token_jwt>
                   ← reçoit le profil plat (voir structure ci-dessous)

CoreAuthService::syncUser() → updateOrCreate dans users locale
                             → attache $user à $request->attributes->get('auth_user')
```

### Structure de réponse du Core `GET /api/me` — OBJET PLAT
```json
{
  "id": "uuid-string",
  "user_id": "uuid-string",
  "email": "artisan@example.com",
  "username": "jdupont",
  "display_name": "Jean Dupont",
  "user_type": "professionnel",
  "profile_photo": null,
  "bio": null,
  "city": "Paris",
  "company_name": "Dupont Rénovation",
  "metier": "Plombier",
  "is_verified": false,
  "identity_status": "pending",
  "role": "professionnel",
  "has_pro_subscription": false,
  "shop_enabled": false
}
```

> ⚠️ Le Core retourne un **objet plat**, pas `{ user: {...}, profile: {...} }`.
> `id` et `user_id` contiennent tous les deux le même UUID.
> Notre `CoreAuthService::syncUser()` lit directement `$coreData['id']`.

### Accéder à l'utilisateur dans un controller
```php
/** @var \App\Models\User $user */
$user = $request->attributes->get('auth_user');
// $user->id        → ID local MySQL (entier)
// $user->core_uuid → UUID du Core
// $user->role      → rôle provenant du Core
```

### Middleware enregistré
```php
// bootstrap/app.php
$middleware->alias([
    'core.auth' => \App\Http\Middleware\CoreAuthMiddleware::class,
]);

// Utilisation dans routes/api.php
Route::middleware('core.auth')->group(function () { ... });
```

---

## 🏗️ Architecture DDD Modulaire

### Structure des modules
```
app/
├── Modules/
│   ├── Auth/
│   │   └── Services/
│   │       └── CoreAuthService.php   # Appel /api/me + sync user local
│   └── CRM/
│       ├── Controllers/              # Léger — reçoit, délègue, retourne
│       ├── Requests/                 # Toute la validation
│       └── Services/                 # Toute la logique métier
├── Http/
│   └── Middleware/
│       └── CoreAuthMiddleware.php    # Intercepte toutes les requêtes protégées
└── Models/
    ├── User.php                      # Synchro depuis Core
    ├── Prospect.php
    ├── Client.php
    ├── Quote.php
    ├── Invoice.php
    ├── Chantier.php
    └── CompanySetting.php
```

### Règles d'architecture
- **Controller** → reçoit la requête, appelle le Service, retourne JsonResponse. Pas de logique métier.
- **Request** → toute la validation. Jamais de `$request->validate()` dans un controller.
- **Service** → toute la logique métier. C'est ici qu'on crée, modifie, supprime.
- **Model** → Eloquent pur. Relations, casts, scopes. Pas de logique métier.

### Syntaxe Laravel 12 (PHP Attributes)
```php
// ✅ Correct — Laravel 12
#[Fillable(['owner_id', 'name', 'phone'])]
class Prospect extends Model { ... }

// ❌ Incorrect — ancienne syntaxe
protected $fillable = ['owner_id', 'name'];
```

---

## 🗄️ Base de données — Tables migrées

### Multi-tenancy par `owner_id`
Chaque enregistrement CRM appartient à un artisan via `owner_id` (clé étrangère vers `users.id` local).
Toutes les requêtes sont filtrées par `owner_id`.

### Tables

| Table | Description | Clé métier |
|-------|-------------|------------|
| `users` | Synchro depuis Core `/me` | `core_uuid` (UUID du Core) |
| `prospects` | Contacts avant conversion | `owner_id`, `status`, `pipeline_stage` |
| `clients` | Convertis depuis prospect | `owner_id`, `prospect_id` |
| `quotes` | Devis avec lignes JSON | `owner_id`, `client_id`, `status` |
| `invoices` | Factures liées aux devis | `owner_id`, `client_id`, `quote_id`, `status` |
| `chantiers` | Chantiers avec géolocalisation | `owner_id`, `client_id`, `quote_id`, `status` |
| `company_settings` | Paramètres artisan (1 par user) | `user_id` (unique) |

### Enums importants
```
chantier_type : renovation | construction | extension | plomberie | electricite
                peinture | toiture | carrelage | maconnerie | autre

prospect.status       : new | contacted | qualified | converted | lost
prospect.pipeline_stage : prospect | devis | negociation | signe | perdu

quote.status   : draft | sent | accepted | refused | expired
invoice.status : draft | sent | pending | paid | overdue | cancelled
chantier.status : to_plan | planned | started | in_progress | completed | cancelled
chantier.pipeline_stage : planification | en_cours | reception | facture | clos
```

---

## 🔌 API — Endpoints existants

### Protégés (middleware `core.auth` — Bearer JWT du Core)
```
GET    /api/batiment/prospects                      → Liste des prospects
POST   /api/batiment/prospects                      → Créer un prospect
GET    /api/batiment/prospects/{id}                 → Détail
PUT    /api/batiment/prospects/{id}                 → Modifier
DELETE /api/batiment/prospects/{id}                 → Supprimer
POST   /api/batiment/prospects/{id}/convert-to-client → Convertir en client

GET    /api/batiment/clients                        → Liste des clients
POST   /api/batiment/clients                        → Créer un client
GET    /api/batiment/clients/{id}                   → Détail enrichi (+ quotes, invoices, chantiers, notes)
PUT    /api/batiment/clients/{id}                   → Modifier
DELETE /api/batiment/clients/{id}                   → Supprimer
POST   /api/batiment/clients/{id}/notes             → Ajouter une note
POST   /api/batiment/clients/{id}/generate-portal-token → Générer un token d'accès client
```

---

## 📁 Modules — État d'avancement

### ✅ Terminé
- `CoreAuthMiddleware` + `CoreAuthService` → connexion au Core via `/api/me`
- Migration `users` locale avec `core_uuid`
- Migrations CRM : prospects, clients, quotes, invoices, chantiers, company_settings, client_notes
- Module CRM `Prospects` : CRUD complet + `POST /{id}/convert-to-client`
- Module CRM `Clients` : CRUD + notes + portal token + conversion depuis prospect + compteurs stats

### 🔄 À faire (CRM)
- Module `Quotes` → CRUD + envoi + signature + calcul automatique
- Module `Invoices` → CRUD + marquage payé + génération PDF
- Module `Chantiers` → CRUD + pipeline + géolocalisation + assigned_workers
- Module `CompanySettings` → GET + PUT (un enregistrement par artisan)

### 📋 Plus tard
- Ecosystem social : posts, shops, listings, jobs, events
- Module matching : `project_requests` → artisans
- Vérification du rôle `universe_slug = bati` sur le Core

---

## ⚙️ Configuration locale

### Lancer le projet
```bash
cd ~/project/abracadabativ2
php artisan serve --port=8001
```

### Tester l'API
1. Se connecter sur le Core (port 8000) : `POST http://localhost:8000/api/auth/login`
2. Récupérer le token JWT
3. Appeler abracadabativ2 avec ce token :
   ```
   GET http://localhost:8001/api/batiment/prospects
   Authorization: Bearer <token>
   Accept: application/json
   ```

### Variables `.env` clés
```env
APP_NAME=AbracadaBati
APP_URL=http://localhost:8001
CORE_API_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=abracadabativ2
DB_USERNAME=admin
DB_PASSWORD=admin2025
SESSION_DRIVER=file
CACHE_STORE=file
```

---

## 🚫 Ce qu'on ne fait PAS

- ❌ Pas de `$request->validate()` dans les controllers
- ❌ Pas de logique métier dans les controllers
- ❌ Pas de JWT local (auth uniquement via Core)
- ❌ Pas de Sanctum
- ❌ Pas d'interfaces Service pour l'instant
- ❌ Pas de création sans vérifier Emergent d'abord

---

## 📂 Projets locaux

| Projet | Chemin | Rôle |
|--------|--------|------|
| `abracadaworld-core` | `~/project/abracadaworld-core/` | Core Laravel — auth centrale — port 8000 |
| `abracadabativ2`     | `~/project/abracadabativ2/`     | Univers Bati Laravel — port 8001 |
| `AbracadaBati`       | `~/project/AbracadaBati/`       | Référence Emergent (FastAPI + React) |

---

## 🌿 Git Workflow — Règles obligatoires

### Branche principale
- `main` est la branche de production — on n'y pousse jamais directement

### Nomenclature des branches
| Type | Préfixe | Exemple |
|------|---------|---------|
| Nouvelle fonctionnalité | `feature/` | `feature/crm-quotes` |
| Correction de bug | `fix/` | `fix/prospect-conversion` |
| Refactoring | `refactor/` | `refactor/core-auth-service` |
| Configuration / maintenance | `chore/` | `chore/update-env-example` |
| Documentation | `docs/` | `docs/update-claude-md` |

### Cycle de vie d'une branche
```bash
# 1. Toujours partir de main à jour
git checkout main && git pull

# 2. Créer la branche
git checkout -b feature/nom-du-module

# 3. Développer + committer au fil de l'eau
git commit -m "feat: description claire"

# 4. Tests validés sur la branche
# 5. Soumettre pour validation (Fanomezantsoa valide)
# 6. Merge vers main uniquement après accord
# 7. Retests sur main après merge
```

### Gestion des conflits
- Claude **ne résout jamais un conflit seul**
- En cas de conflit : **signaler + expliquer + proposer la résolution**
- **Fanomezantsoa valide** la résolution avant que Claude applique quoi que ce soit

*Dernière mise à jour : 31 Mars 2026 — Module Clients terminé (CRUD + notes + portal token + conversion)*
*Rédigé par : Fanomezantsoa + Claude*
