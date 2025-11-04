-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 01:29 PM
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
-- Database: `eduwell`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `member_since` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT 'image/user icon.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `password`, `created_at`, `member_since`, `profile_pic`) VALUES
(1, 'Juan carlos manuel', '1111111@gmail.com', 'student', '$2y$10$B1V8VEO5c/1zQyuxu725ZOZkDZjrlx/ndg99oeWlMj7Qmpn8XN8QC', '2025-10-23 04:16:36', '2025-10-23 04:16:36', '1761193939_IMG20250904181706.jpg'),
(2, '122', '1234@gmail.com', 'student', '$2y$10$xeUKBCEg8LKJgb7UtegGfO4dgK0FkPZ76uauMQY9.hhR6s4l4mDAi', '2025-10-23 04:47:47', '2025-10-23 04:47:47', '1761223690_IMG20250904181706.jpg'),
(3, 'Akaza', 'fortnitegamersea@gmail.com', 'student', '$2y$10$5B1zBAEozxb6J6EMKWfbL.JUZ77s.28gR6TQLfR3mOb.WXtLvNCSW', '2025-10-23 08:25:49', '2025-10-23 08:25:49', '1761408152_2x2 JC.jpg'),
(4, '231', 'mjcmanuel@tip.edu.ph', 'student', '$2y$10$a6kaqs87DNRgsSpDeAUTze6M1DuLZJ26hfboU5wGRzRsDK7ROgXve', '2025-10-25 15:54:52', '2025-10-25 15:54:52', 'image/user icon.png'),
(5, 'admin', 'shinociroshi@gmail.com', 'student', '$2y$10$ae.N0omdUaLvb6ubKcswXuwH8Te9r0/BmgSwMG3k84WH6OzXlr87O', '2025-10-29 16:06:34', '2025-10-29 16:06:34', 'image/user icon.png'),
(6, 'Jose Thyron Hilacan', 'mjthilacan@tip.edu.ph', 'student', '$2y$10$CzjJI/qYz5S3vd3CFP/mbOfBtm99sINpr/uPe0G8tEuizGRFcHEze', '2025-10-30 02:51:46', '2025-10-30 02:51:46', 'image/user icon.png');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
