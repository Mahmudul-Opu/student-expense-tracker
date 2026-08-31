-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2026 at 05:58 PM
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
-- Database: `expense_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(6, 'Bills'),
(5, 'Education'),
(7, 'Entertainment'),
(2, 'Food'),
(8, 'Other'),
(1, 'Salary'),
(4, 'Shopping'),
(3, 'Travel');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `amount`, `description`, `transaction_date`, `created_at`) VALUES
(1, 1, 1, 'income', 10000.00, 'Office', '2026-08-12', '2026-08-30 04:23:12'),
(2, 1, 2, 'expense', 500.00, 'Vegetables', '2026-06-10', '2026-08-30 04:24:02'),
(3, 1, 3, 'expense', 300.00, 'Cox\'s Bazar', '2026-08-30', '2026-08-30 04:24:26'),
(4, 2, 5, 'income', 1000.00, '', '2026-08-30', '2026-08-30 04:30:03'),
(5, 3, 7, 'income', 2000.00, '', '2026-08-30', '2026-08-30 04:30:57'),
(6, 3, 6, 'expense', 2000.00, '', '2026-08-30', '2026-08-30 12:20:23'),
(7, 3, 4, 'income', 500.00, '', '2026-08-30', '2026-08-30 12:20:36'),
(8, 3, 8, 'expense', 600.00, '', '2026-08-31', '2026-08-30 12:21:23'),
(9, 1, 3, 'expense', 300.00, 'Bus ticket', '2026-02-03', '2026-08-30 12:29:33'),
(10, 1, 4, 'expense', 3000.00, 'Shirt', '2026-07-15', '2026-08-30 13:19:10'),
(12, 4, 2, 'expense', 500.00, 'Apple', '2026-04-25', '2026-08-30 15:15:57'),
(13, 4, 4, 'income', 300000.00, 'Smartphone(VIVO X300)', '2026-08-04', '2026-08-30 15:16:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'opu', 'opu@gmail.com', '$2y$10$ukePhyLSja8zFwqOdRhC4ekX4IwFtIMjc/wm/4QV/cJT29JWqhFX.', '2026-08-30 02:17:36'),
(2, 'User A', 'a@test.com', '$2y$10$bR5rl7w5nxcgb28rNx569.jbNUxnBl9RF0m274SP5b8KgoyExP19S', '2026-08-30 04:29:39'),
(3, 'User B', 'b@test.com', '$2y$10$hBV4mIAgiRhXsbx.0y2vpefi6dLFrQGIspSpHNbXo.z/nvYtsa0ni', '2026-08-30 04:30:41'),
(4, 'shakib', 'shakib@gmail.com', '$2y$10$J17bcOTPfAuDJWQrFHjssuzAi3/SAvJYNXUOGFIF1kGwQo2XgIUJe', '2026-08-30 15:13:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_user` (`user_id`),
  ADD KEY `fk_transaction_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
