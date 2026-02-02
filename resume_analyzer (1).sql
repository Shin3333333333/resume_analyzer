-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 10:42 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resume_analyzer`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `insert_resume_analysis_to_db` (IN `MODE` INT, IN `p_resume_content` VARCHAR(255), IN `p_job_description` TEXT)   BEGIN
	IF MODE = 1
    THEN
    	BEGIN 
    		INSERT INTO tbl_resume_analyses (resume_content
        	                             	,job_description)
    		VALUES 							(p_resume_content
            	                         	,p_job_description);
		END;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_resume_analyses`
--

CREATE TABLE `tbl_resume_analyses` (
  `id` int(11) NOT NULL,
  `resume_content` longtext DEFAULT NULL,
  `job_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_resume_analyses`
--

INSERT INTO `tbl_resume_analyses` (`id`, `resume_content`, `job_description`, `created_at`) VALUES
(1, 'asdsad', 'asdsad', '2026-02-02 10:42:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_resume_analyses`
--
ALTER TABLE `tbl_resume_analyses`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_resume_analyses`
--
ALTER TABLE `tbl_resume_analyses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
