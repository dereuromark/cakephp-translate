# CakePHP Translate Plugin - Improvements Roadmap

## Completed in 0.4.0

- [x] **Orphaned Strings View** - Backend action to display strings no longer in source code
- [x] **`i18n validate` Command** - Validate translation files for issues
- [x] **POT Updater Tool** - Standalone PHP script for CI integration (PR #36)
- [x] **Translate Behavior Manager** - Admin interface for CakePHP's TranslateBehavior migrations
- [x] **Multi-Project Support** - Improved project switching and management
- [x] **Locale Switcher** - UI element for switching between locales
- [x] **Languages → Locales Rename** - Consistent naming throughout

---

## CLI Commands

### New Commands

#### `i18n sync`
Synchronize PO files with database in both directions.
- `--direction=import` - Import PO files to database
- `--direction=export` - Export database to PO files
- `--dry-run` - Show what would change without making changes
- `--locale=de_DE` - Sync specific locale only

#### `i18n cleanup`
Clean up orphaned/obsolete translations.
- Remove strings no longer in source code
- Remove terms for deleted strings
- `--dry-run` - Preview what would be removed
- `--interactive` - Confirm each removal

#### `i18n diff`
Show differences between POT file and database.
```bash
bin/cake i18n diff --plugin=MyPlugin
# + 3 new strings in source
# - 2 strings removed from source
# ~ 5 strings with updated references
```

### Existing Command Improvements

#### `i18n extract_to_db`
- [ ] Add `--exclude-paths` option to skip certain directories
- [ ] Add `--domains` option to extract only specific domains
- [ ] Add `--verbose` mode showing each extracted string
- [ ] Support extracting from JavaScript/TypeScript files (`__()` patterns)
- [ ] Add `--since=<commit>` to extract only from changed files

#### `i18n dump_from_db`
- [ ] Add `--locale` option to dump specific locale only
- [ ] Add `--domain` option to dump specific domain only
- [ ] Add `--format=po|json|php` for different output formats
- [ ] Add `--confirmed-only` to only dump confirmed translations
- [ ] Add `--wrap=80` option for line wrapping in PO files

#### `i18n validate` (implemented in 0.4.0)
- [ ] Add `--fix` option for auto-fixable issues (whitespace normalization)
- [ ] Add `--strict` mode with additional checks
- [ ] Add severity levels (error, warning, info)
- [ ] Check for duplicate translations with different msgids
- [ ] Validate plural forms match locale requirements

## Web Interface

### String Management

#### Orphaned Strings View (implemented in 0.4.0)
Backend action to display strings that are no longer referenced in source code.
- [x] List strings where `references` is empty or points to non-existent files
- [x] Bulk delete option with confirmation
- [ ] Show last extraction date to identify stale strings
- [ ] Option to mark as "manually added" to exclude from orphan detection
- [ ] Filter by domain, age (older than X days)

#### Coverage Dashboard
Show translation coverage statistics per locale and domain.
- Progress bars for each locale
- Highlight domains with low coverage
- Export coverage report

### Translation Editor
- [ ] Inline editing with auto-save
- [ ] Side-by-side comparison with source string
- [ ] Translation memory suggestions from similar strings
- [ ] Keyboard shortcuts for power users (Ctrl+Enter to save, arrows to navigate)
- [ ] Bulk actions (confirm all, mark all as fuzzy)
- [ ] Filter by: untranslated, fuzzy, confirmed, recently modified

### Dashboard
- [ ] Translation progress charts per locale
- [ ] Recent activity feed
- [ ] Top contributors list
- [ ] Strings needing review queue
- [ ] Search across all strings with highlighting

### Quality Assurance
- [ ] Translation review workflow (submit -> review -> approve)
- [ ] Comments/notes on translations
- [ ] Flag problematic translations for review
- [ ] History/changelog per string with diff view
- [ ] Rollback to previous translation

### Import/Export
- [ ] Import from XLIFF, CSV, JSON formats
- [ ] Export to XLIFF for professional translators
- [ ] Batch import with conflict resolution UI
- [ ] Export translation memory (TMX format)

## API & Integration

### REST API
- [ ] Endpoints for programmatic access
  - `GET /api/translations/{locale}/{domain}` - Get all translations
  - `POST /api/translations/{locale}/{domain}` - Update translations
  - `GET /api/strings/search?q=...` - Search strings
  - `GET /api/coverage` - Get coverage statistics
- [ ] API key authentication
- [ ] Rate limiting
- [ ] Webhooks for translation events

### Machine Translation
- [ ] Integrate with DeepL API
- [ ] Integrate with Google Cloud Translation
- [ ] Integrate with AWS Translate
- [ ] Auto-translate button for individual strings
- [ ] Bulk auto-translate with human review queue
- [ ] Translation memory lookups before API calls

### External Services
- [ ] Crowdin integration (import/export)
- [ ] Transifex integration
- [ ] Lokalise integration
- [ ] Phrase integration
- [ ] GitHub/GitLab PR automation for translation updates

## Developer Experience

### POT Updater Tool (implemented in 0.4.0)
See `scripts/README.md` for usage documentation.
- [x] Standalone PHP script runnable from plugin root
- [x] Comparison report with existing POT
- [x] `--fail-on-diff` for CI integration
- [x] Skip write if only timestamp changed
- [ ] GitHub Actions workflow example
- [ ] Pre-commit hook example

### IDE Integration
- [ ] Generate translation keys from templates
- [ ] Autocomplete for translation keys
- [ ] Jump to translation in web UI

### Testing
- [ ] Test helper for verifying all strings are translated
- [ ] Fixture factory for translation testing
- [ ] Mock translator for unit tests

## Performance & Scalability

### Caching
- [ ] Cache parsed PO files
- [ ] Cache translation lookups with tags
- [ ] Invalidate cache on translation update
- [ ] Redis/Memcached support for distributed caching

### Database
- [ ] Add indexes for common queries
- [ ] Optimize bulk import/export queries
- [ ] Support for read replicas
- [ ] Pagination for large string sets

### Background Processing
- [ ] Queue bulk operations (import, export, auto-translate)
- [ ] Progress tracking for long-running tasks
- [ ] Email notifications on completion

## Security & Access Control

### Permissions
- [ ] Role-based access (admin, translator, reviewer)
- [ ] Per-locale permissions (translator for de_DE only)
- [ ] Per-project permissions
- [ ] Audit log for all changes

### Data Protection
- [ ] Soft delete for strings and translations
- [ ] Backup/restore functionality
- [ ] Export personal data (GDPR compliance)

## Configuration

### Project Settings
- [ ] Configure plural forms per locale
- [ ] Set primary/base locale
- [ ] Define translation workflow (direct publish vs review)
- [ ] Configure auto-translation settings
- [ ] Set up notification preferences

### Advanced Options
- [ ] Custom validation rules
- [ ] Placeholder format configuration
- [ ] HTML tag whitelist for translations
- [ ] Character limit per string

## Documentation

- [ ] API documentation with examples
- [ ] Video tutorials for common workflows
- [ ] Translation best practices guide
- [ ] Integration guides for popular services
- [ ] Migration guide from other translation systems

## Priority Matrix

### High Priority (Next Release)
1. `i18n sync` command
2. Coverage dashboard improvements
3. DeepL/Google Translate integration
4. Translation editor improvements (inline editing, keyboard shortcuts)

### Medium Priority
1. REST API endpoints
2. Import/Export XLIFF format
3. Dashboard activity feed
4. Review workflow

### Low Priority (Future)
1. External service integrations (Crowdin, Transifex)
2. IDE integration
3. Advanced permission system
4. Translation memory (TMX)

## Contributing

If you'd like to contribute to any of these features:
1. Check if there's an existing issue for the feature
2. Create a new issue to discuss the implementation approach
3. Submit a PR with tests and documentation

## Notes

- All new features should include tests
- CLI commands should support `--json` output for CI integration
- Web UI changes should be mobile-responsive
- Consider backwards compatibility for major changes
