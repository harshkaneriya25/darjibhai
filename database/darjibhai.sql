-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 01:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `darjibhai`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `actual_password` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `role_id`, `name`, `email`, `phone`, `password`, `actual_password`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'hrs patel', 'admin@gmail.com', '7896541234', '$2y$10$2Ugf2u/g4ri.S/HRAlQ3J.jumUPLcQ37iKbRO3MC0UBYicf4YFkna', '123456789', 1, '2026-09-18 13:23:52', '2026-09-28 11:46:00'),
(2, 1, 'om joshi', 'omjoshi@gmail.com', '1236547890', '$2y$10$DLlqvByx74f2tpDfZhlmxeNDcxrQ2ymph.t1fDB/r77FoEoNRn44q', '123456789', 1, '2026-09-22 12:48:22', '2026-09-22 12:48:22'),
(3, 1, 'dhrumil', 'dhuli@gmail.com', '7896541230', '$2y$10$oG1FTD/3Imtls7cUl0G0/e.TEV4naAvU5gqDngdN5bF4FhzDr1aPu', '123456789', 1, '2026-09-23 10:50:16', '2026-09-23 10:50:16'),
(5, 2, 'yash', 'master@gmail.com', '7854123690', '$2y$10$gJzCMi2EefUPU1MjF3TcgOGLLhauyTEO0xrNQ7qD/UmV0xryCaPVi', '123456789', 1, '2026-09-23 12:49:36', '2026-09-28 11:46:28'),
(8, 4, 'karan', 'partner@gmail.com', '7893214560', '$2y$10$lzcr29DNB9in1md26PMV5u5h4iiZMFPNvKHk4ZN5PkvLjIr3TDTIu', '123456789', 1, '2026-09-24 09:38:06', '2026-09-28 11:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `completed_orders`
--

CREATE TABLE `completed_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `master_id` int(10) UNSIGNED DEFAULT NULL,
  `delivery_partner_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `job_description_name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `demo_image` varchar(255) DEFAULT NULL,
  `body_measurements` longtext DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `completed_orders`
--

INSERT INTO `completed_orders` (`id`, `order_id`, `order_no`, `user_id`, `master_id`, `delivery_partner_id`, `product_name`, `job_description_name`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `demo_image`, `body_measurements`, `delivery_date`, `created_at`, `completed_at`) VALUES
(1, 3, 'ORD-0002', 1, 7, 8, 'shuit', 'tackin button', 'Harsh kaneriya', '1234567890', 'harsh@gmail.com', 'rajkot', NULL, '{\"upper_body\":{\"neck\":\"\",\"shoulder\":\"\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"25\",\"bottom_hem\":\"\"}}', '2026-09-24', '2026-09-24 18:58:38', '2026-09-25 14:59:24'),
(2, 4, 'ORD-0003', 3, 7, 8, 'Pajama', 'Back Adjustment', 'yash', '7854126930', 'yash@gmail.com', 'mavdi , rajkot', NULL, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"25\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-25', '2026-09-25 16:39:52', '2026-09-25 17:11:25'),
(3, 5, 'ORD-0004', 4, 7, 8, 'shuit', 'Buttonhole Making', 'om joshi', '7968451230', 'omjoshi@gmail.com', 'kalawad road , rajkot', NULL, '{\"upper_body\":{\"neck\":\"24\",\"shoulder\":\"24\",\"chest\":\"24\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"24\",\"outseam_length\":\"24\",\"bottom_hem\":\"24\"}}', '2026-09-25', '2026-09-25 17:35:12', '2026-09-25 17:36:48'),
(4, 6, 'ORD-0005', 1, 5, 8, 'Blazer', 'Alteration', 'Harsh kaneriya', '1234567890', 'harsh@gmail.com', 'rajkot', NULL, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"25\",\"torso_length\":\"25\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-28', '2026-09-28 18:05:52', '2026-09-28 18:12:24'),
(5, 7, 'ORD-0006', 1, 5, 8, 'shuit', 'Chest Adjustment', 'Harsh kaneriya', '1234567890', 'harsh@gmail.com', 'rajkot', NULL, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"25\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-28', '2026-09-28 18:24:25', '2026-09-28 18:26:34'),
(6, 8, 'ORD-0007', 1, 5, 8, 'Pajama', 'Design Modification', 'Harsh kaneriya', '1234567890', 'harsh@gmail.com', 'rajkot', NULL, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"24.6\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-29', '2026-09-29 15:26:10', '2026-09-29 15:28:05'),
(7, 9, 'ORD-0008', 1, 5, 8, 'Dress', 'Back Adjustment', 'Harsh kaneriya', '1234567890', 'harsh@gmail.com', 'rajkot', '1790677084_shirt.webp', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"25\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-30', '2026-09-29 15:48:04', '2026-09-30 16:46:09'),
(8, 10, 'ORD-0009', 5, 5, 8, 'Shirt', 'Chest Adjustment', 'patel', '9876543210', 'patel@gmail.com', 'rajkot', '1790767026_shirt.jpg', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"25\",\"outseam_length\":\"25\",\"bottom_hem\":\"\"}}', '2026-09-30', '2026-09-30 16:47:06', '2026-09-30 16:49:35'),
(9, 11, 'ORD-0010', 5, 5, 8, 'Jacket', 'Cuff Adjustment', 'patel', '9876543210', 'patel@gmail.com', 'rajkot', NULL, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"24\",\"armhole\":\"24\",\"bicep\":\"24\"},\"middle_body\":{\"waist\":\"24\",\"stomach\":\"24\",\"torso_length\":\"23.7\"},\"lower_body\":{\"hip\":\"24\",\"thigh\":\"24\",\"knee\":\"24\",\"inseam\":\"24\",\"outseam_length\":\"24\",\"bottom_hem\":\"24\"}}', '2026-09-30', '2026-09-30 16:53:13', '2026-09-30 16:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `job_descriptions`
--

CREATE TABLE `job_descriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_descriptions`
--

INSERT INTO `job_descriptions` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Custom Stitching', 'Complete custom stitching according to customer measurements and requirements', 1, '2026-09-25 10:06:06', '2026-09-25 12:50:32'),
(2, 'Alteration', 'General alteration of garments to improve fitting and comfort', 1, '2026-09-25 10:06:06', '2026-09-25 12:50:39'),
(3, 'Size Adjustment', 'Adjust garment size according to customer measurements', 1, '2026-09-25 10:06:06', '2026-09-25 12:51:05'),
(4, 'Length Adjustment', 'Increase or decrease the length of the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:09'),
(5, 'Waist Adjustment', 'Adjust the waist size for a better fit', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:13'),
(6, 'Sleeve Adjustment', 'Increase or decrease sleeve length or width', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:17'),
(7, 'Shoulder Adjustment', 'Adjust shoulder width and fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:21'),
(8, 'Collar Adjustment', 'Modify collar size and fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:25'),
(9, 'Neck Design', 'Create or modify the neckline according to the requested design', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:29'),
(10, 'Sleeve Design', 'Create or modify sleeve style and design', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:32'),
(11, 'Pocket Addition', 'Add new pockets to the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:45:38'),
(12, 'Pocket Removal', 'Remove existing pockets from the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:18'),
(13, 'Button Replacement', 'Replace damaged or missing buttons', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:24'),
(14, 'Buttonhole Making', 'Create new buttonholes according to garment requirements', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:29'),
(15, 'Zip Replacement', 'Replace damaged or broken zippers', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:35'),
(16, 'Zip Installation', 'Install a new zipper on the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:41'),
(17, 'Cuff Making', 'Create new cuffs for shirts and other garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:48'),
(18, 'Cuff Adjustment', 'Adjust cuff size and fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:53'),
(19, 'Hemming', 'Finish or shorten the bottom edge of the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:46:59'),
(20, 'Tapering', 'Reduce garment width for a slimmer fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:02'),
(21, 'Waistband Replacement', 'Replace damaged or worn waistbands', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:59'),
(22, 'Lining Replacement', 'Replace old or damaged garment lining', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:54'),
(23, 'Lining Installation', 'Add lining to the inside of the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:49'),
(24, 'Shoulder Padding', 'Add or replace shoulder padding for better structure', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:44'),
(25, 'Side Seam Adjustment', 'Adjust the side seams to improve garment fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:37'),
(26, 'Bottom Width Adjustment', 'Increase or decrease the width of the garment bottom', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:31'),
(27, 'Chest Adjustment', 'Adjust chest area for improved fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:27'),
(28, 'Back Adjustment', 'Modify the back fitting of the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:22'),
(29, 'Stitch Repair', 'Repair loose, broken, or damaged stitching', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:17'),
(30, 'Seam Repair', 'Repair damaged or opened garment seams', 1, '2026-09-25 10:06:06', '2026-09-28 12:47:11'),
(31, 'Tear Repair', 'Repair torn or damaged fabric areas', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:06'),
(32, 'Patch Work', 'Apply fabric patches to cover or repair damaged areas', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:11'),
(33, 'Embroidery', 'Add decorative embroidery designs to garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:16'),
(34, 'Decorative Stitching', 'Add decorative stitching patterns to garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:22'),
(35, 'Applique Work', 'Attach decorative fabric designs to the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:27'),
(36, 'Lace Attachment', 'Attach lace or decorative trim to the garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:31'),
(37, 'Border Attachment', 'Add decorative borders to garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:37'),
(38, 'Bead Work', 'Add beads and decorative elements to garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:42'),
(39, 'Stone Work', 'Add decorative stones to garments', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:47'),
(40, 'Hook and Eye Installation', 'Install hooks and eyes for garment fastening', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:54'),
(41, 'Snap Button Installation', 'Install snap buttons for garment fastening', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:54'),
(42, 'Elastic Replacement', 'Replace damaged or stretched elastic', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:49'),
(43, 'Elastic Waist Adjustment', 'Adjust or replace elastic waistbands', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:42'),
(44, 'Drawstring Installation', 'Install drawstrings for adjustable garment fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:36'),
(45, 'Collar Replacement', 'Replace damaged or worn collars', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:31'),
(46, 'Cuff Replacement', 'Replace damaged or worn cuffs', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:22'),
(47, 'Pocket Repair', 'Repair damaged or loose pockets', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:16'),
(48, 'Pocket Replacement', 'Replace damaged pockets with new pockets', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:10'),
(49, 'Shoulder Repair', 'Repair damaged shoulder stitching or fabric', 1, '2026-09-25 10:06:06', '2026-09-28 12:49:05'),
(50, 'Sleeve Replacement', 'Replace damaged sleeves with new sleeves', 1, '2026-09-25 10:06:06', '2026-09-28 12:48:59'),
(51, 'Neckline Adjustment', 'Modify neckline size and shape for better fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:00'),
(52, 'Front Opening Modification', 'Modify or create the front opening of a garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:05'),
(53, 'Back Opening Modification', 'Modify or create the back opening of a garment', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:15'),
(54, 'Design Modification', 'Modify the existing garment design according to customer requirements', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:23'),
(55, 'Pattern Modification', 'Modify the garment pattern for a different style or fitting', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:31'),
(56, 'Custom Design', 'Create a garment based on a customer-provided design', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:37'),
(57, 'Urgent Stitching', 'Complete garment stitching on an urgent or priority basis', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:43'),
(58, 'Express Alteration', 'Complete garment alterations on an express basis', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:48'),
(59, 'Measurement Service', 'Take and record customer measurements for tailoring', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:53'),
(60, 'Final Fitting', 'Perform final fitting and make necessary adjustments before delivery', 1, '2026-09-25 10:06:06', '2026-09-28 12:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `delivery_partner_id` int(10) UNSIGNED DEFAULT NULL,
  `master_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `job_description_id` int(10) UNSIGNED DEFAULT NULL,
  `demo_image` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `body_measurements` text DEFAULT NULL,
  `order_status` enum('created','assigned_to_master','master_accepted','master_declined','master_completed','received_from_master','assigned_to_delivery','delivery_accepted','delivery_declined','out_for_delivery','delivered','completed','cancelled') NOT NULL DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `user_id`, `delivery_partner_id`, `master_id`, `product_id`, `job_description_id`, `demo_image`, `delivery_date`, `body_measurements`, `order_status`, `created_at`, `updated_at`) VALUES
(2, 'ORD-0001', 2, 8, NULL, 3, NULL, NULL, '2026-09-24', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"25\",\"outseam_length\":\"25\",\"bottom_hem\":\"25\"}}', 'delivered', '2026-09-24 12:12:02', '2026-09-24 12:36:47'),
(3, 'ORD-0002', 1, 8, NULL, 5, NULL, NULL, '2026-09-24', '{\"upper_body\":{\"neck\":\"\",\"shoulder\":\"\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"25\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-24 13:28:38', '2026-09-25 09:29:24'),
(4, 'ORD-0003', 3, 8, NULL, 2, 28, NULL, '2026-09-25', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"25\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-25 11:09:52', '2026-09-25 11:41:25'),
(5, 'ORD-0004', 4, 8, NULL, 5, 14, NULL, '2026-09-25', '{\"upper_body\":{\"neck\":\"24\",\"shoulder\":\"24\",\"chest\":\"24\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"24\",\"outseam_length\":\"24\",\"bottom_hem\":\"24\"}}', 'delivered', '2026-09-25 12:05:12', '2026-09-25 12:06:48'),
(6, 'ORD-0005', 1, 8, 5, 6, 2, NULL, '2026-09-28', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"25\",\"torso_length\":\"25\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-28 12:35:52', '2026-09-28 12:42:24'),
(7, 'ORD-0006', 1, 8, 5, 5, 27, NULL, '2026-09-28', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"25\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-28 12:54:25', '2026-09-28 12:56:34'),
(8, 'ORD-0007', 1, 8, 5, 2, 54, NULL, '2026-09-29', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"24.6\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-29 09:56:10', '2026-09-29 09:58:05'),
(9, 'ORD-0008', 1, 8, 5, 15, 28, '1790677084_shirt.webp', '2026-09-30', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"25\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-29 10:18:04', '2026-09-30 11:16:09'),
(10, 'ORD-0009', 5, 8, 5, 1, 27, '1790767026_shirt.jpg', '2026-09-30', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"\",\"thigh\":\"\",\"knee\":\"\",\"inseam\":\"25\",\"outseam_length\":\"25\",\"bottom_hem\":\"\"}}', 'delivered', '2026-09-30 11:17:06', '2026-09-30 11:19:35'),
(11, 'ORD-0010', 5, 8, 5, 14, 18, NULL, '2026-09-30', '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"24\",\"armhole\":\"24\",\"bicep\":\"24\"},\"middle_body\":{\"waist\":\"24\",\"stomach\":\"24\",\"torso_length\":\"23.7\"},\"lower_body\":{\"hip\":\"24\",\"thigh\":\"24\",\"knee\":\"24\",\"inseam\":\"24\",\"outseam_length\":\"24\",\"bottom_hem\":\"24\"}}', 'delivered', '2026-09-30 11:23:13', '2026-09-30 11:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `job_description_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `body_measurements` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `job_description_id`, `quantity`, `body_measurements`, `created_at`, `updated_at`) VALUES
(1, 8, 2, 54, 1, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"25\",\"stomach\":\"24.6\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-29 09:56:10', '2026-09-29 09:56:10'),
(2, 9, 15, 28, 1, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"25\",\"armhole\":\"\",\"bicep\":\"\"},\"middle_body\":{\"waist\":\"\",\"stomach\":\"\",\"torso_length\":\"\"},\"lower_body\":{\"hip\":\"25\",\"thigh\":\"25\",\"knee\":\"\",\"inseam\":\"\",\"outseam_length\":\"\",\"bottom_hem\":\"\"}}', '2026-09-29 10:18:04', '2026-09-29 10:18:04'),
(3, 11, 14, 18, 1, '{\"upper_body\":{\"neck\":\"25\",\"shoulder\":\"25\",\"chest\":\"25\",\"sleeve_length\":\"24\",\"armhole\":\"24\",\"bicep\":\"24\"},\"middle_body\":{\"waist\":\"24\",\"stomach\":\"24\",\"torso_length\":\"23.7\"},\"lower_body\":{\"hip\":\"24\",\"thigh\":\"24\",\"knee\":\"24\",\"inseam\":\"24\",\"outseam_length\":\"24\",\"bottom_hem\":\"24\"}}', '2026-09-30 11:23:13', '2026-09-30 11:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `description`, `created_at`) VALUES
(2, 2, 'created', 'Admin manually created the order.', '2026-09-24 12:12:02'),
(3, 2, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-24 12:27:18'),
(4, 2, 'master_accepted', 'Master tailor accepted the task.', '2026-09-24 12:27:32'),
(5, 2, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-24 12:27:36'),
(6, 2, 'received_from_master', 'Admin received complete product from Master.', '2026-09-24 12:27:41'),
(7, 2, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-24 12:36:32'),
(8, 2, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-24 12:36:42'),
(9, 2, 'delivered', 'Order successfully delivered to customer.', '2026-09-24 12:36:47'),
(17, 3, 'created', 'Order placed by customer.', '2026-09-24 13:28:38'),
(18, 3, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-25 09:02:20'),
(19, 3, 'master_accepted', 'Master tailor accepted the task.', '2026-09-25 09:03:16'),
(20, 3, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-25 09:03:34'),
(21, 3, 'received_from_master', 'Admin received complete product from Master.', '2026-09-25 09:03:47'),
(22, 3, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-25 09:04:03'),
(23, 3, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-25 09:04:31'),
(24, 3, 'delivered', 'Order successfully delivered to customer.', '2026-09-25 09:29:24'),
(25, 4, 'created', 'Admin manually created the order.', '2026-09-25 11:09:52'),
(26, 4, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-25 11:20:17'),
(27, 4, 'master_accepted', 'Master tailor accepted the task.', '2026-09-25 11:21:37'),
(28, 4, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-25 11:21:42'),
(29, 4, 'received_from_master', 'Admin received complete product from Master.', '2026-09-25 11:32:00'),
(30, 4, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-25 11:40:08'),
(31, 4, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-25 11:40:35'),
(32, 4, 'delivered', 'Order successfully delivered to customer.', '2026-09-25 11:41:25'),
(33, 5, 'created', 'Admin manually created the order.', '2026-09-25 12:05:12'),
(34, 5, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-25 12:05:37'),
(35, 5, 'master_accepted', 'Master tailor accepted the task.', '2026-09-25 12:06:08'),
(36, 5, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-25 12:06:10'),
(37, 5, 'received_from_master', 'Admin received complete product from Master.', '2026-09-25 12:06:17'),
(38, 5, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-25 12:06:24'),
(39, 5, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-25 12:06:43'),
(40, 5, 'delivered', 'Order successfully delivered to customer.', '2026-09-25 12:06:48'),
(41, 6, 'created', 'Order placed by customer.', '2026-09-28 12:35:52'),
(42, 6, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-28 12:36:52'),
(43, 6, 'master_accepted', 'Master tailor accepted the task.', '2026-09-28 12:37:11'),
(44, 6, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-28 12:37:17'),
(45, 6, 'received_from_master', 'Admin received complete product from Master.', '2026-09-28 12:37:37'),
(46, 6, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-28 12:42:05'),
(47, 6, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-28 12:42:22'),
(48, 6, 'delivered', 'Order successfully delivered to customer.', '2026-09-28 12:42:24'),
(49, 7, 'created', 'Order placed by customer.', '2026-09-28 12:54:25'),
(50, 7, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-28 12:55:20'),
(51, 7, 'master_accepted', 'Master tailor accepted the task.', '2026-09-28 12:55:41'),
(52, 7, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-28 12:55:45'),
(53, 7, 'received_from_master', 'Admin received complete product from Master.', '2026-09-28 12:56:02'),
(54, 7, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-28 12:56:10'),
(55, 7, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-28 12:56:32'),
(56, 7, 'delivered', 'Order successfully delivered to customer.', '2026-09-28 12:56:34'),
(57, 8, 'created', 'Order placed by customer.', '2026-09-29 09:56:10'),
(58, 8, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-29 09:56:58'),
(59, 8, 'master_accepted', 'Master tailor accepted the task.', '2026-09-29 09:57:14'),
(60, 8, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-29 09:57:17'),
(61, 8, 'received_from_master', 'Admin received complete product from Master.', '2026-09-29 09:57:34'),
(62, 8, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-29 09:57:42'),
(63, 8, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-29 09:58:03'),
(64, 8, 'delivered', 'Order successfully delivered to customer.', '2026-09-29 09:58:05'),
(65, 9, 'created', 'Order placed by customer.', '2026-09-29 10:18:04'),
(66, 9, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-30 11:12:51'),
(67, 9, 'master_accepted', 'Master tailor accepted the task.', '2026-09-30 11:13:05'),
(68, 9, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-30 11:13:07'),
(69, 9, 'received_from_master', 'Admin received complete product from Master.', '2026-09-30 11:13:38'),
(70, 9, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-30 11:13:44'),
(71, 9, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-30 11:15:56'),
(72, 9, 'delivered', 'Order successfully delivered to customer.', '2026-09-30 11:16:09'),
(73, 10, 'created', 'Admin manually created the order.', '2026-09-30 11:17:06'),
(74, 10, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-30 11:17:12'),
(75, 10, 'master_accepted', 'Master tailor accepted the task.', '2026-09-30 11:18:37'),
(76, 10, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-30 11:18:41'),
(77, 10, 'received_from_master', 'Admin received complete product from Master.', '2026-09-30 11:19:08'),
(78, 10, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-30 11:19:12'),
(79, 10, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-30 11:19:29'),
(80, 10, 'delivered', 'Order successfully delivered to customer.', '2026-09-30 11:19:35'),
(81, 11, 'created', 'Order placed by customer.', '2026-09-30 11:23:13'),
(82, 11, 'assigned_to_master', 'Admin assigned order to Master.', '2026-09-30 11:23:29'),
(83, 11, 'master_accepted', 'Master tailor accepted the task.', '2026-09-30 11:23:47'),
(84, 11, 'master_completed', 'Master tailor marked the product as completed.', '2026-09-30 11:23:52'),
(85, 11, 'received_from_master', 'Admin received complete product from Master.', '2026-09-30 11:24:07'),
(86, 11, 'assigned_to_delivery', 'Admin assigned order to Delivery Partner.', '2026-09-30 11:24:15'),
(87, 11, 'delivery_accepted', 'Delivery partner accepted the order.', '2026-09-30 11:24:35'),
(88, 11, 'delivered', 'Order successfully delivered to customer.', '2026-09-30 11:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Shirt', 'Custom tailored shirt stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:16'),
(2, 'Pajama', 'Custom tailored pajama stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:18'),
(3, 'kutra', '', 1, '2026-09-23 12:54:47', '2026-09-23 12:54:47'),
(4, 'pant', '', 1, '2026-09-24 13:27:38', '2026-09-24 13:27:38'),
(5, 'shuit', '', 1, '2026-09-24 13:27:47', '2026-09-24 13:27:47'),
(6, 'Blazer', 'Custom tailored blazer stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:21'),
(7, 'Waistcoat', 'Custom tailored waistcoat stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:25'),
(8, 'Sherwani', 'Custom tailored sherwani stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:28'),
(9, 'Kurti', 'Custom tailored kurti stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:32'),
(10, 'Blouse', 'Custom tailored blouse stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:36'),
(11, 'Salwar', 'Custom tailored salwar stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:39'),
(12, 'Lehenga', 'Custom tailored lehenga stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:43'),
(13, 'Skirt', 'Custom tailored skirt stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:46'),
(14, 'Jacket', 'Custom tailored jacket stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:59'),
(15, 'Dress', 'Custom tailored dress stitching', 1, '2026-09-25 10:02:12', '2026-09-25 13:03:51');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Super Admin', '2026-09-18 12:55:24'),
(2, 'Master', '2026-09-18 12:55:24'),
(4, 'Delivery Partner', '2026-09-23 12:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `actual_password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `actual_password`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Harsh kaneriya', 'harsh@gmail.com', '1234567890', '$2y$10$JG0xy6pedXVGcoqSmdWPie.ca9uivtxQ8.rteJOok5M7BSIKeKCSC', '123456789', 'rajkot', 1, '2026-09-18 13:00:18', '2026-09-18 13:00:18'),
(2, 'prem', 'prem@gmail.com', '7854123690', '$2y$10$ywVDTlY3u.HgZjPKywdXnO1gOAvEkfD3P2DsabwQjmq4VS.P6vGmi', '123456789', 'Gondal Road , Rajkot', 1, '2026-09-19 12:43:03', '2026-09-19 12:43:03'),
(3, 'yash', 'yash@gmail.com', '7854126930', '$2y$10$frA83nwyJeX6xUILG7wBz.VfreiGxINYShZI89BlP9El0puCjADMO', '123456789', 'mavdi , rajkot', 1, '2026-09-25 09:58:15', '2026-09-25 09:58:15'),
(4, 'om joshi', 'omjoshi@gmail.com', '7968451230', '$2y$10$TP9x4f12u.9NvH0Q4m9ofu8Q8kvsDvs6D2hGGcaR7flGpytUh1Cgq', '', 'kalawad road , rajkot', 1, '2026-09-25 12:05:12', '2026-09-25 12:05:12'),
(5, 'patel', 'patel@gmail.com', '9876543210', '$2y$10$v1AucG6KrxEUiO5yom.FM.GBfEzrcz1VmFvgY2EL.KxIUrOOQCq4u', '123456789', 'rajkot', 1, '2026-09-28 04:22:58', '2026-09-28 04:22:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_admin_role` (`role_id`);

--
-- Indexes for table `completed_orders`
--
ALTER TABLE `completed_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_no` (`order_no`),
  ADD KEY `completed_at` (`completed_at`);

--
-- Indexes for table `job_descriptions`
--
ALTER TABLE `job_descriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_product` (`product_id`),
  ADD KEY `fk_order_job` (`job_description_id`),
  ADD KEY `idx_orders_delivery_partner` (`delivery_partner_id`),
  ADD KEY `fk_orders_master` (`master_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_item_order` (`order_id`),
  ADD KEY `fk_order_item_product` (`product_id`),
  ADD KEY `fk_order_item_job` (`job_description_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_history_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `completed_orders`
--
ALTER TABLE `completed_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `job_descriptions`
--
ALTER TABLE `job_descriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_job` FOREIGN KEY (`job_description_id`) REFERENCES `job_descriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_delivery_partner` FOREIGN KEY (`delivery_partner_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_master` FOREIGN KEY (`master_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_job` FOREIGN KEY (`job_description_id`) REFERENCES `job_descriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_order_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
