-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql305.infinityfree.com
-- Generation Time: Oct 23, 2026 at 04:46 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_43249720_prof_hassan_portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `institution` varchar(150) DEFAULT NULL,
  `role_title` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointments`
--


-- --------------------------------------------------------

--
-- Table structure for table `appointment_bookings`
--

CREATE TABLE `appointment_bookings` (
  `id` int(11) NOT NULL,
  `student_name` varchar(150) DEFAULT NULL,
  `student_email` varchar(150) DEFAULT NULL,
  `student_phone` varchar(50) DEFAULT NULL,
  `consultation_type` varchar(100) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(50) DEFAULT NULL,
  `institution` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rescheduled','Declined') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(150) DEFAULT NULL,
  `sender_email` varchar(150) DEFAULT NULL,
  `institution` varchar(150) DEFAULT NULL,
  `subject_type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `student_name` varchar(150) DEFAULT NULL,
  `student_email` varchar(150) DEFAULT NULL,
  `student_phone` varchar(50) DEFAULT NULL,
  `academic_background` varchar(250) DEFAULT NULL,
  `preferred_schedule` varchar(100) DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `zoom_join_url` varchar(500) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `degree_title` varchar(200) DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `badge_label` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


CREATE TABLE `leadership` (
  `id` int(11) NOT NULL,
  `role_title` varchar(150) DEFAULT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile_settings`
--

CREATE TABLE `profile_settings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `bio_summary` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `years_exp` int(11) DEFAULT NULL,
  `students_count` varchar(50) DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT NULL,
  `hero_bg` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- Table structure for table `research_papers`
--

CREATE TABLE `research_papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `journal_name` varchar(200) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `abstract_text` text DEFAULT NULL,
  `paper_url` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `software_courses`
--

CREATE TABLE `software_courses` (
  `id` int(11) NOT NULL,
  `software_name` varchar(100) DEFAULT NULL,
  `course_title` varchar(200) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `skill_level` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `icon_class` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Upcoming','Closed') DEFAULT 'Active'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


CREATE TABLE `software_skills` (
  `id` int(11) NOT NULL,
  `tool_name` varchar(100) DEFAULT NULL,
  `proficiency` varchar(50) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;



CREATE TABLE `teaching_courses` (
  `id` int(11) NOT NULL,
  `course_title` varchar(200) DEFAULT NULL,
  `category_badge` varchar(100) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `appointment_bookings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leadership`
--
ALTER TABLE `leadership`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile_settings`
--
ALTER TABLE `profile_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `research_papers`
--
ALTER TABLE `research_papers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `software_courses`
--
ALTER TABLE `software_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `software_skills`
--
ALTER TABLE `software_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teaching_courses`
--
ALTER TABLE `teaching_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointment_bookings`
--
ALTER TABLE `appointment_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leadership`
--
ALTER TABLE `leadership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `profile_settings`
--
ALTER TABLE `profile_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `research_papers`
--
ALTER TABLE `research_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `software_courses`
--
ALTER TABLE `software_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `software_skills`
--
ALTER TABLE `software_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teaching_courses`
--
ALTER TABLE `teaching_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
