# Roadmap

Where the Hunter System stands, what shipped when, and what is open.

Kept at the repo root beside `README.md`, `AGENTS.md` and `CLAUDE.md` rather
than in a `docs/` folder, per the project rule against new base directories.

## Milestones

M4–M9 were **not** worked through one at a time. All five milestone test files
landed together in `a4c0b98` ("Build Hunter System: RPG progression backend and
dashboard", 2026-09-09), so the milestone numbering describes *scope*, not a
sequence still in progress.

| Milestone | Scope | Backend | Front end |
| --- | --- | --- | --- |
| M4 | XP, levels, rank progression, stat allocation | Done `a4c0b98` | Done |
| M5 | Live workout, set logging, idempotent completion | Done `a4c0b98` | Completed later — see below |
| M6 | Daily and weekly missions, rewards, streaks | Done `a4c0b98` | Completed later |
| M7 | Calendar aggregation, heatmap, agenda | Done `a4c0b98` | **Page did not exist** — built later |
| M8/M9 | Achievements, dungeons, boss battles, titles | Done `a4c0b98` | Done |

### The pattern worth knowing

Those milestones shipped **backend-complete and front-end-incomplete**, and
their service tests passed the whole time. The gaps only showed up in a browser:

- **M7** — `CalendarService` was fully built and tested, but
  `Pages/Calendar/Index.vue` did not exist. The controller rendered a component
  that was not there, so `/calendar` returned 200 and then died client-side.
- **M5** — the set-completion endpoint could not be reached from the UI: the
  root Blade view had no `csrf-token` meta tag, so every "Complete set" threw a
  `TypeError`. The store also never flipped `is_completed`, pinning the UI to
  set one, and nothing ever called `workouts.complete`, so XP, streaks and
  program advancement never ran.
- **M6** — `WaterLitres` and `Steps` missions were generated but could never
  progress, because `synchronizeProgress` had no case for either metric.

If a milestone looks done, check that a user can actually reach it.

## Since the milestones

Work that closed those gaps, plus features no milestone covered.

| Area | State |
| --- | --- |
| Calendar page | Built: month grid, agenda, year heatmap |
| Live workout | CSRF, set advance, completion, timed exercises, rest from server config |
| Mission progress | `WaterLitres`, `ActiveDays`, `TrainingMinutes`, `DistanceKm` wired; `Steps` retired (nothing records steps) |
| Program editor | Full CRUD, policy, nested validation; system programs copy-to-edit |
| Mid-mission editing | Add, remove, reorder and resize exercises without losing logged sets |
| Hydration | `water_logs`, timezone-aware totals, weight-scaled target, card + modal, 7-day trend |
| Exercise library | 108 exercises, searchable and filterable, card grid with detail modal |
| Demonstrations | 108 animated GIFs from a movement-archetype renderer |
| Dashboard | Rank palette E–S, scanline panels, segmented attribute bars |
| Mobile nav | One `HunterNav` for both layouts; the dashboard drawer was permanently open |

### Seed content

108 exercises across 9 categories, 8 system programs, 14 missions. Every seeder
is idempotent; `SeedContentTest` fails the build if an active mission uses a
metric `synchronizeProgress` cannot compute.

### Exercise demonstrations

Six movements have bespoke poses; the rest render from a movement archetype
(motion + load + variant) in `exerciseArchetypes.js`, with per-exercise
refinement layers stacked on top:

```
exerciseDemoScene.js       base mannequin, IK, camera
  exerciseArchetypes.js    slug -> motion, load, variant
  exerciseCorrections.js   equipment fixes
  exerciseFormCorrections.js
  exerciseMotionRefinements.js
  exerciseApparatusRefinements.js   machines, benches, bars
```

Regenerate artwork with:

```bash
node resources/js/Support/generateExerciseDemos.mjs [slug ...]
```

`ExerciseDemos.test.mjs` projects every joint of every exercise across all 32
frames and fails if anything clips the 400x240 frame, so limbs leaving the
canvas break the build rather than needing to be spotted in a picture.

## Open

Nothing is currently blocking, and no placeholder data remains in the app.
Candidates, roughly by value:

- **Food logging** — `NutritionService` derives daily energy and macro targets
  from lean mass, training volume and goal, but nothing records what was
  actually eaten, so the card shows a target rather than intake against it.
- **Step tracking** — the retired "Reach 8,000 steps" mission can come back if a
  step source is added.
- **Market** — page exists as a coming-soon placeholder.
- **Program editing while enrolled** — locked today, because rebuilding
  structure nulls `workouts.program_day_id` and strands the enrollment. Lifting
  the lock means repointing scheduled workouts during the rebuild.

## Working on this repo

- Use `C:\php\php.exe`. The `php` on PATH is 8.4.0 and fails the Composer
  platform check before Laravel boots.
- `php artisan db:seed` blocks on a production confirmation if `.env` is not
  loaded — run it from the project root.
- Tests: `php artisan test --compact` and `node --test tests/Unit/<file>.mjs`
  (the node runner does not take a directory).
