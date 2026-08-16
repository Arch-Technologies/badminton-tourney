# ShuttleSync

A badminton tournament / competition management system: tournaments,
events (singles/doubles categories), player registration, automatic
bracket generation (single elimination, double elimination, round robin),
and score entry.

Built as a deliberately simple, self-contained Laravel app so it can run on
low-end shared hosting:

- **SQLite** — no database server to provision.
- **Blade + Tailwind (pre-built)** — no Node/npm needed on the server.
- **No queue workers, no Redis** — everything runs synchronously.

See [DEPLOY.md](DEPLOY.md) for DreamHost shared-hosting deployment
instructions.

## How it's organized

- **Public site** (`/`, `/tournaments/...`) — anyone can browse published
  tournaments, view brackets/standings, and submit a registration for an
  event.
- **Organizer area** (`/organizer/...`, requires login) — create
  tournaments and events, approve/reject registrations, generate brackets,
  and record match results.
- `app/Services/BracketGenerator.php` — builds the match tree for all three
  formats from approved registrations.
- `app/Services/BracketProgression.php` — advances winners/losers through
  the bracket as results come in (including bye/walkover resolution and
  double-elimination's grand-final reset match).
- `app/Services/MatchResultRecorder.php` — validates and records game
  scores for a match.
- `app/Services/StandingsCalculator.php` — computes round-robin standings.

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed   # seeds a demo tournament + organizer login
npm run dev                  # or: npm run build
php artisan serve
```

Demo organizer login after seeding: `organizer@example.com` / `password`.

## Tests

None yet — this was built and verified via manual smoke testing (route
checks, full bracket-generation simulations for all three formats
including bye cascades and a double-elimination grand-final reset, and
live HTTP form submissions for scoring and public registration). If you
extend this app, consider adding PHPUnit/Pest feature tests around
`BracketGenerator` and `MatchResultRecorder` first — that's where the
real complexity lives.
