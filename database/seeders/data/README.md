# LECPA Syllabus Dataset — v2022-10 (Generated)

This dataset is a structured extraction of the **LECPA CPA Syllabi Effective October 2022** PDF into a canonical topic tree suitable for seeding into the app’s `syllabus_topics` table.

## Files Generated

- `lecp_syllabus_v2022_10_full.json` — Full topic tree (all subjects)

## Contents Summary

Node counts **excluding** subject root nodes:

- FAR: 157
- AFAR: 168
- MS: 106
- AUD: 158
- RFBT: 427
- TAX: 106

Total nodes **including** subject roots: 1128

## Topic Code Conventions

- Subject root nodes use the subject code as the topic code (e.g., `FAR`, `AUD`, `RFBT`).
- Child nodes use `{SUBJECT}-{numbering}` where `numbering` matches the syllabus outline numbering with trailing `.0` removed:
  - `1.0` → `1`
  - `2.0` → `2`
  - `2.3.2.1` → `2.3.2.1`

Examples:
- `FAR-2.3.5.2`
- `AUD-2.2.4.4.6`
- `RFBT-7.2.12.4.8`

## Where to Place the JSON

Recommended path (matches our earlier plan):

```
database/seeders/data/lecp_syllabus_v2022_10_full.json
```

You can either:
- replace your existing dataset with the generated file, or
- keep both and update the seeder to point to the generated file.

## Seeding Notes

- Because `syllabus_topics.id` uses random UUIDs, the **stable key** for upsert is:
  - `(syllabus_version_id, topic_code)`

- For subject mapping:
  - Your internal subject codes are `MAS` and `AUDIT`, but the dataset uses official syllabus codes `MS` and `AUD`.
  - Ensure your `subjects` table has `syllabus_code` mapping:
    - `MAS → MS`
    - `AUDIT → AUD`

Generated at: 2026-01-10T22:48:38.306800Z
