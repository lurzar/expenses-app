# PostgreSQL 18 local-volume upgrade

The `v2.0.6` development stack pins PostgreSQL 18.4. PostgreSQL data directories
created by an earlier major version cannot be mounted directly by PostgreSQL 18.
The application does not delete, rewrite, or migrate an existing Docker volume
automatically.

## Before switching versions

1. Check the current server major version while the existing stack is running:

   ```sh
   docker compose exec pgsql postgres --version
   ```

2. If it is earlier than 18, create and verify a logical backup before checking
   out or starting the updated stack:

   ```sh
   docker compose exec -T pgsql sh -lc 'pg_dumpall -U "$POSTGRES_USER"' > expenses-app-pg-backup.sql
   test -s expenses-app-pg-backup.sql
   ```

3. Stop the old stack without deleting its volumes:

   ```sh
   docker compose down
   ```

   Do not run `docker compose down --volumes` or delete the old PostgreSQL
   volume. It is the rollback source until the restored database is verified.

## Restore into PostgreSQL 18

Use a new Compose project name so PostgreSQL 18 receives a new volume while the
old volume remains intact:

```sh
export COMPOSE_PROJECT_NAME=expenses-app-pg18
docker compose up -d pgsql
docker compose exec -T pgsql sh -lc 'psql -U "$POSTGRES_USER" -d postgres' < expenses-app-pg-backup.sql
docker compose up -d
```

Keep the same `COMPOSE_PROJECT_NAME` for later commands, or store it in the
local `.env` file. Run the application tests and inspect important records
before retiring the old project volume.

## Rollback

Stop the PostgreSQL 18 project, unset `COMPOSE_PROJECT_NAME`, check out the
previous application revision and matching PostgreSQL image, then restart the
original Compose project. Do not attach the PostgreSQL 18 data volume to an
older server major.
