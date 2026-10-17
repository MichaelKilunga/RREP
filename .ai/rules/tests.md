---
paths:
  - 'tests/**'
---

# Tests

## Tests run on in-memory SQLite, seeded once, with Vite disabled
phpunit.xml sets DB_CONNECTION=sqlite / DB_DATABASE=:memory:; tests/TestCase.php uses RefreshDatabase with $seed = true (DatabaseSeeder runs once per process) and calls withoutVite(). Never point tests at the MySQL `rrep` dev database again. Seeded fixtures tests rely on: admin@rehospace.com / password, PropertyType rows, published properties and projects. Blade escapes `&` to `&amp;`, so assertSee('A & B') must NOT pass escape=false. Branch, Property and other HasUuid models use `uuid` as the route key, so route('...', $model) yields the uuid; controllers must use implicit binding (not find($id)). tests/Feature/PageSmokeTest.php renders every public and staff GET page; extend its route lists when adding pages.
