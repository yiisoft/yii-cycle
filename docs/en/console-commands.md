# Console commands

## Common

- `cycle/schema` - Get information about schema used
- `cycle/schema/php [file]` - Export current schema as PHP file
- `cycle/schema/clear` - Clear current schema cache

## Migrations

- `migrate/list` - Get migrations list
- `migrate/create <name>` - Create `<name>` file with empty migration
- `migrate/generate` - Generate migration file based on the diff between entity-annotation based schema and DB structure
- `migrate/up` - Apply all not yet applied migrations
- `migrate/down` - Rollback last migration
