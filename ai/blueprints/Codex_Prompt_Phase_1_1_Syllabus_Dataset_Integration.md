# Codex Prompt — Phase 1.1: Syllabus Dataset Integration & Validation

## Context

You are working inside a **Laravel 12** application with **Filament v4**.

The database schema for the following tables already exists and is correct:

- `subjects`
- `syllabus_versions`
- `syllabus_topics`

**Do NOT redesign schemas.  
Do NOT rename tables or columns.  
Do NOT introduce new subject concepts.**

A full canonical syllabus dataset has been generated from the official **LECPA CPA Syllabi Effective October 2022** and has been copied into the project.

---

## Objective (Phase 1.1)

Safely integrate the new syllabus dataset, validate it, and ensure it seeds correctly **without breaking existing behavior**.

---

## Files Present

- `database/seeders/data/lecp_syllabus_v2022_10_full.json`
- `database/seeders/data/README.md` (reference only)

---

## Hard Constraints (Must Not Be Violated)

1. **No database schema changes**
2. **No renaming of existing tables or columns**
3. **Subject codes must remain unchanged**
   - Internal subject codes:
     - `FAR`
     - `AFAR`
     - `MAS`
     - `AUDIT`
     - `RFBT`
     - `TAX`
   - Official syllabus code mapping:
     - `MAS → MS`
     - `AUDIT → AUD`
4. `syllabus_topics.id` uses **random UUIDs**
   - Stable identity is `(syllabus_version_id, topic_code)`
5. Seeding must be **idempotent**
   - Re-running seeders must not create duplicates

---

## Required Work

### 1) Dataset Validation (Before Seeding)

Implement validation logic that confirms:

- Every node has:
  - a valid `subject_code`
  - a valid `topic_code`
  - a valid `title`
- Every `parent_topic_code` (if present) exists
- No duplicate `(syllabus_version_id, topic_code)` pairs
- No cycles in parent relationships
- Computed `depth` matches the topic code hierarchy
- Each subject has exactly **one root node**

Validation may be implemented as:
- a PHPUnit test, **or**
- an Artisan command (**preferred**)

If validation fails, seeding **must abort** with a clear error message.

---

### 2) Seeder Integration

Confirm or update the existing syllabus seeder so that:

- Data is loaded from `lecp_syllabus_v2022_10_full.json`
- Subjects are resolved by matching:
  - `subjects.syllabus_code` → dataset `subject_code`
- Topics are **upserted** using:
  - `(syllabus_version_id, topic_code)`
- Parent relationships are resolved **after** all topics are created
- `is_leaf` is computed automatically
- `display_order` preserves dataset order

---

### 3) Safety & Idempotency Tests

Add tests asserting that:

- `php artisan migrate:fresh --seed` completes successfully
- Re-running seeders does **not** increase row counts
- Each subject produces a single connected tree
- Leaf nodes have no children

---

## Acceptance Criteria

- Full syllabus tree is visible in Filament per subject
- API endpoints return complete hierarchies
- No duplicate topics after re-seeding
- No schema changes introduced

---

## Explicitly Out of Scope

- Question banks
- Study content
- UI redesign
- Performance optimization
- Spaced repetition logic

---

## Deliverables

- Dataset validation logic (test or command)
- Seeder updates (if required)
- Tests covering dataset integrity
- Short summary of changes made and rationale

---

**Stop after Phase 1.1 is complete.  
Do NOT proceed to Phase 2.  
Report results clearly.**

## Progress Log

- Phase 1 (dataset inventory): Found two JSON files in `database/seeders/data`. `lecp_syllabus_v2022_10.json` contains only subject roots, while `lecp_syllabus_v2022_10_full.json` contains the full topic tree. The README lives at `database/seeders/data/README.md` (not `lecp_syllabus_v2022_10_dataset_README.md`). Use the full JSON as the reference dataset per the latest instruction.
- Phase 2 (encoding check): No replacement characters found in the full dataset JSON. The README and dataset include expected typographic punctuation (em dashes, curly apostrophes), but no corrupted glyphs like `�`.
- Phase 3 (seeder/test references): `database/seeders/Concerns/LoadsSyllabusData.php` and related seeders currently load `seeders/data/lecp_syllabus_v2022_10.json` and expect a `version` key. The full dataset uses `lecp_syllabus_v2022_10_full.json` and a different top-level shape (`syllabus_version`, `generated_at`, `nodes`), so the loaders/seeders will need adjustments when switching to the full dataset.
- Phase 4 (loader/validation update): `database/seeders/Concerns/LoadsSyllabusData.php` now reads `lecp_syllabus_v2022_10_full.json`, normalizes the version metadata, and validates nodes (parents, depth, duplicates, cycles, single roots) before seeding. `database/seeders/SyllabusVersionSeeder.php` now avoids overwriting `source` when the dataset does not provide one.
- Phase 5 (tests update): `tests/Feature/SyllabusSeederTest.php` now asserts each subject builds a single connected tree and leaf nodes have no children.
- Phase 6 (dataset + test correction): Fixed a duplicate topic code in `database/seeders/data/lecp_syllabus_v2022_10_full.json` (RFBT-7.2.13.x sequence) and moved the migrate/seed assertion into `tests/Feature/SyllabusMigrateFreshSeedTest.php`, using a temp sqlite file to avoid sqlite vacuum conflicts.
