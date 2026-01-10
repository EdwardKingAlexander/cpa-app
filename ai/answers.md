# Decisions on Subject Codes and Syllabus Versioning

## 1) Subject Codes Strategy

**Decision:** Keep the existing internal subject codes (e.g., `MAS`, `AUDIT`) as the canonical identifiers and introduce a mapping to the official LECPA syllabus codes (`MS`, `AUD`).

### Rationale
- Avoids breaking changes to existing data, routes, analytics, and UI.
- Preserves alignment with the official PRC/BOA syllabus terminology.
- Future‑proofs the system for alternate syllabi or renamed subjects.

### Implementation Pattern
- `subjects.code` → remains the internal, canonical code (`MAS`, `AUDIT`, etc.).
- Add `subjects.syllabus_code` (or `exam_code`) → stores official codes (`MS`, `AUD`).
- Enforce uniqueness on `syllabus_code` (scoped by syllabus version if versioned).

### Example Mappings
- `MAS` → `MS`
- `AUDIT` → `AUD`
- `FAR` → `FAR`
- `AFAR` → `AFAR`
- `RFBT` → `RFBT`
- `TAX` → `TAX`

This approach allows the syllabus dataset to stay faithful to official codes while the application remains stable internally.

---

## 2) Subjects Table vs New exam_subjects Table

**Decision:** Extend the existing `subjects` table and introduce lightweight syllabus versioning. Do **not** create a parallel `exam_subjects` table.

### Rationale
- Prevents duplication and conceptual drift.
- Keeps “subject” as a single source of truth across the application.
- Syllabus changes occur at the topic level more than the subject level.

---

## Recommended Schema Additions

### `syllabus_versions`
Used to support current and future LECPA syllabi.

Fields:
- `id` (uuid)
- `code` (string, e.g. `2022-10`)
- `effective_date` (date)
- `source` (string, nullable — e.g., PDF filename or URL)
- timestamps

---

### Extend `subjects`
Keep all existing fields and add:

- `syllabus_code` (string, nullable)  
  Official syllabus code (e.g., `MS`, `AUD`)
- `default_syllabus_version_id` (fk → `syllabus_versions`, nullable)  
  Indicates the active syllabus for the subject
- `exam_question_count` (int, nullable)  
  Example: 70 for FAR, 100 for RFBT

---

### `syllabus_topics`
Canonical, versioned syllabus tree.

Fields:
- `id` (uuid)
- `subject_id` (fk → `subjects`)
- `syllabus_version_id` (fk → `syllabus_versions`)
- `topic_code` (string, e.g., `AUD-1.2.3`)
- `title` (string)
- `parent_id` (nullable fk → `syllabus_topics`)
- `depth` (tinyint)
- `display_order` (int)
- `is_leaf` (boolean)
- timestamps

Constraints:
- Unique `(syllabus_version_id, topic_code)`
- Indexed on `subject_id`, `parent_id`, `topic_code`

---

## Resulting Benefits

- One unified `subjects` table for the entire application.
- Clean, versioned syllabus topic trees.
- Ability to switch or add syllabi without schema rewrites.
- Minimal migration risk and maximum long‑term flexibility.

---

**This document reflects final architectural decisions for Phase 1 (Syllabus → Canonical Data Model).**
