# php-tatr

**php-tatr** is a PHP port of [tsoding/tatr](https://github.com/tsoding/tatr) — a git-friendly, file-based task tracker.

All credit for the original design, the on-disk task format, and the TQL (Task Query Language) goes to [Alexey "tsoding" Kutepov](https://github.com/tsoding) and the [tatr](https://github.com/tsoding/tatr) project. This repository is a from-scratch reimplementation of the same ideas in PHP, so it can be installed and used anywhere PHP and Composer are available, without needing a C compiler.

There is no database and no server. Every task is a plain `TASK.md` file, sitting in its own folder, inside a `tasks/` directory that you commit to git alongside your code.

## Why would I want this?

- Your tasks live **next to your code**, in the same git repository, so they travel with clones, branches, and pull requests.
- Every task is a plain text file, so `git log`, `git blame`, and `git diff` all work on your task history for free.
- No servers, no accounts, no database — it's just files and a small CLI.

## Requirements

- PHP 8.2 or newer
- [Composer](https://getcomposer.org/) (to install the package)

You do **not** need to know PHP to use this tool. If you can run one command in a terminal, you can use `tatr`.

## Installation

If you don't already have a PHP project, create an empty folder and initialize Composer in it first:

```bash
mkdir my-project
cd my-project
composer init --no-interaction
```

Then install php-tatr as a **dev dependency** — it's a workflow tool you run from the terminal, not something your application's code calls at runtime, so it belongs alongside things like PHPUnit rather than in production:

```bash
composer require --dev keremardicli/php-tatr
```

This downloads the tool into your project's `vendor/` folder and creates a `vendor/bin/tatr` command you can run.

### Running the `tatr` command

Every command below starts with:

```bash
vendor/bin/tatr <command> [options]
```

On Windows, this also works from PowerShell or cmd.exe exactly the same way, as long as PHP is installed and on your `PATH`. If it isn't, you can always run it explicitly through PHP:

```bash
php vendor/bin/tatr <command> [options]
```

> Tip: if you get tired of typing `vendor/bin/tatr`, you can add an alias — on Mac/Linux, `alias tatr="vendor/bin/tatr"`; on Windows PowerShell, `Set-Alias tatr vendor\bin\tatr`. In the rest of this README we'll just write `tatr` for brevity.

## Quick start

```bash
# 1. Create a tasks/ directory in the current folder
tatr init

# 2. Create your first task
tatr new -t bug -p 50 Fix the login page

# 3. List your open tasks
tatr ls

# 4. Get a summary grouped by tag
tatr summary
```

That's it — a `tasks/` folder now exists in your project, with one sub-folder per task. Commit it to git like any other file:

```bash
git add tasks
git commit -m "Add first task"
```

## Commands

Run `tatr help` at any time to see this list from the CLI itself.

| Command | What it does |
|---|---|
| `tatr init` | Creates the `tasks/` directory in the current folder |
| `tatr new [TITLE...]` | Creates a new task |
| `tatr ls [QUERY...]` | Lists tasks, optionally filtered by a TQL query |
| `tatr find <HUID>` | Finds a single task by its ID |
| `tatr summary` | Shows task counts grouped by tag |
| `tatr ref [HUID]` | Finds tasks that mention/reference a given task |
| `tatr untag -t <tag> [QUERY...]` | Removes a tag from matching tasks |
| `tatr graph` | Generates a `graph.dot` file showing how tasks reference each other |
| `tatr version` | Shows the tatr version |
| `tatr help` | Shows usage help |

Every command also accepts `-help` to print its own usage.

### `tatr init`

Creates the `tasks/` folder. Run this once per project, from the project's root folder.

```bash
tatr init
tatr init -no-readme   # skip creating tasks/README.md
```

### `tatr new` — create a task

```bash
tatr new Buy milk
tatr new -t shopping,errand Buy milk
tatr new -t bug -p 10 Critical login bug
```

Flags:
- `-t <tags>` — comma or space separated tags, e.g. `-t bug,urgent`
- `-p <priority>` — a number that controls sort order in `ls` (default: `100`). By default `ls` lists tasks with the **highest** priority number first; add `-a` to `ls` to flip that around.
- `-s <suffix>` — appends a custom suffix to the generated task ID, useful if you create two tasks in the same second

Everything after the flags becomes the task title.

Each task is stored as `tasks/<HUID>/TASK.md`, where `<HUID>` (Human-Usable ID) is a timestamp like `20260914-082227`. You can open and edit that `TASK.md` file directly in any text editor — it's just markdown.

### `tatr ls` — list tasks

```bash
tatr ls                 # all open tasks, highest priority first
tatr ls -c               # include closed tasks too
tatr ls -a               # ascending order instead of descending
tatr ls -id               # sort by task ID instead of priority
tatr ls :bug              # only tasks tagged "bug"
```

The part after the flags is a **TQL** (Task Query Language) query — see below.

### `tatr find` — look up one task

```bash
tatr find 20260914-082227
tatr find 20260914-082227 -path-only   # just print the file path
```

### `tatr summary`

```bash
tatr summary       # counts of open tasks, grouped by tag
tatr summary -c    # same, but for closed tasks
```

### `tatr ref` — find related tasks

If task A's `TASK.md` mentions task B's ID anywhere in its text, `tatr ref` on task B will list task A. Handy for tracking "this task depends on that one" style relationships without a rigid schema.

```bash
tatr ref 20260914-082227
tatr ref              # uses the current folder name as the HUID, if you're inside a task folder
```

### `tatr untag` — remove a tag in bulk

```bash
tatr untag -t bug                 # remove the "bug" tag from every open task that has it
tatr untag -t bug -t urgent :bug  # remove multiple tags, only from tasks matching a query
tatr untag -t bug -c              # also consider closed tasks
```

### `tatr graph`

Scans every task for references to other task IDs and writes a `graph.dot` file (in the [Graphviz DOT format](https://graphviz.org/doc/info/lang.html)) describing those connections. If you have [Graphviz](https://graphviz.org/) installed (specifically the `neato` command), it will also render `graph.svg` automatically. If not, you still get the `.dot` file and can render it later, or view it with any online DOT viewer.

## The TQL query language

TQL (Task Query Language) is a tiny expression language used by `tatr ls` and `tatr untag` to filter tasks. You don't need to be a programmer to use it — think of it like search filters.

| Query | Meaning |
|---|---|
| *(empty)* | matches every task |
| `any` | matches every task (same as empty) |
| `:bug` | tasks tagged `bug` |
| `not :bug` | tasks **not** tagged `bug` |
| `:bug and :urgent` | tasks tagged both `bug` and `urgent` |
| `:bug or :feature` | tasks tagged `bug` or `feature` |
| `tagged` | tasks that have at least one tag |
| `not tagged` | tasks with no tags at all |
| `priority lt 50` | tasks with priority less than 50 |
| `priority ge 100` | tasks with priority greater than or equal to 100 |
| `20260914-082227` | the single task with that exact ID |
| `[:bug or :feature] and priority lt 100` | brackets group sub-expressions, just like in math |

Comparison keywords for `priority`: `lt` (less than), `le` (less than or equal), `gt` (greater than), `ge` (greater than or equal), `eq` (equal), `ne` (not equal).

Since these queries are just plain words, remember to quote them in your shell if they contain characters your shell treats specially, or simply pass them as separate arguments — `tatr ls :bug and :urgent` works fine without quotes because tatr joins all the trailing words back into one query internally.

## The `tasks/` folder format

Everything is plain text, so you can always edit files by hand if you want to:

```
tasks/
├── README.md                    (optional, created by `tatr init`)
├── tags                         (optional — descriptions for your tags)
└── 20260914-082227/
    └── TASK.md
```

A `TASK.md` file looks like this:

```markdown
# Fix the login page

- STATUS: OPEN
- PRIORITY: 50
- TAGS: bug,urgent

The login page throws an error when the password field is empty.
```

- The first `# Title` line is the task's title.
- `- KEY: VALUE` lines are properties. `STATUS`, `PRIORITY`, and `TAGS` are understood by tatr; you can add your own custom properties too, and they'll be preserved.
- Everything after the properties is free-form markdown — write whatever you like there.

An optional `tasks/tags` file lets you attach a human-readable description to each tag, shown by `tatr summary`:

```
bug     Something is broken
feature A new capability
```

## Developing php-tatr itself

If you want to work on php-tatr's own source code (not just use it as a dependency):

```bash
git clone https://github.com/KeremArdicli/php-tatr.git
cd php-tatr
composer install
vendor/bin/phpunit          # run the full test suite
php bin/tatr <command>      # run the CLI directly from source
```

The code is organized into three layers: `src/Core/` (task parsing, rendering, and storage), `src/Query/` (the TQL compiler and evaluator), and `src/Command/` (one class per CLI command, dispatched from `bin/tatr`).

## License

GPL-2.0-only, matching the license of the original [tsoding/tatr](https://github.com/tsoding/tatr) project.
