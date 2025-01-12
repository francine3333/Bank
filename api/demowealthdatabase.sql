-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2025 at 03:19 PM
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
-- Database: `demowealthdatabase`
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
('arvin', 'My savings', '816', 1),
('francine', 'My savings', '0', 2),
('arvin', 'My savings', '289', 4),
('cc', 'My savings', '0', 12),
('bob', 'My savings', '5000', 13),
('francine', 'Debit Card', '5', 14);

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
(1, 'arvin', 'Car Loan', 500.00, 'Pending', '2024-07-22 17:34:57');

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
(1, 'arvin', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36', 'Unknown', '2024-07-15 12:12:04'),
(2, 'arvin', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36', 'Unknown', '2024-07-15 12:12:08'),
(3, 'arvin', '136.158.59.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-15 15:29:08'),
(4, 'arvin', '136.158.59.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-15 15:29:11'),
(5, 'arvin', '136.158.59.42', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-15 16:23:39'),
(6, 'arvin', '136.158.59.42', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-15 16:23:43'),
(7, 'arvin', '136.158.59.42', 'Desktop Device: ', 'Manila, Metro Manila, PH', '2024-07-15 17:27:14'),
(8, 'arvin', '136.158.59.42', 'Desktop Device: ', 'Manila, Metro Manila, PH', '2024-07-15 17:27:17'),
(9, 'arvin', '136.158.59.42', 'Desktop Device: ', 'Manila, Metro Manila, PH', '2024-07-15 17:37:39'),
(10, 'arvin', '136.158.59.42', 'Desktop Device: ', 'Manila, Metro Manila, PH', '2024-07-15 17:37:42'),
(11, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-16 09:03:36'),
(12, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-16 09:03:39'),
(13, 'arvin', '136.158.59.42', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-17 09:03:45'),
(14, 'arvin', '136.158.59.42', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Manila, Metro Manila, PH', '2024-07-17 09:03:48'),
(15, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 15:46:35'),
(16, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 15:46:38'),
(17, 'cc', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 16:10:43'),
(18, 'cc', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 16:10:46'),
(19, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 16:39:58'),
(20, 'bob', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-17 16:40:01'),
(21, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 13:25:03'),
(22, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 13:25:07'),
(23, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 14:03:59'),
(24, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 14:04:03'),
(25, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 17:34:38'),
(26, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 17:34:43'),
(27, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 23:08:53'),
(28, 'arvin', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Unknown', '2024-07-18 23:08:56'),
(29, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 16:19:29'),
(30, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 16:23:54'),
(31, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:01:45'),
(32, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:15:30'),
(33, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:25:55'),
(34, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:26:02'),
(35, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:26:50'),
(36, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:26:55'),
(37, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:35:30'),
(38, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:35:54'),
(39, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:37:43'),
(40, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:37:45'),
(41, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:38:38'),
(42, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-03 17:38:45'),
(43, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-07 02:29:33'),
(44, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-07 02:35:20'),
(45, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-07 02:43:32'),
(46, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-07 02:43:33'),
(47, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0', 'Unknown', '2024-09-07 02:46:44'),
(48, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 20:31:25'),
(49, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 20:31:26'),
(50, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 21:22:18'),
(51, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 21:35:33'),
(52, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 21:38:41'),
(53, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-05 21:40:32'),
(54, 'francine', '::1', 'Desktop Device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0', 'Unknown', '2025-01-10 13:08:48');

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
(1, 'arvin', 'New device login detected.', '2024-07-22 18:38:34'),
(2, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:43:30'),
(3, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:44:07'),
(4, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:44:18'),
(5, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:44:22'),
(6, 'arvin', 'Your balance is below 500.', '2024-07-22 18:45:05'),
(7, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:45:05'),
(8, 'arvin', 'Your balance is below 500.', '2024-07-22 18:45:58'),
(9, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:45:58'),
(10, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:47:41'),
(11, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:48:49'),
(12, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:48:52'),
(13, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:48:53'),
(14, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:48:54'),
(15, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:54:28'),
(16, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:55:29'),
(17, 'arvin', 'Your balance is below 500.', '2024-07-22 18:55:38'),
(18, 'arvin', 'Your balance is below 1000.', '2024-07-22 18:55:38'),
(19, 'francine', 'Low Balance Alert Your balance for savings id number \'2\' with savings type \'My savings\' is below 500.', '2024-09-06 18:52:58'),
(20, 'francine', 'Low Balance Alert Your balance for savings id number \'2\' with savings type \'My savings\' is below 500.', '2025-01-10 05:09:17'),
(21, 'francine', 'Low Balance Alert Your balance for savings id number \'14\' with savings type \'Debit Card\' is below 500.', '2025-01-10 05:09:17');

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
('2024-07-17', '03:32:44pm', 'Deleted all logs', 'Database Management', 'arvin', 'arvin'),
('2024-09-07', '02:38:34am', 'Delete', 'Accounts Management', 'navarez', 'francine');

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
('arvin', '5000', 'Deposit', 'My savings', 4, '2024-07-11 10:51:23'),
('arvin', '5000', 'Deposit', 'My savings', 1, '2024-07-11 10:51:49'),
('arvin', '350', 'Withdraw', 'My savings', 1, '2024-07-11 10:53:19'),
('francine', '50', 'Deposit', 'My savings', 2, '2024-07-11 10:55:46'),
('arvin', '621', 'Withdraw', 'My savings', 4, '2024-07-11 10:56:51'),
('arvin', '25001', 'Withdraw', 'My savings', 4, '2024-07-11 12:35:25'),
('arvin', '1', 'Withdraw', 'My savings', 1, '2024-07-11 18:19:35'),
('bob', '5000', 'Deposit', 'My savings', 13, '2024-07-17 16:40:39'),
('arvin', '5000', 'Deposit', 'My savings', 4, '2024-07-11 10:51:23'),
('arvin', '5000', 'Deposit', 'My savings', 1, '2024-07-11 10:51:49'),
('arvin', '350', 'Withdraw', 'My savings', 1, '2024-07-11 10:53:19'),
('francine', '50', 'Deposit', 'My savings', 2, '2024-07-11 10:55:46'),
('arvin', '621', 'Withdraw', 'My savings', 4, '2024-07-11 10:56:51'),
('arvin', '25001', 'Withdraw', 'My savings', 4, '2024-07-11 12:35:25'),
('arvin', '1', 'Withdraw', 'My savings', 1, '2024-07-11 18:19:35'),
('bob', '5000', 'Deposit', 'My savings', 13, '2024-07-17 16:40:39'),
('arvin', '400', 'Withdraw', 'My savings', 1, '2024-07-25 01:03:41'),
('francine', '1400', 'Withdraw', 'My savings', 2, '2024-09-07 02:52:58');

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
('arvin', 'navarez', 'My savings', '1', 4, '2024-07-12 22:49:14'),
('arvin', 'navarez', 'My savings', '4', 5, '2024-07-12 22:49:31');

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
('arvin', 'cutiegboy@gmail.com', '123', 'ADMINISTRATOR', 'admin', '2024-07-09', 'active'),
('bob', 'ndestroyer50@gmail.com', '123', 'USERS', 'arvin', '2024-07-17', 'active'),
('cc', '', '123', 'USERS', 'arvin', '2024-07-17', 'closed'),
('francine', 'lastimosarisch@gmail.com', '1234', 'ADMINISTRATOR', 'arvin', '2024-07-09', 'active');

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
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
