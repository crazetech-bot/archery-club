-- ============================================================
-- Run this in phpMyAdmin against: fmsport_demo (tenant DB)
-- Creates all tenant tables + migrations tracking
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `archers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `coach_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `dominant_hand` varchar(10) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `archers_user_id_index` (`user_id`),
  KEY `archers_coach_id_index` (`coach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coaches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coaches_user_id_unique` (`user_id`),
  KEY `coaches_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `archer_id` bigint(20) unsigned NOT NULL,
  `coach_id` bigint(20) unsigned DEFAULT NULL,
  `round_type` varchar(255) DEFAULT NULL,
  `distance_metres` smallint(5) unsigned DEFAULT NULL,
  `max_score` smallint(5) unsigned DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_sessions_archer_id_started_at_index` (`archer_id`,`started_at`),
  KEY `training_sessions_coach_id_foreign` (`coach_id`),
  CONSTRAINT `training_sessions_archer_id_foreign` FOREIGN KEY (`archer_id`) REFERENCES `archers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_sessions_coach_id_foreign` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `live_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_session_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `arrows_per_end` tinyint(3) unsigned NOT NULL DEFAULT 6,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_sessions_training_session_id_status_index` (`training_session_id`,`status`),
  CONSTRAINT `live_sessions_training_session_id_foreign` FOREIGN KEY (`training_session_id`) REFERENCES `training_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `live_ends` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `live_session_id` bigint(20) unsigned NOT NULL,
  `end_number` tinyint(3) unsigned NOT NULL,
  `total_score` smallint(5) unsigned NOT NULL DEFAULT 0,
  `x_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `ten_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `tag` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_ends_live_session_id_foreign` (`live_session_id`),
  CONSTRAINT `live_ends_live_session_id_foreign` FOREIGN KEY (`live_session_id`) REFERENCES `live_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `live_arrows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `live_end_id` bigint(20) unsigned NOT NULL,
  `arrow_number` tinyint(3) unsigned NOT NULL,
  `score` varchar(255) NOT NULL,
  `position_x` decimal(6,3) DEFAULT NULL,
  `position_y` decimal(6,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_arrows_live_end_id_foreign` (`live_end_id`),
  CONSTRAINT `live_arrows_live_end_id_foreign` FOREIGN KEY (`live_end_id`) REFERENCES `live_ends` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipment_setups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `archer_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `bow_type` varchar(255) NOT NULL,
  `bow_brand` varchar(255) DEFAULT NULL,
  `bow_model` varchar(255) DEFAULT NULL,
  `draw_weight_lbs` decimal(5,1) DEFAULT NULL,
  `draw_length_inches` decimal(4,1) DEFAULT NULL,
  `arrow_brand` varchar(255) DEFAULT NULL,
  `arrow_model` varchar(255) DEFAULT NULL,
  `arrow_spine` smallint(5) unsigned DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_setups_archer_id_is_current_index` (`archer_id`,`is_current`),
  CONSTRAINT `equipment_setups_archer_id_foreign` FOREIGN KEY (`archer_id`) REFERENCES `archers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `level` varchar(255) DEFAULT NULL,
  `round_type` varchar(255) DEFAULT NULL,
  `distance_metres` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `competitions_date_index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competition_results` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` bigint(20) unsigned NOT NULL,
  `archer_id` bigint(20) unsigned NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `score` smallint(5) unsigned DEFAULT NULL,
  `max_score` smallint(5) unsigned DEFAULT NULL,
  `placing` smallint(5) unsigned DEFAULT NULL,
  `competed_at` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competition_results_competition_id_archer_id_unique` (`competition_id`,`archer_id`),
  KEY `competition_results_archer_id_competed_at_index` (`archer_id`,`competed_at`),
  CONSTRAINT `competition_results_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `competition_results_archer_id_foreign` FOREIGN KEY (`archer_id`) REFERENCES `archers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lanes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `distance_metres` smallint(5) unsigned NOT NULL DEFAULT 18,
  `target_face` varchar(10) NOT NULL DEFAULT '40cm',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lanes_number_unique` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lane_bookings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lane_id` bigint(20) unsigned NOT NULL,
  `archer_id` bigint(20) unsigned DEFAULT NULL,
  `group_id` bigint(20) unsigned DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lane_bookings_lane_id_start_time_end_time_index` (`lane_id`,`start_time`,`end_time`),
  KEY `lane_bookings_archer_id_foreign` (`archer_id`),
  CONSTRAINT `lane_bookings_lane_id_foreign` FOREIGN KEY (`lane_id`) REFERENCES `lanes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lane_bookings_archer_id_foreign` FOREIGN KEY (`archer_id`) REFERENCES `archers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coach_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `archer_id` bigint(20) unsigned NOT NULL,
  `coach_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coach_notes_archer_id_coach_id_index` (`archer_id`,`coach_id`),
  CONSTRAINT `coach_notes_archer_id_foreign` FOREIGN KEY (`archer_id`) REFERENCES `archers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coach_notes_coach_id_foreign` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_03_06_000001_create_archers_table', 1),
  ('2026_03_06_000002_create_coaches_table', 1),
  ('2026_03_06_000003_create_training_sessions_table', 1),
  ('2026_03_06_000004_create_live_sessions_table', 1),
  ('2026_03_06_000005_create_live_ends_table', 1),
  ('2026_03_06_000006_create_live_arrows_table', 1),
  ('2026_03_06_000007_create_equipment_setups_table', 1),
  ('2026_03_06_000008_create_competitions_table', 1),
  ('2026_03_06_000009_create_competition_results_table', 1),
  ('2026_03_06_000010_create_lanes_table', 1),
  ('2026_03_06_000011_create_lane_bookings_table', 1),
  ('2026_03_07_000001_create_coach_notes_table', 1);

SET FOREIGN_KEY_CHECKS = 1;
