# Codex Prompt — Phase 1.2: Filament & API Polish (Read‑Only)

## Context

Phase 1.1 (Syllabus Dataset Integration & Validation) is either complete or in progress.
This phase assumes:

- The full LECPA syllabus dataset (v2022‑10) is seeded correctly
- `subjects`, `syllabus_versions`, and `syllabus_topics` schemas are unchanged
- Data integrity has already been validated

**Do NOT modify schemas.  
Do NOT modify seed data structure.  
Do NOT introduce editing capabilities for syllabus data.**

---

## Objective (Phase 1.2)

Improve **usability, clarity, and reliability** of syllabus access for:
- Admins (via Filament)
- Mobile clients (via API)

This phase is strictly **read‑only polish**.

---

## Hard Constraints

1. No database schema changes
2. No subject code changes
3. No syllabus topic editing (admin or API)
4. No new domain concepts
5. Must work with existing seeded data

---

## Part 1 — Filament Admin Improvements

### A) Tree‑First Browsing Experience
Enhance `SyllabusTopicResource` to support:

- Clear hierarchical indentation
- Subject filter (default to active syllabus version)
- Syllabus version filter (read‑only)
- Consistent ordering using `display_order`
- Visual depth indicator (indentation or badge)

---

### B) Readability Enhancements

Add:
- Topic code display (monospaced)
- Child count indicator
- Leaf node badge
- Subject badge
- Syllabus version badge

Ensure these are **visual only**, not editable.

---

### C) Search & Filters

Search must support:
- `topic_code`
- `title`

Filters:
- Subject
- Syllabus version
- Leaf / non‑leaf

---

## Part 2 — API Stability & Contract Hardening

### A) Tree Endpoint Consistency

Ensure tree endpoints:
- Always return children sorted by `display_order`
- Are version‑aware
- Never return orphan nodes

Recommended endpoints:
- `GET /api/v1/syllabus/subjects`
- `GET /api/v1/syllabus/subjects/{code}/tree`
- `GET /api/v1/syllabus/topics/{topicCode}`

---

### B) Response Shape Consistency

Each topic node should include:
- `id`
- `topic_code`
- `title`
- `depth`
- `is_leaf`
- `children` (empty array if leaf)

---

### C) Performance Guardrails

- Avoid N+1 queries
- Prefer eager loading
- Optional: cache full trees per `(subject, syllabus_version)`

Caching must be:
- Read‑only
- Safe to invalidate via cache clear

---

## Part 3 — Tests

Add tests that confirm:

- Filament resource loads with full dataset
- Tree endpoints return ordered hierarchies
- Leaf nodes never contain children
- API responses are version‑scoped

---

## Acceptance Criteria

- Admin can browse the full syllabus comfortably
- API consumers receive predictable, ordered trees
- No schema changes
- No data mutations
- No regressions from Phase 1.1

---

## Explicitly Out of Scope

- Question banks
- Content authoring
- Progress tracking
- Spaced repetition
- Permissions or roles

---

## Deliverables

- Updated Filament resource (read‑only UX polish)
- API controller refinements (if needed)
- Tests validating tree integrity and ordering
- Short summary of changes

---

**Stop after Phase 1.2.  
Do NOT proceed to Phase 2.  
Report results clearly.**
