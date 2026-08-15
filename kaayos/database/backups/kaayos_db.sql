-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 01, 2026 at 10:43 AM
-- Server version: 8.0.46-0ubuntu0.24.04.2
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kaayos_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `worker_id` bigint UNSIGNED NOT NULL,
  `service_category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `price` decimal(10,2) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-0e20fcf5f694714a7418ef90da99d3b9', 'i:1;', 1782870448),
('laravel-cache-0e20fcf5f694714a7418ef90da99d3b9:timer', 'i:1782870448;', 1782870448),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1782772879),
('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1782772879;', 1782772879),
('laravel-cache-3b07f30c26fffaf85af950e091b36921', 'i:5;', 1782780171),
('laravel-cache-3b07f30c26fffaf85af950e091b36921:timer', 'i:1782780171;', 1782780171),
('laravel-cache-77de68daecd823babbb58edb1c8e14d7106e83bb', 'i:1;', 1782776480),
('laravel-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer', 'i:1782776480;', 1782776480),
('laravel-cache-79e6a2f77cbfb3f93fbe09feb0ea9b5c', 'i:1;', 1782870094),
('laravel-cache-79e6a2f77cbfb3f93fbe09feb0ea9b5c:timer', 'i:1782870094;', 1782870094),
('laravel-cache-b95cd370efcdece21569bbc0a6e262f3', 'i:1;', 1782520188),
('laravel-cache-b95cd370efcdece21569bbc0a6e262f3:timer', 'i:1782520188;', 1782520188),
('laravel-cache-eb51d312665541458b56886dfc45a820', 'i:3;', 1782870331),
('laravel-cache-eb51d312665541458b56886dfc45a820:timer', 'i:1782870331;', 1782870331),
('laravel-cache-otp-send:1', 'i:2;', 1782245758),
('laravel-cache-otp-send:1:timer', 'i:1782245758;', 1782245758);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `raised_by` bigint UNSIGNED NOT NULL,
  `status` enum('open','under_review','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_20_000000_add_fields_to_user_table', 2),
(5, '2026_06_27_000005_create_bookings_table', 3),
(6, '2026_06_22_000000_create_password_otp_tokens_table', 4),
(7, '2026_06_21_123341_create_personal_access_tokens_table', 5),
(9, '2026_06_23_202009_add_preferences_and_avatar_to_users_table', 7),
(10, '2026_06_27_000001_create_worker_profiles_table', 8),
(11, '2026_06_27_000002_create_work_portfolios_table', 8),
(12, '2026_06_27_000003_create_worker_documents_table', 8),
(13, '2026_06_27_000004_add_login_lockout_to_users_table', 9),
(14, '2026_06_30_000001_add_admin_role_and_suspension_to_users', 10),
(15, '2026_06_30_000002_add_rejected_status_and_review_to_worker_documents', 10),
(16, '2026_06_30_000003_create_notifications_table', 10),
(17, '2026_06_30_000004_create_service_categories_table', 10),
(18, '2026_06_30_000005_create_services_table', 10),
(19, '2026_06_30_000006_create_provider_services_table', 10),
(20, '2026_06_30_000007_create_disputes_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('286ac746-8955-47d8-a88f-f3815be995a5', 'App\\Notifications\\VerificationApproved', 'App\\Models\\User', 4, '{\"title\":\"Verification Approved\",\"message\":\"Your verification documents have been approved. You are now a verified service provider.\",\"user_id\":4}', NULL, '2026-06-30 17:40:20', '2026-06-30 17:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `password_otp_tokens`
--

CREATE TABLE `password_otp_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_otp_tokens`
--

INSERT INTO `password_otp_tokens` (`id`, `user_id`, `token`, `expires_at`, `used`, `created_at`, `updated_at`) VALUES
(1, 1, '$2y$12$WdB5ZnYHwN.x57P4EmMJV.680c8TswcZ1bo.eOcUbs/08/xJ8Iu82', '2026-06-23 12:15:58', 1, '2026-06-23 12:05:58', '2026-06-23 12:06:18'),
(2, 1, '$2y$12$AUYcvj9m2RIy2MncWj8fnOeCh/iAYZ/DXVatBHUHh6uIdgCE.2Una', '2026-06-23 12:18:47', 0, '2026-06-23 12:08:47', '2026-06-23 12:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('johndavebriones09@gmail.com', '$2y$12$xhUheuWUM6Dh3VNFsMXCYuJrPabg7I/CjcNXsTnhwNP6MZyeQU7E.', '2026-06-29 16:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'account-page', '96d30e4886ba2fe9802811a715bb18faf0e7b93dac9c6a34031e2f7a0f444d21', '[\"*\"]', NULL, NULL, '2026-06-21 04:36:10', '2026-06-21 04:36:10'),
(2, 'App\\Models\\User', 1, 'account-page', 'b784a2297483ef00e0bbc7c46d1292387eb8b2fa61a325cef7e3ce14d0e0abf0', '[\"*\"]', NULL, NULL, '2026-06-21 04:38:12', '2026-06-21 04:38:12'),
(3, 'App\\Models\\User', 1, 'account-page', '08ec73afb41ee14b179cd89a1f6db887c39d0d3d5a2eed58c0645e21f93a1ddc', '[\"*\"]', NULL, NULL, '2026-06-21 04:39:26', '2026-06-21 04:39:26'),
(4, 'App\\Models\\User', 1, 'account-page', '317a2c93b0e169805d3b967b75a4d3c2a722e2ac2a8dc8384d696ead838ddf28', '[\"*\"]', NULL, NULL, '2026-06-21 04:44:05', '2026-06-21 04:44:05'),
(5, 'App\\Models\\User', 1, 'account-page', '590700f25bd5335e7456901119d035fd8370d15e526430809ee9a1da13b2a2ee', '[\"*\"]', NULL, NULL, '2026-06-21 04:46:46', '2026-06-21 04:46:46'),
(6, 'App\\Models\\User', 1, 'account-page', 'e0366bf1ee79c7628a93b7935eb84612f59bfb6cc80b42189ee75e677d9d33c0', '[\"*\"]', NULL, NULL, '2026-06-21 04:47:00', '2026-06-21 04:47:00'),
(7, 'App\\Models\\User', 1, 'account-page', '00e65709ddb428319781ce745165ede38411c53533200ab044a5faa931fcccaa', '[\"*\"]', NULL, NULL, '2026-06-21 04:50:57', '2026-06-21 04:50:57'),
(8, 'App\\Models\\User', 1, 'account-page', 'bf7254fd2d6ffb1077e75832995b6a9eda61587fc1bd241a9e6e8cd047a0f726', '[\"*\"]', NULL, NULL, '2026-06-21 04:53:15', '2026-06-21 04:53:15'),
(9, 'App\\Models\\User', 1, 'account-page', '40be987c19ea5a23edc1acdda6f3f264f595cbb13241170c24fae4b7f22e827e', '[\"*\"]', NULL, NULL, '2026-06-21 04:59:16', '2026-06-21 04:59:16'),
(10, 'App\\Models\\User', 1, 'account-page', 'c074236b4786e20d67a6cfec28845f32fb279558d3b242274c2330809052467a', '[\"*\"]', NULL, NULL, '2026-06-21 05:29:05', '2026-06-21 05:29:05'),
(11, 'App\\Models\\User', 1, 'account-page', 'f94c2a0ebd39ae5949973389afc92b0485dbe0dc2ffe190149cc2ec95d90900c', '[\"*\"]', NULL, NULL, '2026-06-21 05:29:37', '2026-06-21 05:29:37'),
(12, 'App\\Models\\User', 1, 'account-page', '31d84e264619952c1d3349b8b02f8249f4f30a5c36adb377f35b2186637f1ea6', '[\"*\"]', NULL, NULL, '2026-06-21 05:49:49', '2026-06-21 05:49:49'),
(13, 'App\\Models\\User', 1, 'account-page', '3618db0a7bf5815cb13ca15a6bda1c53939225403b49f6edeb63c8dfc168fbbd', '[\"*\"]', NULL, NULL, '2026-06-21 13:26:37', '2026-06-21 13:26:37'),
(14, 'App\\Models\\User', 1, 'account-page', 'a4e9a9de904c03c35eb35f8872cb992c2467577e08ae15ec1099b608c21ca074', '[\"*\"]', NULL, NULL, '2026-06-21 15:40:02', '2026-06-21 15:40:02'),
(15, 'App\\Models\\User', 1, 'account-page', 'a05619479e343d2ee118b4720878aa3c6fd46ef906930c0c3d67a4599f46acc4', '[\"*\"]', NULL, NULL, '2026-06-21 15:40:07', '2026-06-21 15:40:07'),
(16, 'App\\Models\\User', 1, 'account-page', '94cd3e63b86a641e06023c3b7f8dcf168fec7be95d9792bca2be83843c1a8725', '[\"*\"]', NULL, NULL, '2026-06-21 15:40:10', '2026-06-21 15:40:10'),
(17, 'App\\Models\\User', 1, 'account-page', 'f8054f4f6bfe21b013e394ddb539c7dd77b8dc40546d0c79c69e939a4fdfd71b', '[\"*\"]', NULL, NULL, '2026-06-21 15:51:15', '2026-06-21 15:51:15'),
(18, 'App\\Models\\User', 1, 'account-page', '0b36b347260ce4958f5181d6b92441dce6626939f0a0f9e7858e31c438232cee', '[\"*\"]', NULL, NULL, '2026-06-21 15:53:10', '2026-06-21 15:53:10'),
(19, 'App\\Models\\User', 1, 'account-page', '0f6c214a3f13b2cf2fcf406c993c92caa4c361abf7eea7648ecb0ae60405fde0', '[\"*\"]', NULL, NULL, '2026-06-23 00:39:24', '2026-06-23 00:39:24'),
(20, 'App\\Models\\User', 1, 'account-page', '1498d844f24cfcebd6ed31ce33f1ffa56389ea3c3270e8cf78e9fb66817223f8', '[\"*\"]', NULL, NULL, '2026-06-23 01:37:45', '2026-06-23 01:37:45'),
(21, 'App\\Models\\User', 1, 'account-page', '3cb2cb86112c2f30b46fe8486d2cdcfa68eb0e7e4f00196490864e2b79445c91', '[\"*\"]', NULL, NULL, '2026-06-23 01:38:11', '2026-06-23 01:38:11'),
(22, 'App\\Models\\User', 1, 'account-page', 'c3f5c008b51ec72b1581c8574c351d0fd0562c09fa487762864587788795217c', '[\"*\"]', NULL, NULL, '2026-06-23 02:07:52', '2026-06-23 02:07:52'),
(23, 'App\\Models\\User', 1, 'account-page', 'dfb5ff83df53f0e72a5a8a1a18db6b5e1a2c4122bbebca885660b988e986c871', '[\"*\"]', NULL, NULL, '2026-06-23 02:31:58', '2026-06-23 02:31:58'),
(24, 'App\\Models\\User', 1, 'account-page', '34ab7fc16f032e35e2a81ccee6bfe9dabf3b9679d8dafbe088051edef675547f', '[\"*\"]', NULL, NULL, '2026-06-23 02:34:14', '2026-06-23 02:34:14'),
(25, 'App\\Models\\User', 1, 'account-page', 'd0f3f3d403b85beb929cbcd7672272d084e1a50f3fce146c8b7f34314c8e26c3', '[\"*\"]', NULL, NULL, '2026-06-23 02:47:11', '2026-06-23 02:47:11'),
(26, 'App\\Models\\User', 1, 'account-page', '454a66e14f30ae9c7d96a22ab56aa800fb6ca3b22530d8032726b0eb0386866e', '[\"*\"]', NULL, NULL, '2026-06-23 02:48:38', '2026-06-23 02:48:38'),
(27, 'App\\Models\\User', 1, 'account-page', 'b5447f5f5aa502613e678fddd4a20e0a60c1af780640f1439bae629cd54ad173', '[\"*\"]', NULL, NULL, '2026-06-23 04:32:41', '2026-06-23 04:32:41'),
(28, 'App\\Models\\User', 1, 'account-page', 'f9d7b7f62942a4eb862452840ab02c831bd3e9bf0017d929326d4d454f1e6759', '[\"*\"]', NULL, NULL, '2026-06-23 04:33:46', '2026-06-23 04:33:46'),
(29, 'App\\Models\\User', 1, 'account-page', 'f7e9b440e0dab499d95ab5b7916aac48b14dcfe0ad441a5b396095fc5259ab02', '[\"*\"]', NULL, NULL, '2026-06-23 04:34:00', '2026-06-23 04:34:00'),
(30, 'App\\Models\\User', 1, 'account-page', '794fadf73395ce67d89d4fc9950ee8a14b2ed119b0499e2b6696dd246928aed6', '[\"*\"]', NULL, NULL, '2026-06-23 12:05:42', '2026-06-23 12:05:42'),
(31, 'App\\Models\\User', 1, 'account-page', 'c6d17c7547330737270a1562c8ced02627b0d03aecd04738d051fbabc0a9d834', '[\"*\"]', NULL, NULL, '2026-06-23 12:12:53', '2026-06-23 12:12:53'),
(32, 'App\\Models\\User', 1, 'account-page', '820ce0ab3c8cbad7a2d1892fc6e3f40d3b9b4f52d5d3dbebab1913d26d3f3fa8', '[\"*\"]', NULL, NULL, '2026-06-23 12:12:58', '2026-06-23 12:12:58'),
(33, 'App\\Models\\User', 1, 'account-page', '8d87a25b95be870d53790bfdaacbf185827d53edff20281e71aa3b1d95c92ba1', '[\"*\"]', NULL, NULL, '2026-06-23 12:13:30', '2026-06-23 12:13:30'),
(34, 'App\\Models\\User', 1, 'account-page', '8e066354d0430d3fb437c871f7c6d3ee6c9a15f2b75db8e1a7f7255aefbe1b00', '[\"*\"]', NULL, NULL, '2026-06-23 12:24:14', '2026-06-23 12:24:14'),
(35, 'App\\Models\\User', 1, 'account-page', '5bfac1d008fbef812772b56c27ff3e40a8f11c1c3e3ba1e6ebcd9e23255157b0', '[\"*\"]', NULL, NULL, '2026-06-23 12:24:39', '2026-06-23 12:24:39'),
(36, 'App\\Models\\User', 1, 'account-page', '182351910470cbc8eaeb5769eeb25743d4c2528619a84df4b11a3202bf9bee36', '[\"*\"]', NULL, NULL, '2026-06-23 12:24:51', '2026-06-23 12:24:51'),
(37, 'App\\Models\\User', 1, 'account-page', '7e27ca525e7d341cd4b03ec2075fbf88b1f9577c56a01e351477efce865f022e', '[\"*\"]', NULL, NULL, '2026-06-23 12:24:53', '2026-06-23 12:24:53'),
(38, 'App\\Models\\User', 1, 'account-page', '9b705e7c37e81900943dbe76071853218c4b6a64a04565efb44d45c5a8c38bb9', '[\"*\"]', NULL, NULL, '2026-06-23 12:24:55', '2026-06-23 12:24:55'),
(39, 'App\\Models\\User', 1, 'account-page', 'a3bdab5fcd7f3de972d568eebcfc60189bfeebbfc5a063f66eec0acd30da4404', '[\"*\"]', NULL, NULL, '2026-06-23 12:25:03', '2026-06-23 12:25:03'),
(40, 'App\\Models\\User', 1, 'account-page', 'be0d92ca85fd6f950a61f678c12880e7c84ad318739229b80920e3be8ba2f97f', '[\"*\"]', NULL, NULL, '2026-06-23 12:25:29', '2026-06-23 12:25:29'),
(41, 'App\\Models\\User', 1, 'account-page', '98c3f47ca168c7d9a0df2898787cd778ffee27def46f8f5aac23670d29fcc66d', '[\"*\"]', NULL, NULL, '2026-06-23 12:26:12', '2026-06-23 12:26:12'),
(42, 'App\\Models\\User', 1, 'account-page', 'bf191c5025294f01d591eec251e68e66d932d42dbcab4638bd80d9a6d8c88274', '[\"*\"]', NULL, NULL, '2026-06-23 12:31:49', '2026-06-23 12:31:49'),
(43, 'App\\Models\\User', 1, 'account-page', 'ee837cd86c3bf999281f8b2658b7a5d82bddd121c662e8f0ab69df3f2067463f', '[\"*\"]', NULL, NULL, '2026-06-23 12:31:59', '2026-06-23 12:31:59'),
(44, 'App\\Models\\User', 1, 'account-page', 'a63d60353b72dac2ce201b2aae9349f56d96277ad787297792f9d66a0aa0b1e9', '[\"*\"]', NULL, NULL, '2026-06-23 12:37:58', '2026-06-23 12:37:58'),
(45, 'App\\Models\\User', 1, 'account-page', '4cec1e8d98aadcc6129a663092d03487b8bfe0f1f63529c59ece1c66d7997473', '[\"*\"]', NULL, NULL, '2026-06-23 12:38:20', '2026-06-23 12:38:20'),
(46, 'App\\Models\\User', 1, 'account-page', 'd60aac2314d911eab1a91cd58b18c0799e904a10ce74ae12b21ae7887079a462', '[\"*\"]', NULL, NULL, '2026-06-23 13:04:16', '2026-06-23 13:04:16'),
(47, 'App\\Models\\User', 1, 'account-page', '7175c69ac1fd039b361f5cc0e66f94a45916c7759760bcd9b9fe17ad50d9c67b', '[\"*\"]', NULL, NULL, '2026-06-23 13:04:36', '2026-06-23 13:04:36'),
(48, 'App\\Models\\User', 1, 'account-page', '5f6f2813ad67747a15949958dbd5d28bd43ac3e444d61fb27f8cb5a34e7286bc', '[\"*\"]', NULL, NULL, '2026-06-23 13:04:49', '2026-06-23 13:04:49'),
(49, 'App\\Models\\User', 1, 'account-page', '63b8f12f059a9614cf075e4db14c50f3989210b1a3cc9ddf7ab6dffebe2eee5f', '[\"*\"]', NULL, NULL, '2026-06-23 13:05:08', '2026-06-23 13:05:08'),
(50, 'App\\Models\\User', 1, 'account-page', 'f57a78f5994ec56df0e4299e99d6d94929975d4937d27a460b468a588fccb367', '[\"*\"]', NULL, NULL, '2026-06-23 13:05:28', '2026-06-23 13:05:28'),
(51, 'App\\Models\\User', 1, 'account-page', 'd5f33d41e8906e7feccf53142645ab9ccefcf21ba231a5dcbd382f0482cd5d2b', '[\"*\"]', NULL, NULL, '2026-06-23 13:05:43', '2026-06-23 13:05:43'),
(52, 'App\\Models\\User', 1, 'account-page', '093d2ea827128747df0ea89cd0e54dff5df8233886d723d628e031b6f08a4ac0', '[\"*\"]', NULL, NULL, '2026-06-24 18:32:51', '2026-06-24 18:32:51'),
(53, 'App\\Models\\User', 1, 'account-page', 'ed8f3f20e24f28a2b369d75b6db0e77050dd66f15327d74510532d291f9a00ca', '[\"*\"]', NULL, NULL, '2026-06-24 18:32:52', '2026-06-24 18:32:52'),
(54, 'App\\Models\\User', 1, 'account-page', 'fd815556688e72aeb6e21ff4ebbf0869a7b0648b078663e982728601019ac950', '[\"*\"]', NULL, NULL, '2026-06-24 18:33:04', '2026-06-24 18:33:04'),
(55, 'App\\Models\\User', 1, 'account-page', '5b7eb587ff95da94ade12fb5e7da5a91744f1f8825c8cbe2ef90f421a83c0ddc', '[\"*\"]', NULL, NULL, '2026-06-24 18:33:42', '2026-06-24 18:33:42'),
(56, 'App\\Models\\User', 1, 'account-page', 'be2b3f5483545640045eafd7a6fa13caa87e0665353ee6da1af9cc6b115cf208', '[\"*\"]', NULL, NULL, '2026-06-24 18:35:54', '2026-06-24 18:35:54'),
(57, 'App\\Models\\User', 1, 'account-page', '4cad6181e1ed6279669f6622c806b130eeaa56e3e0e16e38da0bf089d4bb02b9', '[\"*\"]', NULL, NULL, '2026-06-24 18:45:24', '2026-06-24 18:45:24'),
(58, 'App\\Models\\User', 1, 'account-page', '412ec54c3fd754c0b6ab098df5a05b6b10418f923127c61c034de3d11e6b96af', '[\"*\"]', NULL, NULL, '2026-06-30 17:31:49', '2026-06-30 17:31:49'),
(59, 'App\\Models\\User', 1, 'account-page', '4e2189032aab1287eab2c83764c08eeaa47174092752052d13af9e82f2ac3560', '[\"*\"]', NULL, NULL, '2026-06-30 17:31:52', '2026-06-30 17:31:52'),
(60, 'App\\Models\\User', 1, 'account-page', '0039ad7b8e727fd19e20a77ca4affe986a7c5f993e2cc618b63d4491cc2c2df9', '[\"*\"]', NULL, NULL, '2026-06-30 17:32:17', '2026-06-30 17:32:17'),
(61, 'App\\Models\\User', 1, 'account-page', '14865eb3688d9ef54dbed21d04f88ece0dd229d98267d64fd3242c6c14854f1f', '[\"*\"]', NULL, NULL, '2026-06-30 17:32:21', '2026-06-30 17:32:21'),
(62, 'App\\Models\\User', 1, 'account-page', 'd47529f6d3f04dbba45c125692c7f73deefb61238a48174424e7434c971baf73', '[\"*\"]', NULL, NULL, '2026-06-30 17:44:48', '2026-06-30 17:44:48'),
(63, 'App\\Models\\User', 1, 'account-page', '9edd1f89124b497eaeec349fc0a2e015cf60cc393a1b156e87b08e2c9e6c97ba', '[\"*\"]', NULL, NULL, '2026-06-30 17:44:57', '2026-06-30 17:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

CREATE TABLE `provider_services` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3iysFhUJXqddOcB5MkCvdkdSZXoRXUsyOSpmVrkx', NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJjcHI2S05XZGF4cUdBanJHeHExMTdYZ2hzbnk3Tk1Tc2VRcEtFU1MyIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDAiLCJyb3V0ZSI6ImhvbWUifX0=', 1782875998);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','worker','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `service_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_notifications` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All updates',
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'English',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failed_login_attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `suspended_reason` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `name`, `email`, `phone`, `role`, `service_category`, `city`, `email_notifications`, `language`, `avatar`, `failed_login_attempts`, `locked_until`, `suspended_at`, `suspended_reason`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Dave Briones', 'John Dave Briones', 'johndavebriones09@gmail.com', '09165905802', 'client', 'electrical', 'Balayan, Batangas', 'All updates', 'Filipino', 'avatars/Gi8uJtmh65IhBzYK63htKEV18GsD9nU7beGLMfsj.png', 0, NULL, NULL, NULL, '2026-06-29 14:40:31', '$2y$12$/qz4XK7iFpFBd/E.SRidxuaHmOjSLf0QB6g9T8LI8ZVQFOATU5Zyy', NULL, '2026-06-17 11:58:20', '2026-06-29 14:40:31'),
(3, 'John Dave', 'Briones', 'John Dave Briones', 'johndavebriones05@gmail.com', '09165905802', 'admin', NULL, 'Tuy, Batangas', 'All updates', 'English', NULL, 0, NULL, NULL, NULL, '2026-06-29 15:40:42', '$2y$12$gKyX/19fEWvT1ljlX5pVeODVy.FJmrT1NnGVp0wj5TsHHPABOOvtu', NULL, '2026-06-17 22:20:49', '2026-06-29 16:50:49'),
(4, 'John Dave', 'Briones', 'John Dave Briones', '23-79211@g.batstate-u.edu.ph', '09165905802', 'worker', 'other', 'Balayan, Batangas', 'All updates', 'English', NULL, 0, NULL, NULL, NULL, '2026-06-26 15:32:10', '$2y$12$2Q9JWT096Zf3Nl1.SmIw0.ceI4HXzwC6w8mhkbN6JQcsuuPzL1JUy', NULL, '2026-06-26 15:29:48', '2026-06-30 17:33:48');

-- --------------------------------------------------------

--
-- Table structure for table `worker_documents`
--

CREATE TABLE `worker_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('verified','pending','not_submitted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_submitted',
  `verified_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `worker_documents`
--

INSERT INTO `worker_documents` (`id`, `user_id`, `document_type`, `file_path`, `status`, `verified_at`, `admin_notes`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'Government-Issued ID', 'documents/8Vy8MLbrZlc0RjdKJBmwL8CK7CaObAXk5VDMkGyT.jpg', 'verified', '2026-06-30 17:40:20', NULL, NULL, NULL, '2026-06-30 17:36:47', '2026-06-30 17:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `worker_profiles`
--

CREATE TABLE `worker_profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `skills` json DEFAULT NULL,
  `spoken_languages` json DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `available_days` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_areas` json DEFAULT NULL,
  `years_of_experience` tinyint UNSIGNED DEFAULT NULL,
  `service_radius` smallint UNSIGNED DEFAULT NULL,
  `service_zone` json DEFAULT NULL,
  `cover_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `worker_profiles`
--

INSERT INTO `worker_profiles` (`id`, `user_id`, `bio`, `skills`, `spoken_languages`, `hourly_rate`, `available_days`, `preferred_hours`, `service_areas`, `years_of_experience`, `service_radius`, `service_zone`, `cover_photo`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-26 15:08:48', '2026-06-26 15:08:48'),
(2, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-30 17:34:01', '2026-06-30 17:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `work_portfolios`
--

CREATE TABLE `work_portfolios` (
  `id` bigint UNSIGNED NOT NULL,
  `worker_profile_id` bigint UNSIGNED NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_client_id_foreign` (`client_id`),
  ADD KEY `bookings_worker_id_foreign` (`worker_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disputes_booking_id_foreign` (`booking_id`),
  ADD KEY `disputes_raised_by_foreign` (`raised_by`),
  ADD KEY `disputes_resolved_by_foreign` (`resolved_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_otp_tokens`
--
ALTER TABLE `password_otp_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_otp_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_services_user_id_service_id_unique` (`user_id`,`service_id`),
  ADD KEY `provider_services_service_id_foreign` (`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`),
  ADD KEY `services_category_id_foreign` (`category_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_categories_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `worker_documents`
--
ALTER TABLE `worker_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `worker_documents_user_id_foreign` (`user_id`),
  ADD KEY `worker_documents_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `worker_profiles`
--
ALTER TABLE `worker_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `worker_profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `work_portfolios`
--
ALTER TABLE `work_portfolios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_portfolios_worker_profile_id_foreign` (`worker_profile_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_otp_tokens`
--
ALTER TABLE `password_otp_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `provider_services`
--
ALTER TABLE `provider_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `worker_documents`
--
ALTER TABLE `worker_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `worker_profiles`
--
ALTER TABLE `worker_profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `work_portfolios`
--
ALTER TABLE `work_portfolios`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_worker_id_foreign` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_raised_by_foreign` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disputes_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_otp_tokens`
--
ALTER TABLE `password_otp_tokens`
  ADD CONSTRAINT `password_otp_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD CONSTRAINT `provider_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_services_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_documents`
--
ALTER TABLE `worker_documents`
  ADD CONSTRAINT `worker_documents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `worker_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_profiles`
--
ALTER TABLE `worker_profiles`
  ADD CONSTRAINT `worker_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_portfolios`
--
ALTER TABLE `work_portfolios`
  ADD CONSTRAINT `work_portfolios_worker_profile_id_foreign` FOREIGN KEY (`worker_profile_id`) REFERENCES `worker_profiles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
