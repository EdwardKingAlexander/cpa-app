# Phase 1 Plan — Syllabus → Canonical Data Model (LECPA CPA Philippines)

**Project:** Philippine CPA Study Application (Flutter mobile + Laravel backend + Filament admin)  
**Source of truth:** LECPA Syllabi Effective October 2022 (PDF already in project context)  
**Goal of this phase:** Convert the official syllabus into a **machine-usable, versioned, canonical topic tree** that drives navigation, tagging, and analytics across the entire app.

---

## Why this Phase Exists

The app must be **syllabus-driven**, not content-driven.

That means:
- The syllabus defines the **database structure** (subjects → topics → subtopics).
- All learning content (flashcards, questions, notes, videos, PDFs) attaches to **syllabus nodes**.
- Spaced repetition and exam readiness analytics roll up by **topic coverage**.

---

## Non-Goals (Explicitly out of scope for Phase 1)

- Building the full question bank UI
- Writing the learning content itself
- Building spaced repetition scheduling algorithms
- Implementing payments / subscriptions
- Importing third-party content

Phase 1 only sets the **foundation** for all of these.

---

## Definitions

- **Subject**: One of the LECPA exam subjects (FAR, AFAR, MS, AUD, RFBT, TAX).
- **Syllabus Node**: Any topic/subtopic item in the syllabus hierarchy.
- **Canonical Tree**: A normalized hierarchy with stable IDs and codes (e.g., `FAR-4.2.5.1`).

---

## Deliverables (Phase 1)

### D1 — Canonical syllabus JSON (authoritative dataset)
Create a single dataset file that represents the syllabus hierarchy.

**Recommended location:**
- `database/seeders/data/lecp_syllabus_v2022_10.json`

**Each node MUST include:**
- `id` (UUID or deterministic hash; see ID strategy below)
- `subject_code` (e.g., `FAR`)
- `topic_code` (e.g., `FAR-4.2.5.1`)
- `title` (exact syllabus title)
- `depth` (0=subject, 1=topic, 2=subtopic, etc.)
- `parent_topic_code` (nullable)
- `display_order`
- `exam_question_count` (nullable; subject-level has values like 70/100 from syllabus)
- `notes` (nullable; admin-only, not student-facing)

### D2 — Laravel schema + seeders
Create migrations + models + seeders for the canonical tree.

Minimum tables:
- `exam_subjects`
- `syllabus_topics`

Optional (but recommended for future-proofing):
- `syllabus_versions` (if you want to support future syllabus updates cleanly)

### D3 — Filament admin screens for syllabus management (read-only at first)
Admin can:
- View subjects
- Browse topic hierarchy
- Search topics by code/title
- (Optional) lock editing to prevent drift from the official syllabus in v1

### D4 — API endpoints for the mobile app
Read-only endpoints that return:
- subject list
- topic tree by subject
- topic details by code/id

### D5 — Automated tests
- Seeder idempotency test (re-seeding does not duplicate)
- Topic tree integrity test (no cycles; valid parents)
- Unique constraint test (topic_code unique per syllabus version)

---

## Key Decisions to Make (Codex should review)

### 1) ID Strategy
Two viable options:

**Option A — Deterministic IDs (recommended for stable re-seeding)**
- ID generated from `(syllabus_version + topic_code)` using UUIDv5 or hash.
- Pros: stable across environments; easy to re-import.
- Cons: slightly more implementation complexity.

**Option B — Random UUID**
- Pros: simplest.
- Cons: re-importing requires matching by topic_code; must enforce unique keys carefully.

**Recommendation:** Option A (Deterministic IDs) + `topic_code` uniqueness constraints.

---

## Data Model (Proposed)

### Table: `exam_subjects`
- `id` (uuid)
- `code` (string, unique) — `FAR`, `AFAR`, `MS`, `AUD`, `RFBT`, `TAX`
- `name` (string)
- `exam_question_count` (int) — from syllabus (e.g. FAR=70, RFBT=100)
- `effective_date` (date) — `2022-10-01` for this syllabus
- timestamps

### Table: `syllabus_topics`
- `id` (uuid)
- `subject_id` (fk → exam_subjects)
- `syllabus_version` (string) — e.g. `2022-10`
- `topic_code` (string) — e.g. `FAR-4.2.5.1`
- `title` (string)
- `parent_id` (nullable fk → syllabus_topics)
- `depth` (tinyint)
- `display_order` (int)
- `is_leaf` (boolean) — computed/maintained by seeder
- timestamps

**Constraints**
- Unique: `(syllabus_version, topic_code)`
- Indexes: `subject_id`, `parent_id`, `topic_code`, `title`

---

## Seeder Approach

1. Load JSON dataset.
2. Upsert `exam_subjects` by `code`.
3. Upsert `syllabus_topics` by `(syllabus_version, topic_code)`.
4. Resolve parents by `parent_topic_code`.
5. Compute `depth`, `display_order`, `is_leaf`.

**Idempotency rule:** Running the seeder multiple times must not create duplicates.

---

## Filament Admin (Phase 1 scope)

### Resources
- `ExamSubjectResource` (list/detail only)
- `SyllabusTopicResource` (tree view + search)

### UX features
- Tree navigation by subject
- Filter by subject
- Search by topic code
- Show counts of children
- (Optional) “Locked” badge showing syllabus is official

---

## API (Phase 1 scope)

### Endpoints (example)
- `GET /api/v1/syllabus/subjects`
- `GET /api/v1/syllabus/subjects/{code}/tree`
- `GET /api/v1/syllabus/topics/{topicCode}`

Responses must be **version-aware**:
- Include `syllabus_version` and `effective_date`

---

## Implementation Phases (Suggested)

### Phase 1.1 — Dataset extraction + normalization
**Output:** `lecp_syllabus_v2022_10.json` created and validated  
Tasks:
- Extract topics from PDF manually or semi-automatically
- Ensure topic codes match syllabus numbering
- Validate hierarchy (parent relationships)
- Add subject-level question counts (FAR/AFAR/MS/AUD/TAX=70; RFBT=100)

Acceptance criteria:
- JSON validates against a schema
- Tree renders in a quick script (sanity check)

### Phase 1.2 — Laravel schema + seeders
Tasks:
- Add migrations
- Add models + relationships
- Implement deterministic ID generation (if chosen)
- Write seeders + tests

Acceptance criteria:
- `php artisan migrate --fresh --seed` succeeds
- Tests pass
- No duplicates after re-seed

### Phase 1.3 — Filament admin (read-only)
Tasks:
- Create Filament resources
- Add tree UI (parent/child)
- Add filters + search

Acceptance criteria:
- Admin can browse syllabus reliably
- Search works by code/title

### Phase 1.4 — Public API endpoints
Tasks:
- Create controllers + routes
- Add versioning support
- Add minimal auth policy (public read ok or token-based, your choice)

Acceptance criteria:
- Flutter can request subject list + trees
- Responses are fast and stable

---

## Risks & Mitigations

- **Risk:** Syllabus updates (future PRC/BOA changes) break compatibility  
  **Mitigation:** Add `syllabus_version` now; design for multi-version later.

- **Risk:** Manual extraction errors (missing nodes, wrong numbering)  
  **Mitigation:** Add validation scripts and a “topic count by subject” report.

- **Risk:** Admin edits drift from official syllabus  
  **Mitigation:** Lock editing in Phase 1; allow changes only through dataset PR.

---

## Open Questions (Codex should flag)
- Should we support multiple syllabus versions now or defer?
- Deterministic IDs vs random UUIDs?
- Do we want topic titles editable (admin) or strictly immutable?
- How should we represent “exam weights” beyond subject question counts (if ever)?

---

## Next Step After Phase 1 (Preview)
Once the canonical tree exists, Phase 2 can add:
- Question bank models (questions, choices, explanations)
- Topic tagging
- Practice modes + scoring
- Spaced repetition scheduling metadata per topic

---

## Appendix — Minimal JSON Node Example

```json
{
  "id": "uuid-or-deterministic-id",
  "subject_code": "FAR",
  "topic_code": "FAR-4.2.5.1",
  "title": "Cost method",
  "parent_topic_code": "FAR-4.2.5",
  "depth": 4,
  "display_order": 1,
  "exam_question_count": null,
  "notes": null
}
```

---

**End of Phase 1 Plan**
