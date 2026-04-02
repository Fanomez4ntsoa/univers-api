# ROADMAP_MIGRATION.md — AbracadaBati v2

> **Lire ce fichier avant chaque nouveau module.**
> Il trace l'état exact de la migration Emergent (FastAPI + MongoDB) 
> vers Laravel 12 + MySQL et indique quoi faire ensuite.

---

## 📍 Position actuelle
```
Phase 1 CRM █████████████████████ 100% — COMPLÈTE ✅
Phase 2     █████████████████████ 100% — COMPLÈTE ✅
Phase 3     █████████████████████ 100% — COMPLÈTE ✅
Phase 4     █████████████████████ 100% — COMPLÈTE ✅
Phase 5     ░░░░░░░░░░░░░░░░░░░░░   0% — pas encore commencé
```

---

## ✅ PHASE 1 — CRM Pro

| Module | Fichiers Emergent de référence | Branche | État |
|---|---|---|---|
| Auth (CoreMiddleware) | `server.py` | `main` | ✅ Terminé + testé |
| Prospects | `batiment_routes.py` + `batiment_models.py` | `main` | ✅ Terminé + testé |
| Clients | `batiment_routes.py` + `batiment_models.py` | `main` | ✅ Terminé + testé |
| Quotes (Devis) | `batiment_routes.py` + `devis_routes.py` + `devis_models.py` | `main` | ✅ Terminé + testé |
| Invoices (Factures) | `batiment_routes.py` + `devis_routes.py` | `main` | ✅ Terminé + testé |
| Chantiers | `batiment_routes.py` + `test_pipeline_chantier.py` | `main` | ✅ Terminé + testé |
| CompanySettings | `batiment_routes.py` | `feature/crm-company-settings` | ✅ Terminé + testé |

---

## ✅ PHASE 2 — Client Portal

| Module | Fichiers Emergent de référence | Branche | État |
|---|---|---|---|
| Accès portal client (token) | `client_portal_routes.py` + `client_portal_models.py` | `main` | ✅ Terminé + testé |
| Signature devis côté client | `client_portal_devis_routes.py` + `test_client_portal_devis.py` | `main` | ✅ Terminé + testé |
| Vue publique devis/facture | `public/PublicQuoteSignPage.jsx` (front référence) | `main` | ✅ Terminé + testé |

---

## ✅ PHASE 3 — Ecosystem Social

> **Débloquée** : Phase 2 complète ✅

| Module | Fichiers Emergent de référence | Branche | État |
|---|---|---|---|
| Posts / Feed | `ecosystem_routes.py` + `ecosystem_models.py` | `main` | ✅ Terminé + testé |
| Shops (boutique artisan) | `cockpit_boutique_routes.py` + `seller_routes.py` | `main` | ✅ Terminé + testé |
| Listings / Petites annonces | `ecosystem_routes.py` | `main` | ✅ Terminé + testé |
| Jobs & Events | `job_alerts_routes.py` + `test_jobs_events.py` | `main` | ✅ Terminé + testé |
| Réseau social (profils, follow) | `social_media_routes.py` | `main` | ✅ Terminé + testé |

### Branches prévues :
- `feature/ecosystem-posts`
- `feature/ecosystem-shops`
- `feature/ecosystem-listings`
- `feature/ecosystem-jobs-events`

---

## ✅ PHASE 4 — Matching

| Module | Fichiers Emergent de référence | Branche | État |
|---|---|---|---|
| Demandes de travaux + devis artisans | `project_request_routes.py` + `project_request_models.py` | `main` | ✅ Terminé + testé |

---

## 🔄 PHASE 5 — Features avancées

> **Débloquée** : Phases 1-4 complètes ✅

| Module | Fichiers Emergent de référence | Branche | État |
|---|---|---|---|
| Paiements Stripe | `stripe_routes.py` + `stripe_models.py` | `main` | ✅ Terminé + testé |
| Génération PDF (devis/factures) | `pdf_generator.py` | | 📋 À faire |
| Messagerie interne | `messaging_routes.py` + `messaging_models.py` | | 📋 À faire |
| Notifications | `notification_routes.py` + `push_routes.py` | | 📋 À faire |
| Pointage / Time tracking | `pointage_routes.py` + `test_pointage.py` | | 📋 À faire |
| Intégration email | `email_integration_routes.py` | | 📋 À faire |
| Intégration calendrier | `calendar_integration_routes.py` | | 📋 À faire |

---

## 🎯 Règle de progression
```
1. Terminer le module sur sa branche feature/
2. Tester tous les endpoints dans Insomnia (checklist CLAUDE.md)
3. Soumettre à Fanomezantsoa pour validation
4. Merger vers main après accord
5. Mettre à jour ce fichier (état → ✅ Terminé + testé)
6. Passer au module suivant
```

> ⚠️ Ne jamais sauter une phase.
> Chaque phase dépend de la précédente pour le bon fonctionnement de l'ensemble.

---

## 📂 Référence Emergent
- Chemin local : `~/project/AbracadaBati/backend/`
- Toujours vérifier le fichier de référence avant de créer un module
- En cas de doute : lire aussi les fichiers `test_*.py` correspondants

---

*Dernière mise à jour : 2 Avril 2026 — Phase 5 Stripe terminé*
*Rédigé par : Fanomezantsoa + Claude*
