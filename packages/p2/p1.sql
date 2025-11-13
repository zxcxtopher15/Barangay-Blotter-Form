-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 29, 2025 at 12:01 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `p1`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` enum('Admin','Desk Officer') NOT NULL DEFAULT 'Desk Officer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `email`, `role`) VALUES
(1, 'brgysanmiguelpasigblotter@gmail.com', 'Admin'),
(17, 'jeomaricp@gmail.com', 'Desk Officer'),
(24, 'anoosjoshua4@gmail.com', 'Desk Officer');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `case_no` int(11) NOT NULL,
  `incident_datetime` datetime NOT NULL,
  `complaint_description` varchar(255) NOT NULL,
  `complainant_first_name` varchar(100) DEFAULT NULL,
  `complainant_middle_name` varchar(100) DEFAULT NULL,
  `complainant_last_name` varchar(100) DEFAULT NULL,
  `complainant_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `complainant_gender` enum('Male','Female') DEFAULT NULL,
  `complainant_phone` char(13) DEFAULT NULL,
  `complainant_address` text DEFAULT NULL,
  `incident_location` text NOT NULL,
  `victim_first_name` varchar(100) NOT NULL,
  `victim_middle_name` varchar(100) DEFAULT NULL,
  `victim_last_name` varchar(100) NOT NULL,
  `victim_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `victim_gender` enum('Male','Female') DEFAULT NULL,
  `victim_phone` char(13) DEFAULT NULL,
  `victim_address` text NOT NULL,
  `witness_first_name` varchar(100) DEFAULT NULL,
  `witness_middle_name` varchar(100) DEFAULT NULL,
  `witness_last_name` varchar(100) DEFAULT NULL,
  `witness_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `witness_gender` enum('Male','Female') DEFAULT NULL,
  `witness_phone` char(13) DEFAULT NULL,
  `witness_address` text DEFAULT NULL,
  `respondent_first_name` varchar(100) DEFAULT NULL,
  `respondent_middle_name` varchar(100) DEFAULT NULL,
  `respondent_last_name` varchar(100) DEFAULT NULL,
  `respondent_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `respondent_gender` enum('Male','Female') DEFAULT NULL,
  `respondent_phone` char(13) DEFAULT NULL,
  `respondent_address` text DEFAULT NULL,
  `complaint_statement` text NOT NULL,
  `reported_by` tinyint(1) NOT NULL,
  `is_affirmed` tinyint(1) NOT NULL,
  `desk_officer_name` varchar(100) NOT NULL,
  `received_datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`case_no`, `incident_datetime`, `complaint_description`, `complainant_first_name`, `complainant_middle_name`, `complainant_last_name`, `complainant_age`, `complainant_gender`, `complainant_phone`, `complainant_address`, `incident_location`, `victim_first_name`, `victim_middle_name`, `victim_last_name`, `victim_age`, `victim_gender`, `victim_phone`, `victim_address`, `witness_first_name`, `witness_middle_name`, `witness_last_name`, `witness_age`, `witness_gender`, `witness_phone`, `witness_address`, `respondent_first_name`, `respondent_middle_name`, `respondent_last_name`, `respondent_age`, `respondent_gender`, `respondent_phone`, `respondent_address`, `complaint_statement`, `reported_by`, `is_affirmed`, `desk_officer_name`, `received_datetime`) VALUES
(1, '2025-08-14 05:05:00', 'Mischief/Vandalism', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'awdad', 'Joshua', 'Dapar', 'Anoos', 4, '', '09398312443', 'awd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-08-27 11:54:36'),
(2, '2025-08-14 04:05:00', 'NAGING NIGGA', 'Joshua', 'Dapar', 'Anoos', 21, 'Male', '09398312443', 'dilang ', 'dilang ', 'Joshua', 'Dapar', 'Anoos', 8, '', '09398312443', 'ADWAWD', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-08-27 11:56:19'),
(3, '2025-08-13 02:05:00', 'INAWAY AKO NI DEA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'awd', 'Joshua', 'Dapar', 'Anoos', 5, '', '09398312443', 'marinduque', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-08-27 12:07:07'),
(4, '2025-08-14 06:10:00', 'Pet-Related Incidents', 'Joshua', 'Dapar', 'Anoos', NULL, NULL, '09398312443', 'awd', 'adw', 'Joshua', 'Dapar', 'Anoos', 5, '', '09398312443', 'awd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-08-27 12:09:39'),
(5, '2025-08-14 05:03:00', 'INAWAY AKO NI JOM TANGINA ', 'Joshua', 'Dapar', 'Anoos', 21, 'Female', '09398312443', 'awdadwa', 'adwadwawd', 'Joshua', 'Dapar', 'Anoos', 5, '', '09398312443', 'awd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-08-29 06:49:02'),
(6, '2025-09-11 02:00:00', 'Noise Complaints', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 1, '', 'a', 'a', 'b', 'b', 'b', NULL, NULL, NULL, NULL, 'c', 'c', '0', NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-01 05:17:26'),
(7, '2025-09-05 01:00:00', 'Neighbor Disputes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 1, '', 'a', 'a', 'b', 'b', 'b', NULL, NULL, NULL, NULL, 'c', 'c', '0', NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-01 05:22:19'),
(8, '2025-09-04 01:00:00', 'Noise Complaints', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 1, '', 'a', 'a', 'b', 'b', 'b', NULL, NULL, NULL, NULL, 'c', 'c', '0', NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-01 05:30:16'),
(10, '2025-09-11 01:02:00', 'Patay ng kapitan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 1, '', 'a', 'a', 'b', 'b', 'b', NULL, NULL, NULL, NULL, 'c', 'c', '0', NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-02 07:44:06'),
(42, '2025-09-05 02:00:00', '55', '', '', '', 0, '', '', '', '55', 'a', '', 'a', 1, 'Male', 'a', '5', '', '', '', 0, '', '', '', '', '', '', 0, '', '', '', '33', 1, 1, 'admin sanmiguel', '2025-09-23 17:13:31'),
(43, '2025-09-13 02:00:00', 'Noise Complaints', '', '', '', 0, '', '', '', 'a', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 1, 'Male', '09215834251', 'a', '', '', '', 0, '', '', '', '', '', '', 0, '', '', '', '0', 1, 1, 'admin sanmiguel', '2025-09-23 17:15:30'),
(44, '2025-09-04 01:01:00', 'Neighbor Disputes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, 'Male', '12345678910', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 1, 1, 'admin sanmiguel', '2025-09-25 10:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `reports_archive`
--

CREATE TABLE `reports_archive` (
  `case_no` int(11) NOT NULL,
  `incident_datetime` datetime NOT NULL,
  `complaint_description` varchar(255) NOT NULL,
  `complainant_first_name` varchar(100) DEFAULT NULL,
  `complainant_middle_name` varchar(100) DEFAULT NULL,
  `complainant_last_name` varchar(100) DEFAULT NULL,
  `complainant_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `complainant_gender` enum('Male','Female') DEFAULT NULL,
  `complainant_phone` char(13) DEFAULT NULL,
  `complainant_address` text DEFAULT NULL,
  `incident_location` text NOT NULL,
  `victim_first_name` varchar(100) NOT NULL,
  `victim_middle_name` varchar(100) DEFAULT NULL,
  `victim_last_name` varchar(100) NOT NULL,
  `victim_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `victim_gender` enum('Male','Female') DEFAULT NULL,
  `victim_phone` char(13) DEFAULT NULL,
  `victim_address` text NOT NULL,
  `witness_first_name` varchar(100) DEFAULT NULL,
  `witness_middle_name` varchar(100) DEFAULT NULL,
  `witness_last_name` varchar(100) DEFAULT NULL,
  `witness_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `witness_gender` enum('Male','Female') DEFAULT NULL,
  `witness_phone` char(13) DEFAULT NULL,
  `witness_address` text DEFAULT NULL,
  `respondent_first_name` varchar(100) DEFAULT NULL,
  `respondent_middle_name` varchar(100) DEFAULT NULL,
  `respondent_last_name` varchar(100) DEFAULT NULL,
  `respondent_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `respondent_gender` enum('Male','Female') DEFAULT NULL,
  `respondent_phone` char(13) DEFAULT NULL,
  `respondent_address` text DEFAULT NULL,
  `complaint_statement` text NOT NULL,
  `reported_by` tinyint(1) NOT NULL,
  `is_affirmed` tinyint(1) NOT NULL,
  `desk_officer_name` varchar(100) NOT NULL,
  `received_datetime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports_archive`
--

INSERT INTO `reports_archive` (`case_no`, `incident_datetime`, `complaint_description`, `complainant_first_name`, `complainant_middle_name`, `complainant_last_name`, `complainant_age`, `complainant_gender`, `complainant_phone`, `complainant_address`, `incident_location`, `victim_first_name`, `victim_middle_name`, `victim_last_name`, `victim_age`, `victim_gender`, `victim_phone`, `victim_address`, `witness_first_name`, `witness_middle_name`, `witness_last_name`, `witness_age`, `witness_gender`, `witness_phone`, `witness_address`, `respondent_first_name`, `respondent_middle_name`, `respondent_last_name`, `respondent_age`, `respondent_gender`, `respondent_phone`, `respondent_address`, `complaint_statement`, `reported_by`, `is_affirmed`, `desk_officer_name`, `received_datetime`) VALUES
(0, '2025-09-16 17:05:21', 'a', 'a', 'a', 'a', 15, 'Male', '0000-000-0000', NULL, '', '', NULL, '', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'asd', 1, 1, 'asd', '2025-09-16 15:06:16'),
(38, '2025-09-17 19:26:00', 'Inaway ako ni Jom ', 'joshua ', 'dapar', 'anoos', 22, 'Male', '0000000000', 'Pasig City, Barangay San Miguel ', 'Pasig City, barangay san miguel', 'Joshua', 'Dapar', 'Anoos', 23, 'Male', '0000000000', 'Pasig City, Baragay San Miguel ', 'Jeomaric', 'Laureta', 'Peñaflor', 0, '', '0000000000', '', 'Jeomaric', 'Laureta', 'Peñaflor', 0, '', '0000000000', '', '0', 1, 1, 'admin sanmiguel', '2025-09-17 11:30:13'),
(40, '2025-09-16 01:00:00', 'a', '', '', '', 0, '', '', '', 'a', 'a', '', 'a', 1, 'Male', 'a', 'a', '', '', '', 0, '', '', '', '', '', '', 0, '', '', '', '0', 1, 1, 'admin sanmiguel', '2025-09-23 15:25:34'),
(37, '2025-09-10 02:01:00', 'Mischief/Vandalism', '', '', '', 0, '', '', '', 'adad', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 2, 'Male', '09215834251', 'ada', '', '', '', 0, 'Male', '', '', '', '', '', 0, 'Male', '', '', '0', 1, 1, 'Penaflor Jeomaric', '2025-09-16 13:43:21'),
(35, '2025-09-17 02:01:00', 'Neighbor Disputes', '', '', '', 0, 'Female', '', '', 'adad', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 2, '', '09215834251', 'adada', '', '', '', 0, 'Female', '', '', '', '', '', 0, 'Female', '', '', '0', 1, 1, 'Penaflor Jeomaric', '2025-09-16 13:23:15'),
(36, '2025-09-16 01:00:00', 'Noise Complaints', '', '', '', 0, '', '', '', 'dada', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 1, '', '09215834251', 'ada', '', '', '', 0, 'Male', '', '', '', '', '', 0, 'Male', '', '', '0', 1, 1, 'Penaflor Jeomaric', '2025-09-16 13:31:36'),
(34, '2025-09-10 02:00:00', 'Noise Complaints', '', '', '', 0, 'Male', '', '', 'dada', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 3, '', '09215834251', 'adad', '', '', '', 0, 'Male', '', '', '', '', '', 0, 'Male', '', '', '0', 1, 1, 'Penaflor Jeomaric', '2025-09-16 13:22:43'),
(32, '2025-09-24 02:00:00', 'Neighbor Disputes', NULL, NULL, NULL, NULL, 'Female', NULL, NULL, 'caca', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 3, '', '09215834251', 'adad', NULL, NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'Female', NULL, NULL, '0', 1, 1, 'admin sanmiguel', '2025-09-16 13:13:27'),
(39, '2025-09-03 01:00:00', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', 'a', 'a', 1, 'Male', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Penaflor Jeomaric', '2025-09-22 15:47:24'),
(31, '2025-09-11 02:01:00', 'Noise Complaints', '', '', '', 0, '', '', '', 'dada', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 2, '', '09215834251', 'adwa', '', '', '', 0, '', '', '', '', '', '', 0, '', '', '', '0', 1, 1, 'admin sanmiguel', '2025-09-16 13:11:21'),
(33, '2025-09-16 02:00:00', 'Minor Theft', NULL, NULL, NULL, NULL, 'Male', NULL, NULL, 'dada', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 1, '', '09215834251', 'adad', NULL, NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, NULL, NULL, 'Male', NULL, NULL, '0', 1, 1, 'admin sanmiguel', '2025-09-16 13:14:50'),
(29, '2025-09-16 02:01:00', 'Neighbor Disputes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patay', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 100, '', '09215834251', 'bahay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-16 13:01:20'),
(28, '2025-09-11 02:01:00', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', NULL, NULL, NULL, NULL, NULL, 'a', NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 10:19:03'),
(27, '2025-09-05 01:01:00', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', NULL, NULL, NULL, NULL, NULL, 'a', NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 10:18:43'),
(25, '2025-09-05 01:00:00', 'c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'c', 'a', NULL, 'a', 1, '', 'a', 'c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 09:20:09'),
(26, '2025-09-05 02:01:00', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 09:46:12'),
(24, '2025-09-11 02:00:00', 'c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 09:19:36'),
(19, '2025-09-04 00:00:00', 'Noise Complaints', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:43:09'),
(23, '2025-09-05 01:00:00', 'b', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 09:17:01'),
(20, '2025-09-10 01:00:00', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 07:51:35'),
(21, '2025-09-04 01:00:00', 'Noise Complaints', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'a', 'a', NULL, 'a', 1, '', 'a', 'A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'Joshua Anoos', '2025-09-04 08:39:19'),
(18, '2025-09-04 00:00:00', 'Noise Complaints', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', '1', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', '1', 1, 1, 'Joshua Anoos', '2025-09-02 08:29:38'),
(16, '2025-09-04 00:00:00', '', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:26:59'),
(14, '2025-09-18 00:00:00', 'test', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:18:19'),
(15, '2025-09-04 00:00:00', '', 'a', '', 'a', 0, '', '', '', '', 'Jeomaric', 'ANoos', 'Jeomaric', 18, 'Male', '0', 'a', 'Lyle', 'Jeomaric', 'Lyle', 0, '', '0', '', 'Bilbao', 'Lyle', 'Bilbao', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:26:33'),
(41, '2025-09-04 01:00:00', 'a', 'a', 'a', 'a', 30, 'Male', 'a', 'a', 'a', 'a', 'a', 'a', 1, 'Male', 'a', 'a', 'a', 'a', 'a', 15, 'Male', 'a', 'a', 'a', 'a', 'a', 13, 'Male', 'a', 'a', '0', 1, 1, 'admin sanmiguel', '2025-09-23 15:26:31'),
(17, '2025-09-11 00:00:00', '', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:28:27'),
(13, '2025-09-10 00:00:00', 'Others', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:09:12'),
(12, '2025-09-11 00:00:00', 'Others', '', '', '', 0, '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', 18, 'Male', '0', 'a', '', '', '', 0, '', '0', '', '', '', '', 0, '', '0', '', 'a', 1, 1, 'Joshua Anoos', '2025-09-02 08:08:29'),
(11, '2025-09-11 02:01:00', 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', 'a', 'a', 'a', 1, '', 'a', '1', 'b', 'b', 'b', NULL, NULL, NULL, NULL, 'c', 'c', '0', NULL, NULL, NULL, NULL, '1', 1, 1, 'Joshua Anoos', '2025-09-02 07:45:37'),
(9, '2025-09-10 01:00:00', 'Noise Complaints', '', '', '', 0, '', '', '', 'a', 'a', 'a', 'a', 1, '', 'a', 'a', 'b', 'b', 'b', 0, '', '', '', 'c', 'c', '0', 0, '', '', '', '0', 1, 1, 'Joshua Anoos', '2025-09-02 06:57:46'),
(30, '2025-09-16 02:01:00', 'Neighbor Disputes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'adsa', 'Jeomaric', 'Laureta', 'Laureta, Peñaflor', 1, '', '09215834251', 'asda', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1, 1, 'admin sanmiguel', '2025-09-16 13:09:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`case_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `case_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
