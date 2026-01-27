# AI Development Protocols

### 🎯 Project Awareness & Context
* **Startup Procedure:** Always read `PLANNING.md` (Architecture) and `TASK.md` (Current State) before writing code.
* **Stack Detection:** * If an `artisan` file exists, follow **Laravel** conventions (Eloquent, Migrations, Service Providers).
    * If no framework is present, use **Standalone PHP** with **PSR-12** standards and **PDO**.
* **Task Logging:** Check `TASK.md` before starting. If the task isn't listed, add it with a `[STAGED]` status and today's date.

### 🐘 PHP & Back-End Architecture
* **Language:** PHP 8.2+ with `declare(strict_types=1);` at the top of every file.
* **Database:** * **Laravel:** Use Migrations and Eloquent ORM.
    * **Standalone:** Use **PDO** with prepared statements. No `mysqli` or raw string interpolation.
* **Modularity:** Max **500 lines per file**. Refactor into Classes, Traits, or Service objects if this limit is approached.
* **Typing:** Use strict type-hinting for all function arguments and return types.

### 🎨 Front-End & UI
* **Styling:** Use **Tailwind CSS** as the primary styling utility.
* **Components:** Use a blend of **Shadcn UI** and **Radix UI** patterns (adapted for Blade, Livewire, or Inertia.js).
* **Architecture:** Keep UI logic separate from business logic. Use modular partials or components.

### 🧪 Testing & Reliability
* **Framework:** **PHPUnit**.
* **Location:** `/tests` directory mirroring the application structure.
* **Requirements:** Every feature must include:
    1. **Happy Path:** Standard successful execution.
    2. **Edge Case:** Empty inputs, null values, or unexpected data types.
    3. **Failure Case:** Proper exception handling for invalid data or unauthorized access.

### 📝 Documentation & Style
* **Naming:** `PascalCase` for Classes, `camelCase` for methods/variables, and `snake_case` for database columns.
* **The "Why" Rule:** Add an inline `// Reason:` comment for any complex logic explaining the intent, not just the action.
* **Updates:** Update `README.md` if new Composer packages are added or `.env` variables are modified.

### 🤖 AI Behavior & Guardrails
* **Context Preservation:** Never assume a database schema exists; verify migrations or SQL files first.
* **File Integrity:** Never delete or overwrite existing code unless explicitly part of a refactor task in `TASK.md`.
* **Uncertainty:** If the project structure is ambiguous or a path is missing, **ask for clarification** before proceeding.
* **Task Completion:** Mark tasks as `[COMPLETED]` in `TASK.md` only after the code is written and basic logical validation is performed.


### Finish your Session
 * Please go and look at all my work so far. and make sure i have used best coding practices, where efficient, and maintained good security.

### Load up context prompt
 * Take a look at the app and architecture. Understand deeply how it works inside and out. Ask me any questions if there are things you don't understand. This will be the basis for the rest of our conversation.

### Tool use summaries: - Add to Claude.MD
 * After completing a task that involves tool use, provide a quick summary of the work you've done

### investigate_before_answering
 * Never speculate about code you have not opened. If the user references a specific file, you MUST read the file before answering. Make sure to investigate and read relevant files BEFORE answering questions about the codebase. Never make any claims about code before investigating unless you are certain of the correct answer - give grounded and hallucination-free answers.

### Debugging 
 * You can view the loggs of apache and app logs to see (use subagent to tail at least 200 lines and return the relevant lines)
 * You can run the needed code in the console of the curent working folder.
