#!/bin/bash

# Database connection
DB_NAME="school_db"
DB_USER="mamuchdak"
CSV_DIR="database/csv"

# List of tables (in correct import order if there are FK constraints)
TABLES=(
  users
  roles
  permissions
  role_has_permissions
  model_has_roles
  model_has_permissions
  personal_access_tokens
  password_reset_tokens
  login_histories
  holidays
  cache
  cache_locks
  job_batches
  jobs
  activity_log
  curriculum_levels
  school_structures
  schools
)

# Import each table
for TABLE in "${TABLES[@]}"; do
  CSV_FILE="$CSV_DIR/$TABLE.csv"
  if [ -f "$CSV_FILE" ]; then
    echo "Importing $TABLE from $CSV_FILE"
    psql -U "$DB_USER" -d "$DB_NAME" -c "\copy $TABLE FROM '$CSV_FILE' DELIMITER ',' CSV HEADER"
  else
    echo "⚠️  $CSV_FILE not found, skipping..."
  fi
done

echo "✅ All CSVs imported."

