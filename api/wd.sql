-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 12:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wd`
--

-- --------------------------------------------------------

--
-- Table structure for table `accbalance`
--

CREATE TABLE `accbalance` (
  `username` varchar(50) NOT NULL,
  `savingstype` varchar(50) NOT NULL,
  `balance` varchar(50) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `accbalance`
--

INSERT INTO `accbalance` (`username`, `savingstype`, `balance`, `id`) VALUES
('arvin', 'My savings', '809538', 1),
('francine', 'My savings', '1403', 2),
('navarez', 'My savings', '405', 3),
('navarez', 'Money Market', '10000', 5),
('cc', 'My savings', '0', 12),
('bob', 'My savings', '4800', 13),
('francine', 'Checking savings', '1403', 14),
('francine', 'Debit Card', '1403', 15),
('sean', 'My savings', '0', 19),
('angelo', 'Money Market', '0', 20),
('joseph', 'My savings', '0', 21);

-- --------------------------------------------------------

--
-- Table structure for table `foodmenu`
--

CREATE TABLE `foodmenu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `foodmenu`
--

INSERT INTO `foodmenu` (`id`, `name`, `category_id`, `price`, `description`, `image_url`) VALUES
(3, 'baked mac', 'budget meal', 80.00, 'solo meal', 'foodimage/bakedmac.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `foodorders`
--

CREATE TABLE `foodorders` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `food_item_id` int(11) NOT NULL,
  `foodbrand` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `foodorders`
--

INSERT INTO `foodorders` (`id`, `username`, `food_item_id`, `foodbrand`, `price`, `order_date`) VALUES
(1, 'arvin', 3, '', 80.00, '2024-12-15 18:27:45');

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int(11) NOT NULL,
  `package_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `investments`
--

INSERT INTO `investments` (`id`, `package_name`, `description`, `amount`) VALUES
(1, 'Basic Package', 'Basic investment package with moderate returns', 100.00),
(2, 'Standard investment', 'Standard investment package with balanced risk and returns', 200.00),
(3, 'Premium package ', 'Premium investment package with high returns and higher risk', 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `loan_type` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `application_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `username`, `loan_type`, `amount`, `status`, `application_date`) VALUES
(1, 'arvin', 'Car Loan', 500.00, 'Approved', '2024-07-22 17:34:57'),
(2, 'arvin', 'Home Loan', 250000.00, 'Approved', '2024-07-27 16:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device` varchar(255) NOT NULL,
  `location` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `device`, `location`, `timestamp`) VALUES
(51, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-16 21:02:13'),
(52, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-16 21:04:21'),
(53, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-16 21:05:27'),
(54, 'sean', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-16 21:17:07'),
(55, 'sean', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 19:53:13'),
(56, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 19:54:50'),
(57, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 19:56:06'),
(58, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 20:00:39'),
(59, 'angelo', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 20:02:58'),
(60, 'arvin', '192.168.100.21', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 20:38:46'),
(61, 'arvin', '192.168.100.21', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-19 20:38:47'),
(62, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Unknown', '2024-09-23 08:46:31'),
(63, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-09-24 17:06:10'),
(64, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-01 17:56:01'),
(65, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-01 17:56:02'),
(66, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-02 15:11:23'),
(67, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-02 15:12:51'),
(68, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-02 15:12:52'),
(69, 'arvin', '192.168.100.21', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Unknown', '2024-10-11 16:47:32'),
(70, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 'Unknown', '2024-10-26 01:15:01'),
(71, 'sean', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 'Unknown', '2024-10-26 01:18:13'),
(72, 'sean', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 'Unknown', '2024-10-26 01:18:14'),
(73, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-21 12:07:52'),
(74, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-21 12:32:24'),
(75, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-21 14:03:56'),
(76, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-22 13:35:27'),
(77, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-22 14:20:01'),
(78, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Unknown', '2024-11-22 18:58:51'),
(79, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'Unknown', '2024-12-08 14:36:38'),
(80, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'Unknown', '2024-12-11 18:37:57'),
(81, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'Unknown', '2024-12-11 18:44:31'),
(82, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'Unknown', '2024-12-11 18:49:27'),
(83, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'Unknown', '2024-12-11 19:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `username`, `message`, `created_at`) VALUES
(1, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-03 15:27:01'),
(2, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-03 15:27:01'),
(3, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-03 15:27:01'),
(4, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-08 07:02:24'),
(5, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-08 07:02:24'),
(6, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-08 07:02:24'),
(7, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-08 07:24:08'),
(8, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-08 07:24:08'),
(9, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-08 07:24:08'),
(10, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-12 15:23:40'),
(11, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-12 15:23:40'),
(12, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-12 15:23:40'),
(13, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-16 11:53:48'),
(14, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-16 11:53:48'),
(15, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-16 11:53:48'),
(16, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-16 12:57:12'),
(17, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-16 12:57:12'),
(18, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-16 12:57:12'),
(19, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-16 13:05:59'),
(20, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-16 13:05:59'),
(21, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-16 13:05:59'),
(22, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-19 12:00:56'),
(23, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-19 12:00:56'),
(24, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-19 12:00:56'),
(25, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-19 12:39:02'),
(26, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-19 12:39:02'),
(27, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-19 12:39:02'),
(28, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-23 00:46:52'),
(29, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-23 00:46:52'),
(30, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-23 00:46:52'),
(31, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-09-24 09:06:29'),
(32, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-09-24 09:06:29'),
(33, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-09-24 09:06:29'),
(34, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-10-01 09:56:21'),
(35, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-10-01 09:56:21'),
(36, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-10-01 09:56:21'),
(37, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-10-02 07:12:32'),
(38, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-10-02 07:12:32'),
(39, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-10-02 07:12:32'),
(40, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-10-11 08:47:55'),
(41, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-10-11 08:47:55'),
(42, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-10-11 08:47:55'),
(43, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-10-25 17:15:41'),
(44, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-10-25 17:15:41'),
(45, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-10-25 17:15:41'),
(46, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-11-21 04:08:58'),
(47, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-11-21 04:08:58'),
(48, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-11-21 04:08:58'),
(49, 'arvin', 'Low Balance Alert Your balance for savings id number \'1\' with savings type \'My savings\' is below 500.', '2024-11-21 04:32:42'),
(50, 'arvin', 'Low Balance Alert Your balance for savings id number \'4\' with savings type \'My savings\' is below 500.', '2024-11-21 04:32:42'),
(51, 'arvin', 'Low Balance Alert Your balance for savings id number \'16\' with savings type \'Debit Card\' is below 500.', '2024-11-21 04:32:42'),
(52, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-21 05:17:52'),
(53, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-21 05:24:14'),
(54, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-21 05:30:19'),
(55, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-21 05:57:24'),
(56, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-22 05:56:56'),
(57, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-22 06:52:49'),
(58, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-22 10:50:46'),
(59, 'arvin', 'Someone attempt to Login to a new device.', '2024-11-22 10:55:03'),
(60, 'arvin', 'Someone attempt to Login to a new device.', '2024-12-15 11:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `payment_method` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `username`, `product_id`, `full_name`, `contact`, `address`, `payment_method`, `payment_amount`, `order_status`, `order_date`) VALUES
(1, 'arvin', 1, 'Arvin', '09458298358', 'san juan city', 1, 200.00, 'completed', '2024-11-22 08:01:31'),
(2, 'bob', 1, 'jr', '09952108678', 'manila', 13, 200.00, 'completed', '2024-11-22 11:01:08');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `asset` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `portfolio`
--

INSERT INTO `portfolio` (`id`, `username`, `asset`, `amount`) VALUES
(1, 'arvin', '01coin', 200.00),
(2, 'arvin', '0vm', 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `productreviews`
--

CREATE TABLE `productreviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `review` text NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `productreviews`
--

INSERT INTO `productreviews` (`id`, `product_id`, `username`, `review`, `rating`, `created_at`) VALUES
(2, 1, 'arvin', 'good products', 5, '2024-11-22 09:45:39'),
(3, 1, 'bob', 'great', 4, '2024-11-22 10:59:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`, `featured`, `created_at`, `description`) VALUES
(1, 'car ', 'toys', 200.00, 'uploads/download.jpg', 0, '2024-11-22 07:32:46', 'FOR KIDS');

-- --------------------------------------------------------

--
-- Table structure for table `tbllogs`
--

CREATE TABLE `tbllogs` (
  `datelog` varchar(15) NOT NULL,
  `timelog` varchar(15) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `id` varchar(50) NOT NULL,
  `performedby` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllogs`
--

INSERT INTO `tbllogs` (`datelog`, `timelog`, `action`, `module`, `id`, `performedby`) VALUES
('2024-07-17', '03:32:44pm', 'Deleted all logs', 'Database Management', 'arvin', 'arvin'),
('2024-09-16', '09:14:40pm', 'Added new account: sean', 'Accounts Management', 'sean', 'arvin'),
('2024-09-19', '08:01:55pm', 'Added new account: angelo', 'Accounts Management', 'angelo', 'arvin'),
('2024-09-19', '08:51:14pm', 'Added new account: joseph', 'Accounts Management', 'joseph', 'arvin'),
('2024-11-21', '01:58:53pm', 'Delete', 'Accounts Management', 'arvin', 'arvin'),
('2024-11-21', '02:01:09pm', 'Added new account: arvin', 'Accounts Management', 'arvin', 'arvin');

-- --------------------------------------------------------

--
-- Table structure for table `thistory`
--

CREATE TABLE `thistory` (
  `username` varchar(50) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `usersavings` varchar(50) NOT NULL,
  `id` int(50) NOT NULL,
  `transaction_date` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `thistory`
--

INSERT INTO `thistory` (`username`, `amount`, `transaction_type`, `usersavings`, `id`, `transaction_date`) VALUES
('arvin', '1', 'Withdraw', 'Debit Card', 16, '2024-09-03 23:02:04'),
('arvin', '5', 'Withdraw', 'Debit Card', 1, '2024-09-03 23:02:11'),
('arvin', '809978', 'Deposit', 'My savings', 28, '2024-11-21 14:01:28');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `asset` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` enum('buy','sell') NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `username`, `asset`, `amount`, `price`, `type`, `date`) VALUES
(1, 'arvin', 'jollibee', 100.00, 100.00, 'buy', '2024-07-18 15:52:12'),
(2, 'arvin', 'mcdo', 200.00, 200.00, 'buy', '2024-07-18 15:53:37'),
(3, 'arvin', 'jollibee', 100.00, 100.00, 'sell', '2024-07-18 16:01:13'),
(4, 'arvin', 'jollibee', 100.00, 500.00, 'buy', '2024-07-18 16:03:55'),
(5, 'arvin', 'kfc', 200.00, 120.00, 'buy', '2024-07-18 16:06:39'),
(6, 'arvin', '0xdefcafe', 20.00, 200.00, 'buy', '2024-07-18 16:48:50'),
(7, 'arvin', '01coin', 10.00, 10.00, 'buy', '2024-07-18 16:49:50'),
(8, 'arvin', '01coin', 10.00, 10.00, 'buy', '2024-07-18 16:50:21'),
(9, 'arvin', '01coin', 20.00, 20.00, 'buy', '2024-07-18 17:49:29'),
(10, 'arvin', '01coin', 150.00, 200.00, 'buy', '2024-07-18 17:50:34'),
(11, 'arvin', '01coin', 100.00, 201.00, 'buy', '2024-07-18 17:57:45'),
(12, 'arvin', '0xbet', 20.00, 12.00, 'buy', '2024-07-18 17:58:10'),
(13, 'arvin', '01coin', 2.00, 2.00, 'buy', '2024-07-18 18:14:59'),
(14, 'arvin', '01coin', 20.00, 2.00, 'buy', '2024-07-18 18:31:19'),
(15, 'arvin', '01coin', 20.00, 10.00, 'buy', '2024-07-18 18:32:09'),
(16, 'arvin', '0-knowledge-network', 200.00, 220.00, 'buy', '2024-07-18 18:44:25'),
(17, 'arvin', '01coin', 200.00, 2.00, 'buy', '2024-07-18 18:45:55'),
(18, 'arvin', '0vm', 20.00, 20.00, 'buy', '2024-07-18 18:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `transferhistory`
--

CREATE TABLE `transferhistory` (
  `fromaccount` varchar(50) NOT NULL,
  `toaccount` varchar(50) NOT NULL,
  `usersavings` varchar(50) NOT NULL,
  `id` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `transfer_date` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transferhistory`
--

INSERT INTO `transferhistory` (`fromaccount`, `toaccount`, `usersavings`, `id`, `amount`, `transfer_date`) VALUES
('arvin', 'navarez', 'My savings', '1', 4, '2024-07-12 22:49:14'),
('arvin', 'navarez', 'My savings', '4', 5, '2024-07-12 22:49:31'),
('arvin', 'francine', 'Debit Card', '1', 1, '2024-09-03 22:31:55'),
('arvin', 'francine', 'Debit Card', '16', 1, '2024-09-03 22:56:15'),
('arvin', 'francine', 'Debit Card', '16', 1, '2024-09-03 22:56:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `account_status` enum('active','closed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `usertype`, `createdby`, `datecreated`, `account_status`) VALUES
('angelo', 'angelopring5366@gmail.com', '1234', 'USERS', 'arvin', '2024-09-19', 'active'),
('arvin', 'cutiegboy@gmail.com', '123', 'ADMINISTRATOR', 'arvin', '2024-11-21', 'active'),
('bob', 'ndestroyer50@gmail.com', '123', 'USERS', 'arvin', '2024-07-17', 'active'),
('cc', '', '123', 'USERS', 'arvin', '2024-07-17', 'closed'),
('francine', '', '1234', 'ADMINISTRATOR', 'arvin', '2024-07-09', 'active'),
('joseph', 'josephyvesemmanuel@gmail.com', '1234', 'USERS', 'arvin', '2024-09-19', 'closed'),
('navarez', '', '1234', 'ADMINISTRATOR', 'arvin', '2024-07-09', 'active'),
('sean', 'seanrobert.canta@gmail.com', '1234', 'USERS', 'arvin', '2024-09-16', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_investments`
--

CREATE TABLE `user_investments` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_investments`
--

INSERT INTO `user_investments` (`id`, `username`, `investment_id`, `amount`, `date_time`) VALUES
(1, 'arvin', 1, 100.00, '2024-07-12 17:33:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accbalance`
--
ALTER TABLE `accbalance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foodmenu`
--
ALTER TABLE `foodmenu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foodorders`
--
ALTER TABLE `foodorders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_item_id` (`food_item_id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `payment_method` (`payment_method`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productreviews`
--
ALTER TABLE `productreviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`,`email`);

--
-- Indexes for table `user_investments`
--
ALTER TABLE `user_investments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accbalance`
--
ALTER TABLE `accbalance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `foodmenu`
--
ALTER TABLE `foodmenu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `foodorders`
--
ALTER TABLE `foodorders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `productreviews`
--
ALTER TABLE `productreviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_investments`
--
ALTER TABLE `user_investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `foodorders`
--
ALTER TABLE `foodorders`
  ADD CONSTRAINT `foodorders_ibfk_1` FOREIGN KEY (`food_item_id`) REFERENCES `foodmenu` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_method`) REFERENCES `accbalance` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `productreviews`
--
ALTER TABLE `productreviews`
  ADD CONSTRAINT `productreviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `productreviews_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
