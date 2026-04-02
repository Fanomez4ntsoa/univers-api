# CLAUDE.md — AbracadaBati v2

> **Lire aussi : `ROADMAP_MIGRATION.md`** contient l'état d'avancement
> complet de la migration Emergent → Laravel. À consulter avant chaque nouveau module.

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
| `users` | Synchro depuis Core `/me` | `core_uuid`, `bio`, `identity_status`, `shop_enabled`, `followers_count`, `following_count`, `posts_count` |
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
GET    /api/batiment/prospects                      → Liste des prospects                                               → **(testé et validé Insomnia)**
POST   /api/batiment/prospects                      → Créer un prospect                                                 → **(testé et validé Insomnia)**
GET    /api/batiment/prospects/{id}                 → Détail                                                            → **(testé et validé Insomnia)**
PUT    /api/batiment/prospects/{id}                 → Modifier                                                          → **(testé et validé Insomnia)**
DELETE /api/batiment/prospects/{id}                 → Supprimer                                                         → **(testé et validé Insomnia)**
POST   /api/batiment/prospects/{id}/convert-to-client → Convertir en client                                             → **(testé et validé Insomnia)**

GET    /api/batiment/clients                        → Liste des clients                                                 → **(testé et validé Insomnia)**
POST   /api/batiment/clients                        → Créer un client                                                   → **(testé et validé Insomnia)**
GET    /api/batiment/clients/{id}                   → Détail enrichi (+ quotes, invoices, chantiers, notes)             → **(testé et validé Insomnia)**
PUT    /api/batiment/clients/{id}                   → Modifier                                                          → **(testé et validé Insomnia)**
DELETE /api/batiment/clients/{id}                   → Supprimer                                                         → **(testé et validé Insomnia)**
POST   /api/batiment/clients/{id}/notes             → Ajouter une note                                                  → **(testé et validé Insomnia)**
POST   /api/batiment/clients/{id}/generate-portal-token → Générer un token d'accès client                               → **(testé et validé Insomnia)**

GET    /api/batiment/quotes                        → Liste des devis (?status=, ?client_id=)                            → **(testé et validé Insomnia)**
POST   /api/batiment/quotes                        → Créer un devis (calcul auto des totaux)                            → **(testé et validé Insomnia)**
GET    /api/batiment/quotes/{id}                   → Détail                                                             → **(testé et validé Insomnia)**
PUT    /api/batiment/quotes/{id}                   → Modifier (bloqué si accepted/invoiced)                             → **(testé et validé Insomnia)**
DELETE /api/batiment/quotes/{id}                   → Supprimer (bloqué si accepted/invoiced)                            → **(testé et validé Insomnia)**
POST   /api/batiment/quotes/{id}/send              → Marquer envoyé                                                     → **(testé et validé Insomnia)**
POST   /api/batiment/quotes/{id}/sign              → Signer (accepter)                                                  → **(testé et validé Insomnia)**
                                                    Body requis : { signature_image: "data:image/png;base64,...", signed_by: "Nom", signed_at: "YYYY-MM-DD" }
POST   /api/batiment/quotes/{id}/duplicate         → Dupliquer en brouillon                                             → **(testé et validé Insomnia)**
POST   /api/batiment/quotes/{id}/convert-invoice   → Convertir en facture                                               → **(testé et validé Insomnia)**

GET    /api/batiment/invoices                      → Liste des factures (?status=, ?client_id=)                         → **(testé et validé Insomnia)**
POST   /api/batiment/invoices                      → Créer une facture (calcul auto des totaux)                         → **(testé et validé Insomnia)**
GET    /api/batiment/invoices/{id}                 → Détail (+ quote liée)                                              → **(testé et validé Insomnia)**
PUT    /api/batiment/invoices/{id}                 → Modifier (bloqué si pas draft)                                     → **(testé et validé Insomnia)**
DELETE /api/batiment/invoices/{id}                 → Supprimer (bloqué si paid/partial)                                 → **(testé et validé Insomnia)**
POST   /api/batiment/invoices/{id}/send            → Marquer envoyée                                                    → **(testé et validé Insomnia)**
POST   /api/batiment/invoices/{id}/mark-paid       → Marquer payée (met à jour amount_paid/due + client revenue)        → **(testé et validé Insomnia)**
POST   /api/batiment/invoices/{id}/cancel          → Annuler (bloqué si paid)                                           → **(testé et validé Insomnia)**

GET    /api/batiment/chantiers                     → Liste des chantiers (?status=)                                     → **(testé et validé Insomnia)**
POST   /api/batiment/chantiers                     → Créer un chantier (auto-remplissage depuis devis)                  → **(testé et validé Insomnia)**
GET    /api/batiment/chantiers/pipeline            → Vue pipeline groupée par stage                                     → **(testé et validé Insomnia)**
GET    /api/batiment/chantiers/{id}                → Détail enrichi (+ documents, comments, time_entries, costs)        → **(testé et validé Insomnia)**
PUT    /api/batiment/chantiers/{id}                → Modifier                                                           → **(testé et validé Insomnia)**
DELETE /api/batiment/chantiers/{id}                → Supprimer (bloqué si in_progress/completed)                        → **(testé et validé Insomnia)**
PUT    /api/batiment/chantiers/{id}/move-stage      → Déplacer dans le pipeline (auto actual_start/end_date)            → **(testé et validé Insomnia)**        
POST   /api/batiment/chantiers/{id}/documents       → Ajouter un document                                               → **(testé et validé Insomnia)**
DELETE /api/batiment/chantiers/{id}/documents/{docId} → Supprimer un document                                           → **(testé et validé Insomnia)**
POST   /api/batiment/chantiers/{id}/comments        → Ajouter un commentaire                                            → **(testé et validé Insomnia)**
POST   /api/batiment/chantiers/{id}/time-entries     → Ajouter du temps (recalcule rentabilité)                         → **(testé et validé Insomnia)**
POST   /api/batiment/chantiers/{id}/costs            → Ajouter un coût (recalcule rentabilité)                          → **(testé et validé Insomnia)**

GET    /api/batiment/settings/company               → Paramètres entreprise (auto-créé si inexistant)                   → **(testé et validé Insomnia)**
PUT    /api/batiment/settings/company               → Mettre à jour (quote_counter/invoice_counter non modifiables)     → **(testé et validé Insomnia)**
```

### Publics — Client Portal (sans auth Core, accès par portal_token)
```
GET    /api/portal/{token}                → Dashboard client (info + quotes + invoices + chantiers)                     → **(testé et validé Insomnia)**
GET    /api/portal/{token}/quotes         → Liste des devis (hors drafts)                                               → **(testé et validé Insomnia)**
GET    /api/portal/{token}/quotes/{id}    → Détail devis + CGV artisan (auto sent→viewed)                               → **(testé et validé Insomnia)**
POST   /api/portal/{token}/quotes/{id}/sign → Signer un devis côté client                                               → **(testé et validé Insomnia)**
GET    /api/portal/{token}/invoices       → Liste des factures (hors drafts)                                            → **(testé et validé Insomnia)**
GET    /api/portal/{token}/invoices/{id}  → Détail d'une facture                                                        → **(testé et validé Insomnia)**
```

### Ecosystem Social (Phase 3 — middleware `core.auth`)
```
GET    /api/ecosystem/posts                        → Feed paginé (15/page)                                                → **(testé et validé Insomnia)**
POST   /api/ecosystem/posts                        → Créer un post                                                        → **(testé et validé Insomnia)**
GET    /api/ecosystem/posts/{id}                   → Détail post                                                          → **(testé et validé Insomnia)**
PUT    /api/ecosystem/posts/{id}                   → Modifier (son post)                                                  → **(testé et validé Insomnia)**
DELETE /api/ecosystem/posts/{id}                   → Supprimer (son post)                                                 → **(testé et validé Insomnia)**
POST   /api/ecosystem/posts/{id}/like              → Like/Unlike toggle                                                   → **(testé et validé Insomnia)**
POST   /api/ecosystem/posts/{id}/comments          → Ajouter un commentaire                                               → **(testé et validé Insomnia)**
GET    /api/ecosystem/posts/{id}/comments          → Liste des commentaires                                               → **(testé et validé Insomnia)**

GET    /api/ecosystem/shop                         → Ma boutique (auto-créée)                                             → **(testé et validé Insomnia)**
PUT    /api/ecosystem/shop                         → Mettre à jour ma boutique                                            → **(testé et validé Insomnia)**
GET    /api/ecosystem/shop/products                → Mes produits                                                         → **(testé et validé Insomnia)**
POST   /api/ecosystem/shop/products                → Ajouter un produit                                                   → **(testé et validé Insomnia)**
PUT    /api/ecosystem/shop/products/{id}           → Modifier un produit                                                  → **(testé et validé Insomnia)**
DELETE /api/ecosystem/shop/products/{id}           → Supprimer un produit                                                 → **(testé et validé Insomnia)**
GET    /api/ecosystem/shops                        → Boutiques actives (public)                                           → **(testé et validé Insomnia)**
GET    /api/ecosystem/shops/{slug}                 → Détail boutique + produits (public)                                  → **(testé et validé Insomnia)**

GET    /api/ecosystem/listings/my                  → Mes annonces                                                         → **(testé et validé Insomnia)**
POST   /api/ecosystem/listings                     → Créer une annonce                                                    → **(testé et validé Insomnia)**
PUT    /api/ecosystem/listings/{id}                → Modifier (la sienne)                                                 → **(testé et validé Insomnia)**
DELETE /api/ecosystem/listings/{id}                → Supprimer (la sienne)                                                → **(testé et validé Insomnia)**
POST   /api/ecosystem/listings/{id}/sold           → Marquer vendu                                                        → **(testé et validé Insomnia)**
GET    /api/ecosystem/listings                     → Annonces actives (public, ?category=, ?city=, ?price_type=)          → **(testé et validé Insomnia)**
GET    /api/ecosystem/listings/{id}                → Détail annonce (public, incrémente views_count)                      → **(testé et validé Insomnia)**

GET    /api/ecosystem/jobs                         → Offres actives (?category=, ?city=, ?contract_type=)                 → **(testé et validé Insomnia)**
POST   /api/ecosystem/jobs                         → Publier une offre                                                    → **(testé et validé Insomnia)**
GET    /api/ecosystem/jobs/{id}                    → Détail offre                                                         → **(testé et validé Insomnia)**
PUT    /api/ecosystem/jobs/{id}                    → Modifier (la sienne)                                                 → **(testé et validé Insomnia)**
DELETE /api/ecosystem/jobs/{id}                    → Supprimer (la sienne)                                                → **(testé et validé Insomnia)**
POST   /api/ecosystem/jobs/{id}/apply              → Postuler                                                             → **(testé et validé Insomnia)**
GET    /api/ecosystem/jobs/{id}/applications       → Candidatures (owner)                                                 → **(testé et validé Insomnia)**

GET    /api/ecosystem/events                       → Liste événements (?event_type=, ?city=)                              → **(testé et validé Insomnia)**
POST   /api/ecosystem/events                       → Créer un événement                                                   → **(testé et validé Insomnia)**
GET    /api/ecosystem/events/{id}                  → Détail événement                                                     → **(testé et validé Insomnia)**
PUT    /api/ecosystem/events/{id}                  → Modifier (le sien)                                                   → **(testé et validé Insomnia)**
DELETE /api/ecosystem/events/{id}                  → Supprimer (le sien)                                                  → **(testé et validé Insomnia)**
POST   /api/ecosystem/events/{id}/attend           → S'inscrire/Se désinscrire toggle                                    → **(testé et validé Insomnia)**

GET    /api/ecosystem/users                        → Découvrir les artisans (20/page + is_following)                      → **(testé et validé Insomnia)**
GET    /api/ecosystem/users/{id}                   → Profil public + 5 derniers posts                                    → **(testé et validé Insomnia)**
POST   /api/ecosystem/users/{id}/follow            → Follow/Unfollow toggle                                               → **(testé et validé Insomnia)**
GET    /api/ecosystem/users/{id}/followers         → Liste des followers                                                  → **(testé et validé Insomnia)**
GET    /api/ecosystem/users/{id}/following         → Liste des abonnements                                                → **(testé et validé Insomnia)**
GET    /api/ecosystem/feed                         → Feed personnalisé (posts des suivis, 15/page)                        → **(testé et validé Insomnia)**
GET    /api/ecosystem/profile                      → Mon profil avec stats                                                → **(testé et validé Insomnia)**
```

### Matching (Phase 4 — middleware `core.auth`)
```
GET    /api/matching/requests                           → Mes demandes                                    → **(testé et validé Insomnia)**
POST   /api/matching/requests                           → Créer une demande                               → **(testé et validé Insomnia)**
GET    /api/matching/requests/{id}                      → Détail + devis reçus                            → **(testé et validé Insomnia)**
PUT    /api/matching/requests/{id}                      → Modifier (bloqué si matched/closed)             → **(testé et validé Insomnia)**
DELETE /api/matching/requests/{id}                      → Supprimer (bloqué si matched)                   → **(testé et validé Insomnia)**
POST   /api/matching/requests/{id}/close                → Fermer la demande                               → **(testé et validé Insomnia)**
POST   /api/matching/requests/{id}/quotes/{id}/accept   → Accepter un devis (matched + refused les autres) → **(testé et validé Insomnia)**
GET    /api/matching/available                          → Demandes disponibles (côté artisan)             → **(testé et validé Insomnia)**
POST   /api/matching/requests/{id}/quote                → Soumettre un devis (bloqué si déjà soumis)      → **(testé et validé Insomnia)**
GET    /api/matching/my-quotes                          → Mes devis soumis                                → **(testé et validé Insomnia)**
```

---

## 📁 Modules — État d'avancement

### ✅ Terminé
- `CoreAuthMiddleware` + `CoreAuthService` → connexion au Core via `/api/me`
- Migration `users` locale avec `core_uuid`
- Migrations CRM : prospects, clients, quotes, invoices, chantiers, company_settings, client_notes
- Module CRM `Prospects` : CRUD complet + `POST /{id}/convert-to-client` *(testé et validé Insomnia)*
- Module CRM `Clients` : CRUD + notes + portal token + conversion depuis prospect + compteurs stats *(testé et validé Insomnia)*
- Module CRM `Quotes` : CRUD + calcul auto totaux + send + sign + duplicate + convert-to-invoice *(testé et validé Insomnia)*
- Module CRM `Invoices` : CRUD + send + mark-paid + cancel + client denormalization + auto totaux *(testé et validé Insomnia)*
- Module CRM `Chantiers` : CRUD + pipeline + move-stage + documents + comments + time-entries + costs + rentabilité auto *(testé et validé Insomnia)*
- Module CRM `CompanySettings` : GET (auto-create) + PUT (counters protégés) *(testé et validé Insomnia)*
- Module `ClientPortal` : dashboard + quotes + invoices + signature devis (routes publiques sans auth Core) *(testé et validé Insomnia)*

- Module Ecosystem `Posts/Feed` : CRUD + feed paginé + like toggle + comments *(testé et validé Insomnia)*
- Module Ecosystem `Shops` : gestion boutique (auto-create) + produits CRUD + consultation publique par slug *(testé et validé Insomnia)*
- Module Ecosystem `Listings` : annonces CRUD + mark-sold + consultation publique filtrable + expiration 30j + views_count *(testé et validé Insomnia)*
- Module Ecosystem `Jobs` : offres CRUD + postuler (bloqué si déjà candidat) + candidatures (owner only) + expiration 60j *(testé et validé Insomnia)*
- Module Ecosystem `Events` : événements CRUD + toggle inscription (bloqué si complet) + attendees_count auto *(testé et validé Insomnia)*
- Module Ecosystem `Social` : discover users + profil public + follow/unfollow toggle + followers/following + feed personnalisé + mon profil *(testé et validé Insomnia)*

- Module Matching : demandes de travaux + devis artisans + accept/refuse + available + my-quotes *(testé et validé Insomnia)*

### 📋 À faire (Phase 5+)
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
git commit -m "[FEAT]: description claire"

# 4. Tests validés sur la branche
# 5. Soumettre pour validation (Fanomezantsoa valide)
# 6. Merge vers main uniquement après accord
# 7. Retests sur main après merge
```

### Gestion des conflits
- Claude **ne résout jamais un conflit seul**
- En cas de conflit : **signaler + expliquer + proposer la résolution**
- **Fanomezantsoa valide** la résolution avant que Claude applique quoi que ce soit

---

## 🧪 Testing — Règles obligatoires

### Principe
Chaque module terminé doit être testé dans Insomnia **avant** de merger vers `main`
et **avant** de commencer le module suivant.
Un tableau vide `[]` sur un GET ne suffit pas — il faut valider la logique métier.

### Checklist minimum par module
- [ ] CREATE → l'enregistrement est bien créé avec les bons champs
- [ ] READ (liste + détail) → les données retournées sont correctes
- [ ] UPDATE → la modification est bien appliquée
- [ ] DELETE → la suppression fonctionne
- [ ] Actions métier → (ex: convert, send, sign...) testées une par une
- [ ] Cas d'erreur → mauvais statut, ID inexistant, champs manquants

### Setup Insomnia
- Base URL : `http://localhost:8001`
- Header obligatoire : `Accept: application/json`
- Header auth : `Authorization: Bearer <token_du_core>`
- Obtenir le token : `POST http://localhost:8000/api/auth/login`

---

## 📂 Projets locaux

| Projet | Chemin | Rôle |
|--------|--------|------|
| `abracadaworld-core` | `~/project/abracadaworld-core/` | Core Laravel — auth centrale — port 8000 |
| `abracadabativ2`     | `~/project/abracadabativ2/`     | Univers Bati Laravel — port 8001 |
| `AbracadaBati`       | `~/project/AbracadaBati/`       | Référence Emergent (FastAPI + React) |

---

*Dernière mise à jour : 2 Avril 2026 — Phase 4 Matching terminée*
*Rédigé par : Fanomezantsoa + Claude*
