-- ============================================================
-- Run this in phpMyAdmin against: fmsport_archery (central DB)
-- Creates: super admin user + first tenant + tenant_user link
-- ============================================================

-- Super admin user
-- Login: admin@fmsport.biz / Admin@2026!
INSERT INTO `users` (`name`, `email`, `email_verified_at`, `password`, `is_super_admin`, `remember_token`, `created_at`, `updated_at`)
VALUES (
  'Admin',
  'admin@fmsport.biz',
  NOW(),
  '$2y$12$bwhftA2LBFGXtegncbA3CeskNrcABcJFPpXQ4w0CyVt.9Kk0yfSsy',
  1,
  NULL,
  NOW(),
  NOW()
);

-- First tenant: Demo Archery Club
-- Tenant DB name: fmsport_demo  (create this DB in cPanel first)
INSERT INTO `tenants` (`name`, `slug`, `db_name`, `db_host`, `db_username`, `db_password`, `plan`, `status`, `created_at`, `updated_at`)
VALUES (
  'Demo Archery Club',
  'demo',
  'fmsport_demo',
  '217.216.35.201',
  'fmsport_archer12',
  'eyJpdiI6ImU0Y29hM1FHUGNGeVUzbFc5WHg1Ync9PSIsInZhbHVlIjoiR1VTbDR1cWhiV0dSS1F1QkJ6eE1hY241RDFITm5hWCsvY1JYNnhveXpHST0iLCJtYWMiOiJjMWYxN2RhOWZjZTNkMGIyYzNiNTlkMTAxODBjM2JkZDRlODE2ODMxZmZhZWJmNDA4MTFmYWE5MWI2Zjc4YzE0IiwidGFnIjoiIn0=',
  'free',
  'active',
  NOW(),
  NOW()
);

-- Link admin user to the tenant as club_admin
INSERT INTO `tenant_user` (`tenant_id`, `user_id`, `role`, `created_at`, `updated_at`)
VALUES (
  (SELECT id FROM tenants WHERE slug = 'demo'),
  (SELECT id FROM users WHERE email = 'admin@fmsport.biz'),
  'club_admin',
  NOW(),
  NOW()
);
