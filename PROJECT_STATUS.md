# CV Builder Pro — Project Status & Handoff Document

> **Last Updated:** May 15, 2026
> **Purpose:** This document is your one-stop reference when starting a fresh session. It covers what this project is, what's been built, what's next, and how the sandbox works.

---

## 1. What Is This Project?

**CV Builder Pro** is a professional CV/resume builder web application. Users create, edit, preview, import, and export CVs in PDF and Word formats.

### Key Features (All Implemented)
- **Wizard Editor** — 8-step guided CV creation
- **WYSIWYG Editor** — Live preview with toolbar
- **15 CV Templates** — Professional, Creative, Minimal, Academic, Executive
- **Template Creator** — Drag-and-drop block-based template builder (13 block types)
- **CV Import** — Upload PDF/DOCX/TXT/RTF, auto-parse into CV sections
- **PDF/Word Export** — Powered by Dompdf and PHPWord
- **Phrase Library** — 1000 industry-specific professional phrases across 20 industries
- **User Auth** — Registration, login, session-based auth with CSRF protection

---

## 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| **OS** | Debian (Linux) |
| **Web Server** | Apache 2.4 |
| **Language** | PHP 8.4 |
| **Database** | MariaDB 11.x |
| **Frontend** | HTML5, CSS, JavaScript (AJAX) |
| **PDF Export** | Dompdf 3.0 |
| **Word Export** | PHPWord 1.2 |
| **PDF Import** | poppler-utils (`pdftotext`) |
| **Drag & Drop** | SortableJS |
| **Version Control** | Git |

### No Framework
Pure PHP MVC-style with front-controller pattern. No Laravel, no Symfony. All routing in `index.php`, all DB via PDO prepared statements.

---

## 3. Project Structure

```
/var/www/html/          ← PRODUCTION (main branch)
/var/www/staging/       ← SANDBOX (dev branch)
/var/www/deploy.sh      ← Deploy script

Key directories:
  config/               ← database.php, constants.php
  controllers/          ← 9 controllers (Auth, Cv, Dashboard, Export, Import, Phrase, Template, TemplateCreator, Ajax)
  models/               ← 5 models (User, Cv, Template, Phrase, TemplateBlock)
  views/                ← PHP templates organized by feature
  includes/             ← bootstrap.php, BlockRenderer.php
  database/             ← schema.sql, seed files, migrations
  assets/               ← CSS and JS files
  uploads/              ← PDF exports, Word exports, imports, graphics
```

---

## 4. Sandbox / Staging Environment

### Why It Exists
To prevent breaking the live production site during development. All experiments, new features, and risky changes happen in staging first.

### How It Works

| | Production | Staging |
|--|-----------|---------|
| **Path** | `/var/www/html/` | `/var/www/staging/` |
| **Git Branch** | `main` | `dev` |
| **Database** | `cv_builder` | `cv_builder_staging` |
| **URL** | `http://217.174.245.14/` | `http://217.174.245.14/staging/` |
| **App Name** | "CV Builder Pro" | "CV Builder Pro (Staging)" |

### Workflow
```
1. Edit files in /var/www/staging/
2. Commit: git add . && git commit -m "message"
3. Deploy: /var/www/deploy.sh deploy staging
4. Test at: http://217.174.245.14/staging/
5. When ready: /var/www/deploy.sh deploy production
```

### Deploy Commands
```bash
/var/www/deploy.sh status                    # Show both environments
/var/www/deploy.sh deploy staging            # Deploy dev → staging
/var/www/deploy.sh deploy production         # Deploy main → production
/var/www/deploy.sh deploy staging --dry-run  # Preview without applying
/var/www/deploy.sh rollback staging          # Undo last staging deploy
/var/www/deploy.sh rollback production       # Undo last production deploy
```

### Important Rules
- **NEVER** edit `/var/www/html/` directly for new features — use staging
- **ALWAYS** test in staging before deploying to production
- Use `--dry-run` to preview changes before deploying
- Rollback is available if something breaks

---

## 5. What's Been Completed

### Core Application ✅
- [x] Git repo initialized (main branch, 52 files committed)
- [x] Staging environment set up (dev branch, isolated DB)
- [x] Deploy script with staging/production/rollback
- [x] Apache configured for both production and staging
- [x] Database schema (11 tables)
- [x] Seed data: 15 templates, 20 industries, 1000 phrases
- [x] User authentication (register, login, logout, bcrypt)
- [x] Dashboard with stats, CV list, quick actions
- [x] CV creation with template selection
- [x] Wizard Editor (8 steps)
- [x] WYSIWYG Editor (live preview)
- [x] CV preview page
- [x] Template gallery with category filtering
- [x] Template preview (AJAX with sample data)
- [x] PDF export (Dompdf)
- [x] Word export (PHPWord)
- [x] Template Creator (drag-and-drop, 13 block types)
- [x] BlockRenderer (generates HTML/CSS from blocks)
- [x] Import feature (PDF/DOCX/TXT/RTF parsing)
- [x] Import review page (edit before creating CV)
- [x] Phrase library (search, filter, pagination, favorites)
- [x] AJAX endpoints for all operations
- [x] CSRF protection on all forms
- [x] Comprehensive README.md documentation
- [x] Full application logic documentation in README

### Import Parser (Recently Fixed) ✅
- [x] Summary extraction (was empty, now works)
- [x] Experience section mapping (was misaligned, now correct)
- [x] Skills list extraction (was 0 found, now works)
- [x] PDF text encoding fix (mangled bullet characters)
- [x] DOCX duplicate line removal
- [x] All 19 parser tests passing

---

## 6. What's Next / Planned

### High Priority
- [ ] **Improve import parser** — Better experience title/company detection for varied CV formats
- [ ] **Add projects section** to CV (currently in schema but not in UI)
- [ ] **Add volunteer section** to CV (currently in schema but not in UI)
- [ ] **Add awards section** to CV (currently in schema but not in UI)
- [ ] **Public CV sharing** — Generate public URL for CVs (`is_public` + `public_slug` exist in schema)
- [ ] **CV versioning** — Track changes, revert to previous versions

### Medium Priority
- [ ] **Email notifications** — Password reset, account verification
- [ ] **Avatar upload** — User profile pictures
- [ ] **Template marketplace** — Share/sell custom templates
- [ ] **Bulk phrase insertion** — Insert multiple phrases at once in editor
- [ ] **CV analytics** — Track views, downloads, export counts
- [ ] **Responsive mobile editing** — Better mobile UX for editors

### Low Priority / Future
- [ ] **Multi-language support** — i18n for CV content and UI
- [ ] **Cover letter builder** — Companion feature
- [ ] **AI suggestions** — Auto-generate summary/experience bullets
- [ ] **OAuth login** — Google, GitHub, LinkedIn sign-in
- [ ] **Team/organization accounts** — Shared CV templates
- [ ] **API** — REST API for third-party integrations

---

## 7. Key Technical Details

### Database Credentials
```
Host: localhost
User: cv_builder
Pass: cv_builder_pass_2024
Production DB: cv_builder
Staging DB: cv_builder_staging
```

### Demo Account
```
Email: demo@cvbuilder.com
Password: demo123456
```

### Routing Pattern
All routes go through `index.php` via `.htaccess` rewrite:
```
/dashboard          → index.php?page=dashboard
/cv/edit/5          → index.php?page=cv/edit/5
/cv/edit/5?mode=wysiwyg  → WYSIWYG editor
/import             → index.php?page=import
/export/pdf/5       → index.php?page=export/pdf/5
```

### Section Types (cv_sections)
`personal`, `summary`, `experience`, `education`, `skills`, `languages`, `certifications`, `references`, `projects`, `volunteer`, `awards`, `custom`

### Template Block Types
`header`, `sidebar`, `section`, `text`, `image`, `divider`, `spacer`, `columns`, `grid`, `personal-field`, `contact-line`, `skill-bar`, `entry-loop`

### Placeholder System
Templates use `{{placeholders}}` that get replaced with CV data:
`{{first_name}}`, `{{last_name}}`, `{{job_title}}`, `{{email}}`, `{{phone}}`, `{{address}}`, `{{linkedin}}`, `{{website}}`, `{{summary}}`, `{{experience_entries}}`, `{{education_entries}}`, `{{skills_entries}}`, `{{language_entries}}`, `{{certification_entries}}`, etc.

---

## 8. How to Continue Work

### Starting a Fresh Session
1. Read this document (you're doing it now)
2. Check current state: `/var/www/deploy.sh status`
3. Read full docs: `cat /var/www/html/README.md`
4. Work in staging: `cd /var/www/staging/`
5. Deploy when ready: `/var/www/deploy.sh deploy staging`

### Common Tasks

**Add a new feature:**
```bash
cd /var/www/staging/
# Edit files...
git add .
git commit -m "Add feature X"
/var/www/deploy.sh deploy staging
# Test at http://217.174.245.14/staging/
# If good, merge to main and deploy to production
```

**Fix a bug in production:**
```bash
cd /var/www/staging/
# Fix the bug...
git add .
git commit -m "Fix bug: description"
/var/www/deploy.sh deploy staging
# Verify fix in staging
cd /var/www/html/
git merge dev
/var/www/deploy.sh deploy production
```

**Add a new PHP dependency:**
```bash
cd /var/www/staging/
composer require vendor/package
git add composer.json composer.lock
git commit -m "Add vendor/package"
/var/www/deploy.sh deploy staging
# Deploy to production runs composer install automatically
```

**Run database migration:**
```bash
# Create migration file
nano /var/www/staging/database/migration_new_feature.sql

# Apply to staging DB
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder_staging < /var/www/staging/database/migration_new_feature.sql

# Commit the migration
cd /var/www/staging/
git add database/migration_new_feature.sql
git commit -m "Add migration: new feature"

# Apply to production when deploying
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < /var/www/html/database/migration_new_feature.sql
```

---

## 9. Known Issues & Quirks

- **pcre.jit warning**: Set `pcre.jit = 0` in php.ini (already noted in docs)
- **Apache ServerName warning**: Harmless, can add `ServerName localhost` to suppress
- **Import parser**: Works well for standard CV formats; edge cases with unusual layouts may need tuning
- **PDF export**: Dompdf may struggle with complex CSS; test templates before export
- **Session timeout**: 24 hours by default; adjust in `constants.php` if needed
- **No remote Git repo**: Currently local-only (`/var/www/html` is the origin). For backup, consider adding a remote.

---

## 10. File Reference

| File | Purpose |
|------|---------|
| `index.php` | Front controller / router |
| `includes/bootstrap.php` | Core functions, DB singleton, helpers |
| `config/database.php` | DB credentials |
| `config/constants.php` | App constants, paths, URLs |
| `controllers/AjaxController.php` | All AJAX endpoints |
| `controllers/ImportController.php` | CV import + parser (679 lines) |
| `controllers/ExportController.php` | PDF/Word export |
| `models/Cv.php` | CV CRUD operations |
| `models/TemplateBlock.php` | Template creator operations |
| `includes/BlockRenderer.php` | Renders blocks to HTML/CSS |
| `database/schema.sql` | Full DB schema |
| `.htaccess` | URL rewriting rules |
| `/var/www/deploy.sh` | Deploy script |

---

## 11. Quick Reference Card

```
URLs:
  Production:  http://217.174.245.14/
  Staging:     http://217.174.245.14/staging/

Paths:
  Production:  /var/www/html/
  Staging:     /var/www/staging/
  Deploy:      /var/www/deploy.sh
  Logs:        /var/www/deploy.log

Git:
  Production:  main branch
  Staging:     dev branch
  Origin:      /var/www/html/ (local bare)

DB:
  Production:  cv_builder
  Staging:     cv_builder_staging
  User:        cv_builder / cv_builder_pass_2024

Login:
  demo@cvbuilder.com / demo123456
```

---

*End of handoff document. When in doubt, run `/var/www/deploy.sh status` and read this file.*
