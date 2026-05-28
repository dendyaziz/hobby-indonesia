---
name: development-workflow
description: Development workflow conventions, database configurations, and standard iteration rules for the Hobby Indonesia application.
---

Use this skill to guide general coding, database connections, and iteration principles for the "Hobby Indonesia" active application. It establishes rules to keep the project clean, robust, and aligned with standard workflows.

## Technical Specifications

- **Project Name**: Hobby Indonesia (`hobby-indonesia`)
- **Framework**: Laravel 13
- **Admin Panel & UI**: Filament PHP (v5)
- **Database**: MySQL
  - **Database Name**: `hobby_indonesia`
  - **Username**: `hobby`
  - **Password**: `hKQZX1Oh@8*!7zOJ`
  - **Host**: `127.0.0.1`
- **Testing Framework**: Pest PHP

---

## Core Development Principles

### 1. Small, Iterative Steps
- Deliver small, incremental changes in each iteration.
- Commit changes frequently (after each minor success/milestone) rather than accumulating large backlogs of tasks.
- Verify correctness immediately using automated feature tests.

### 2. Clean & Tidy Codebase
- Write clean, expressive, and idiomatic Laravel/Filament code.
- Avoid messy hacks, workarounds, or ad-hoc solutions.
- Always follow best practices as outlined in official Laravel and Filament documentations.
