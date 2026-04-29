# RDFA-46 CEO Execution Plan

## Context
- Date: 2026-04-29
- Objective: Rebuild legacy website as a modern PHP/MySQL web platform.
- Constraints: no 1:1 clone, no committed secrets, no production deployment without approval.

## Phase Plan
1. Architecture and target operating model (CTO)
2. Frontend and content implementation (Lead Developer)
3. UX/A11y requirements hardening (UI/UX)
4. Quality gate and CI reliability (QA/DevOps)
5. CEO review and consolidation into a single handoff package

## Delegation Matrix
- CTO
  - Ownership: `docs/RDFA-46-CTO-IMPLEMENTATION-PLAN.md`, `docs/ARCHITECTURE.md`
  - Deliverables: target architecture, migration strategy, risk register, decision log
- Lead Developer
  - Ownership: `src/View/HomepageContent.php`, `public/index.php`, `public/assets/css/site.css`, `public/assets/js/site.js`
  - Deliverables: modernized landing page implementation, mobile-first behavior, no regression in contact flow
- UI/UX Designer
  - Ownership: `docs/RDFA-46-UIUX-DESIGN-BRIEF.md`, `docs/qa-checklist.md`
  - Deliverables: visual direction, UX requirements, accessibility acceptance criteria
- QA/DevOps
  - Ownership: `scripts/ci/qa-gate.sh`, `docs/RDFA-46-QA-DEVOPS-GATE.md`, optional minimal `.github/workflows/required-checks.yml` updates
  - Deliverables: deterministic QA gate, evidence documentation, no deployment expansion

## Acceptance Criteria
- Architecture, implementation, UX, and QA tracks all have explicit deliverables in versioned files.
- No secrets committed and no production deployment actions introduced.
- CI gate scripts remain executable and fail fast on violations.
- Changes are reviewable by file ownership and commit history.

## Risk Controls
- File ownership per role to avoid merge conflicts.
- No role is allowed to revert unknown changes.
- Validation is required in each role handoff (lint/smoke/checklist updates).

## CEO Next Actions
1. Collect outputs from all four delegated roles.
2. Run integration review and conflict check.
3. Trigger final QA gate locally.
4. Publish consolidated status update and next milestone.
