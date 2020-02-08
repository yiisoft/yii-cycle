# Console commands

## Common

- `cycle/schema` - Get information about schema used

## Migrations

- `migrate/list` - get migrations list
- `migrate/create <name>` - create `<name>` file with empty migration
- `migrate/generate` - generate migration file based on the diff between schema and DB structure 
- `migrate/up` - Apply all not yet applied migrations 
- `migrate/down` - Rollback last migration 
