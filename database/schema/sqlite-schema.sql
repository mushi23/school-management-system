CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "payments"(
  "id" integer primary key autoincrement not null,
  "student_id" integer not null,
  "invoice_id" integer,
  "amount" numeric not null,
  "method" varchar not null,
  "reference" varchar,
  "payment_date" date not null,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("student_id") references "students"("id") on delete cascade,
  foreign key("invoice_id") references "invoices"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "invoices"(
  "id" integer primary key autoincrement not null,
  "student_id" integer not null,
  "fee_structure_id" integer not null,
  "amount_due" numeric not null,
  "amount_paid" numeric not null default '0',
  "status" varchar not null default 'unpaid',
  "due_date" date not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("student_id") references "students"("id") on delete cascade,
  foreign key("fee_structure_id") references "fee_structures"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "fee_structures"(
  "id" integer primary key autoincrement not null,
  "school_id" integer not null,
  "academic_year" varchar not null,
  "term" varchar not null,
  "amount" numeric not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("school_id") references "schools"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "scholarships"(
  "id" integer primary key autoincrement not null,
  "student_id" integer not null,
  "fee_structure_id" integer not null,
  "discount_amount" numeric,
  "discount_percent" numeric,
  "reason" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("student_id") references "students"("id") on delete cascade,
  foreign key("fee_structure_id") references "fee_structures"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" varchar not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE TABLE IF NOT EXISTS "login_histories"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "ip_address" varchar,
  "user_agent" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "school_id" integer,
  "active" tinyint(1) not null default '1',
  "phone" varchar,
  "email_verification_token" varchar,
  "deleted_at" datetime,
  foreign key("school_id") references "schools"("id") on delete set null
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "grades"(
  "id" integer primary key autoincrement not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "curriculum_levels"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "schools"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "registration_number" varchar,
  "county" varchar not null,
  "sub_county" varchar,
  "location" varchar,
  "verified" tinyint(1) not null default('0'),
  "created_at" datetime,
  "updated_at" datetime,
  "curriculum_level_id" integer,
  "contact_email" varchar,
  "contact_phone" varchar,
  "active" tinyint(1) not null default '1',
  "deleted_at" datetime,
  "type" varchar,
  "region" varchar,
  "website" varchar,
  "terms" text,
  "streams" text,
  "levels" text,
  "services" text,
  "custom" text,
  "logo" text,
  "background" text,
  "brand_color" varchar not null default '#2563eb',
  "slogan" varchar,
  "is_setup_complete" tinyint(1) not null default '0',
  foreign key("curriculum_level_id") references "curriculum_levels"("id") on delete set null
);
CREATE UNIQUE INDEX "schools_name_unique" on "schools"("name");
CREATE UNIQUE INDEX "schools_registration_number_unique" on "schools"(
  "registration_number"
);
CREATE TABLE IF NOT EXISTS "students"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "admission_number" varchar not null,
  "dob" date not null,
  "gender" varchar not null,
  "school_id" integer not null,
  "guardian_name" varchar,
  "guardian_contact" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "grade_id" integer,
  "pathway" varchar,
  "curriculum_grade_id" integer,
  foreign key("grade_id") references grades("id") on delete cascade on update no action,
  foreign key("school_id") references schools("id") on delete cascade on update no action,
  foreign key("curriculum_grade_id") references "grades"("id") on delete set null
);
CREATE UNIQUE INDEX "students_admission_number_unique" on "students"(
  "admission_number"
);
CREATE TABLE IF NOT EXISTS "subjects"(
  "id" integer primary key autoincrement not null,
  "curriculum_grade_id" integer not null,
  "name" varchar not null,
  "type" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("curriculum_grade_id") references "curriculum_grades"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "learning_outcomes"(
  "id" integer primary key autoincrement not null,
  "subject_id" integer not null,
  "outcome" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("subject_id") references "subjects"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "competencies"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "subject_competency"(
  "id" integer primary key autoincrement not null,
  "subject_id" integer not null,
  "competency_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("subject_id") references "subjects"("id") on delete cascade,
  foreign key("competency_id") references "competencies"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "payment_audits"(
  "id" integer primary key autoincrement not null,
  "payment_id" integer not null,
  "action" varchar not null,
  "performed_by" varchar not null,
  "remarks" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("payment_id") references "payments"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "activity_log"(
  "id" integer primary key autoincrement not null,
  "log_name" varchar,
  "description" text not null,
  "subject_type" varchar,
  "subject_id" integer,
  "causer_type" varchar,
  "causer_id" integer,
  "properties" text,
  "created_at" datetime,
  "updated_at" datetime,
  "event" varchar,
  "batch_uuid" varchar
);
CREATE INDEX "subject" on "activity_log"("subject_type", "subject_id");
CREATE INDEX "causer" on "activity_log"("causer_type", "causer_id");
CREATE INDEX "activity_log_log_name_index" on "activity_log"("log_name");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_05_20_090000_create_schools_table',1);
INSERT INTO migrations VALUES(5,'2025_05_20_091000_create_students_table',1);
INSERT INTO migrations VALUES(6,'2025_05_20_092000_create_payments_table',1);
INSERT INTO migrations VALUES(7,'2025_05_20_093000_create_invoices_table',1);
INSERT INTO migrations VALUES(8,'2025_05_20_094000_create_fee_structures_table',1);
INSERT INTO migrations VALUES(9,'2025_05_20_095000_create_scholarships_table',1);
INSERT INTO migrations VALUES(10,'2025_05_21_072531_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(11,'2025_05_21_135224_add_role_to_users_table',1);
INSERT INTO migrations VALUES(12,'2025_05_22_102206_create_login_histories_table',1);
INSERT INTO migrations VALUES(13,'2025_05_22_123836_drop_role_from_users_table',1);
INSERT INTO migrations VALUES(14,'2025_05_22_124410_create_permission_tables',1);
INSERT INTO migrations VALUES(15,'2025_05_27_063154_add_school_id_to_users_table',1);
INSERT INTO migrations VALUES(16,'2025_06_03_074310_add_curriculum_fields_to_students_table',1);
INSERT INTO migrations VALUES(17,'2025_06_03_074413_create_grades_table',1);
INSERT INTO migrations VALUES(18,'2025_06_03_074414_create_curriculum_levels_table',1);
INSERT INTO migrations VALUES(19,'2025_06_03_113643_add_curriculum_level_id_to_schools_table',1);
INSERT INTO migrations VALUES(20,'2025_06_03_114314_add_curriculum_grade_id_to_students_table',1);
INSERT INTO migrations VALUES(21,'2025_06_03_115128_create_subjects_table',1);
INSERT INTO migrations VALUES(22,'2025_06_03_115421_create_learning_outcomes_table',1);
INSERT INTO migrations VALUES(23,'2025_06_03_115554_create_competencies_table',1);
INSERT INTO migrations VALUES(24,'2025_06_03_120124_create_subject_competency_table',1);
INSERT INTO migrations VALUES(25,'2025_06_07_144934_add_contact_email_and_phone_to_schools_table',1);
INSERT INTO migrations VALUES(26,'2025_06_07_152704_remove_contact_from_schools_table',1);
INSERT INTO migrations VALUES(27,'2025_06_09_084804_add_active_column_to_users_table',1);
INSERT INTO migrations VALUES(28,'create_payment_audits_table',1);
INSERT INTO migrations VALUES(29,'2025_06_09_111210_add_phone_to_users_table',2);
INSERT INTO migrations VALUES(30,'2025_06_09_111345_add_email_verification_token_to_users_table',3);
INSERT INTO migrations VALUES(31,'2025_06_09_144842_add_active_column_to_schools_table',4);
INSERT INTO migrations VALUES(32,'2025_06_09_154724_add_deleted_at_to_schools_table',5);
INSERT INTO migrations VALUES(33,'2025_06_09_155949_add_deleted_at_to_users_table',6);
INSERT INTO migrations VALUES(34,'2025_06_09_163354_create_activity_log_table',7);
INSERT INTO migrations VALUES(35,'2025_06_09_163355_add_event_column_to_activity_log_table',7);
INSERT INTO migrations VALUES(36,'2025_06_09_163356_add_batch_uuid_column_to_activity_log_table',7);
INSERT INTO migrations VALUES(37,'2025_06_13_112655_add_onboarding_fields_to_schools_table',8);
INSERT INTO migrations VALUES(38,'2025_06_13_132559_add_is_setup_complete_to_schools_table',9);
