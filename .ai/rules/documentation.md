# Documentation & Living Memory Rules (docs/**)

## 1. Smart Pre-Flight Protocol (Targeted Reading)
- Inspect `docs/project_state.md` to understand active system state, models, and routes.
- **NEVER read `docs/ARCHITECTURE_LOG.md` or `docs/changelog.md` in full.**
- When specific context is needed, use `grep_search` or slice notation (`StartLine` and `EndLine`) to read only the relevant ADR or changelog section.

## 2. Proportional Post-Flight Protocol (Smart Updating)
- **`docs/changelog.md`**: Always log concise entries under the current release/timestamp for all meaningful code changes, refactors, and bug fixes.
- **`docs/project_state.md`**: Update ONLY when models, migrations, routes, services, console commands, or system configurations are added, modified, or removed.
- **`docs/ARCHITECTURE_LOG.md`**: Record a new ADR ONLY for major structural decisions (e.g. new domain module, architectural redesign, new third-party engine). NEVER create ADRs for routine bug fixes, styling tweaks, minor refactors, or copy changes.

## 3. Strict Token-Economy Response Directive
- **Zero Document Echoing:** Never output full file contents or large excerpts of `docs/` files in chat messages.
- Always report documentation updates as a concise summary:
  - Target file path
  - 3 to 5 bullet points summarizing changes
- Deliver exact code, diffs, and test results directly without conversational filler.

## 4. Archival Standards
- Historical releases prior to `v1.0.36` are archived in `docs/archive/core_kernel_archive.md`.
- Historical pre-Point-Zero logs are archived in `docs/archive/legacy_changelog.md` and `docs/archive/legacy_ARCHITECTURE_LOG.md`.
