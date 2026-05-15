# AGENTS.md — CV Builder Pro

## Project
CV/resume builder. Pure PHP 8.4 + MariaDB + Apache2. No framework. Front-controller routing in `index.php`.

## Sandbox (MANDATORY)
- **Production:** `/var/www/html/` on `main` branch → `http://217.174.245.14/`
- **Staging:** `/var/www/staging/` on `dev` branch → `http://217.174.245.14/staging/`
- **Rule:** ALL new work goes in `/var/www/staging/` first. Never edit production directly.
- **Deploy:** `/var/www/deploy.sh deploy staging` then `/var/www/deploy.sh deploy production`
- **Rollback:** `/var/www/deploy.sh rollback <staging|production>`
- **Dry run:** `/var/www/deploy.sh deploy staging --dry-run`
- Staging uses separate DB `cv_builder_staging` and `BASE_URL` with `/staging` path suffix.
- Staging `.htaccess` has `RewriteBase /staging/` — production has `RewriteBase /`.

## Key Commands
```bash
/var/www/deploy.sh status              # Check both environments
composer install                       # After pulling new deps (deploy script does this)
mysql -u cv_builder -pcv_builder_pass_2024 cv_builder < database/schema.sql  # Apply schema
```

## Architecture
- **Router:** `index.php` — maps `?page=controller/action/id` to controllers
- **Bootstrap:** `includes/bootstrap.php` — DB singleton (PDO), CSRF, auth helpers, `e()`, `redirect()`, `json_response()`
- **Config:** `config/database.php` (credentials), `config/constants.php` (paths, URLs)
- **Controllers:** `controllers/` — 9 controllers, each handles a route segment
- **Models:** `models/` — User, Cv, Template, Phrase, TemplateBlock
- **Views:** `views/<feature>/` — PHP templates, include layout from `views/layouts/main.php`
- **BlockRenderer:** `includes/BlockRenderer.php` — generates HTML/CSS from template blocks (13 types)

## Routing Table
| Route prefix | Controller | Auth required |
|---|---|---|
| `home` | redirects to dashboard or login | no |
| `auth` | AuthController.php | no |
| `dashboard` | DashboardController.php | yes |
| `cv` | CvController.php | yes |
| `templates` | TemplateController.php | yes |
| `creator` | TemplateCreatorController.php | yes |
| `phrases` | PhraseController.php | yes |
| `ajax` | AjaxController.php | yes |
| `export` | ExportController.php | yes |
| `import` | ImportController.php | yes |

## Database
- **Prod:** `cv_builder` | **Staging:** `cv_builder_staging`
- **Creds:** `cv_builder` / `cv_builder_pass_2024` @ `127.0.0.1:3306`
- 11 tables: `users`, `cv_templates`, `cvs`, `cv_sections`, `industries`, `phrase_library`, `user_saved_phrases`, `template_customizations`, `import_history`, `template_blocks`, `template_graphics`
- Schema in `database/schema.sql`. Creator migration in `database/migration_template_creator.sql`.
- Seed files: `seed_templates.sql`, `seed_phrases.sql` (15 templates, 1000 phrases, 20 industries).
- **Demo account:** `demo@cvbuilder.com` / `demo123456`

## Import Parser
- Uses `pdftotext -layout` (poppler-utils) for PDF, `PhpOffice\PhpWord\IOFactory` for DOCX
- Auto-detects sections (personal, summary, experience, education, skills, etc.)
- Parsed data held in `$_SESSION` for review before CV creation
- Files uploaded to `uploads/imports/` — must be owned by `www-data`
- **Known quirk:** PDF bullet characters get mangled — parser normalizes Unicode bullets to `-`

## Template System
- Templates stored in DB (`cv_templates.html_structure`, `cv_templates.css_styles`)
- Use `{{placeholder}}` syntax: `{{first_name}}`, `{{experience_entries}}`, etc.
- Custom templates built via drag-and-drop creator (SortableJS)
- 13 block types: `header`, `sidebar`, `section`, `text`, `image`, `divider`, `spacer`, `columns`, `grid`, `personal-field`, `contact-line`, `skill-bar`, `entry-loop`
- `BlockRenderer` converts blocks → HTML/CSS, saved back to `cv_templates` on save

## Export
- **PDF:** Dompdf 3.0 → `uploads/pdf/`
- **Word:** PHPWord 1.2 → `uploads/word/`
- Export controllers render template HTML with CV data, then convert

## Git
- Local repo only (no remote). `/var/www/html/` is the origin.
- Staging is a clone: `git clone /var/www/html /var/www/staging`
- `.gitignore` excludes: `vendor/`, `uploads/`, `tests/`, `*.log`, `composer.lock`
- Deploy script runs `composer install` automatically after `reset --hard`

## Conventions
- All forms must include `csrf_field()` and verify with `verify_csrf()`
- Controllers check `require_auth()` for protected routes
- Use `e()` for HTML escaping, never raw echo of user data
- JSON responses via `json_response(['key' => 'value'], 200)`
- Flash messages: `flash_message('success', 'Done')` then `get_flash_message()` in view
- `session-ses_*.md` files are session notes — safe to ignore/delete
- `test_parser.php` is a test script — excluded from git
- No `src/` directory exists despite `composer.json` PSR-4 autoload — ignore that config

## Known Issues
- `pcre.jit` warning in PHP 8.4 (non-breaking)
- Apache `ServerName` warning (harmless)
- Import parser works for standard CVs; unusual layouts may need tuning
- Dompdf may struggle with complex CSS in templates
