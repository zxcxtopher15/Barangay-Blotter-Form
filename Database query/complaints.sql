-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 06:44 AM
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
-- Database: `p1`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_no` int(11) NOT NULL,
  `reported_by` tinyint(1) NOT NULL,
  `incident_datetime` datetime NOT NULL,
  `complaint_description` varchar(100) NOT NULL,
  `complainant_first_name` varchar(50) NOT NULL,
  `complainant_middle_name` varchar(50) DEFAULT NULL,
  `complainant_last_name` varchar(50) NOT NULL,
  `complainant_age` varchar(100) DEFAULT NULL,
  `complainant_gender` enum('Male','Female') NOT NULL,
  `complainant_phone` char(11) DEFAULT NULL,
  `complainant_address` varchar(100) DEFAULT NULL,
  `incident_location` varchar(100) NOT NULL,
  `victim_first_name` varchar(50) NOT NULL,
  `victim_middle_name` varchar(50) DEFAULT NULL,
  `victim_last_name` varchar(50) NOT NULL,
  `victim_age` varchar(100) DEFAULT NULL,
  `victim_gender` enum('Male','Female') NOT NULL,
  `victim_phone` char(11) DEFAULT NULL,
  `victim_address` varchar(100) DEFAULT NULL,
  `witness_first_name` varchar(50) DEFAULT NULL,
  `witness_middle_name` varchar(50) DEFAULT NULL,
  `witness_last_name` varchar(50) DEFAULT NULL,
  `witness_age` varchar(100) DEFAULT NULL,
  `witness_gender` enum('Male','Female') DEFAULT NULL,
  `witness_phone` char(11) DEFAULT NULL,
  `witness_address` varchar(100) DEFAULT NULL,
  `respondent_first_name` varchar(50) NOT NULL,
  `respondent_middle_name` varchar(50) DEFAULT NULL,
  `respondent_last_name` varchar(50) NOT NULL,
  `respondent_age` varchar(100) DEFAULT NULL,
  `respondent_gender` enum('Male','Female') NOT NULL,
  `respondent_phone` char(11) DEFAULT NULL,
  `respondent_address` varchar(100) DEFAULT NULL,
  `complaint_statement` varchar(250) NOT NULL,
  `is_affirmed` tinyint(1) NOT NULL,
  `received_datetime` datetime DEFAULT current_timestamp(),
  `desk_officer_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_no`, `reported_by`, `incident_datetime`, `complaint_description`, `complainant_first_name`, `complainant_middle_name`, `complainant_last_name`, `complainant_age`, `complainant_gender`, `complainant_phone`, `complainant_address`, `incident_location`, `victim_first_name`, `victim_middle_name`, `victim_last_name`, `victim_age`, `victim_gender`, `victim_phone`, `victim_address`, `witness_first_name`, `witness_middle_name`, `witness_last_name`, `witness_age`, `witness_gender`, `witness_phone`, `witness_address`, `respondent_first_name`, `respondent_middle_name`, `respondent_last_name`, `respondent_age`, `respondent_gender`, `respondent_phone`, `respondent_address`, `complaint_statement`, `is_affirmed`, `received_datetime`, `desk_officer_name`) VALUES
(47, 1, '2025-08-25 05:05:00', '', '', '', '', '0', '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', '18', 'Male', '9215834251', 'a', '', '', '', '0', '', '0', '', '', '', '', '0', '', '0', '', 'a', 1, '2025-08-20 14:28:14', 'Joshua Anoos'),
(48, 1, '2025-08-25 17:05:00', '', '', '', '', '0', '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', '18', 'Male', '9215834251', 'a', '', '', '', '0', '', '0', '', '', '', '', '0', '', '0', '', 'a', 1, '2025-08-20 14:28:50', 'Joshua Anoos'),
(49, 1, '2025-08-25 17:05:00', '', '', '', '', '0', '', '', '', '', 'Jeomaric', '', 'Laureta, Peñaflor', '18', 'Male', '9215834251', 'a', '', '', '', '0', '', '0', '', '', '', '', '0', '', '0', '', 'a', 1, '2025-08-20 14:29:29', 'Joshua Anoos');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
