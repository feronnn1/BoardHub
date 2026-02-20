-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 10:43 AM
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
-- Database: `boardinghouse_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `room_id` int(11) DEFAULT NULL,
  `last_payment_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `tenant_id`, `property_id`, `status`, `message`, `created_at`, `room_id`, `last_payment_date`, `next_due_date`) VALUES
(4, 15, 1, 'Approved', 'HASHASHDKJASDJASHDHASJDJASD', '2026-02-16 05:39:30', 91, NULL, '2026-04-16'),
(5, 19, 1, 'Approved', 'wekhfggfgfdsjfhldshfdhsfhsafhshfksdkhfs', '2026-02-16 06:09:18', 91, NULL, '2026-04-16'),
(6, 20, 1, 'Approved', 'sdadsadsadasdasdasdads', '2026-02-18 06:33:45', 92, NULL, NULL),
(7, 22, 1, 'Approved', 'ewefhdshfgdhjsgfjdsgjfdsg', '2026-02-20 06:01:28', 92, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `next_due_date` date NOT NULL,
  `status` varchar(50) DEFAULT 'Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `tenant_id`, `property_id`, `amount`, `payment_date`, `next_due_date`, `status`, `created_at`) VALUES
(1, 15, 0, 500.00, '2026-02-18', '0000-00-00', 'Confirmed', '2026-02-18 06:20:08'),
(2, 15, 1, 500.00, '2026-02-18', '2026-04-16', 'Paid', '2026-02-18 06:20:56'),
(3, 19, 1, 100.00, '2026-02-18', '2026-04-16', 'Paid', '2026-02-18 06:21:12'),
(4, 19, 0, 500.00, '2026-02-18', '0000-00-00', 'Confirmed', '2026-02-18 06:31:29'),
(5, 20, 0, 500.00, '2026-02-18', '0000-00-00', 'Confirmed', '2026-02-18 06:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `landlord_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_facebook` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `room_type` enum('Whole Room','Bed Space') NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `shared_price` int(11) NOT NULL DEFAULT 0,
  `price_shared` decimal(10,2) DEFAULT 0.00,
  `gender_pref` enum('Male Only','Female Only','Mixed') DEFAULT 'Mixed',
  `curfew` varchar(100) DEFAULT 'None',
  `inclusions` text DEFAULT NULL,
  `amenities` varchar(500) DEFAULT NULL,
  `landmarks` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `status` enum('Active','Occupied','Hidden') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `wifi_type` enum('Free WiFi','Piso WiFi','Shared Payment','None') DEFAULT 'None',
  `water_type` enum('Mineral/Drinking','Tap/Cleaning Only') DEFAULT 'Tap/Cleaning Only',
  `paid_addons` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `landlord_id`, `title`, `location`, `description`, `contact_phone`, `contact_facebook`, `contact_email`, `room_type`, `capacity`, `price`, `shared_price`, `price_shared`, `gender_pref`, `curfew`, `inclusions`, `amenities`, `landmarks`, `images`, `status`, `created_at`, `wifi_type`, `water_type`, `paid_addons`) VALUES
(1, 3, 'Yellow House', 'Aclaro St. Pob. Zone III  Estancia, Iloilo', 'Boarding House near NISU West Campus, front of Doctor Sumile Residence ', '09123456789', 'Glenn Franco', 'franco@gmail.com', '', 6, 3000.00, 500, 500.00, 'Mixed', 'Closed gate at 9:00pm', 'Water,Electricity,WiFi,Beddings,Study Table,Private CR', 'Private CR, Study Table, Bed Frame, Kitchen', 'Nearby West Campus NISU, 3 minutes walk', '[\"1771229608_house_green house.jpg\",\"1771229608_house_joliya landlord 3.jpg\",\"1771229608_house_landlord2 pfp.jpg\",\"1771229608_house_red house room 1.jpg\"]', 'Active', '2026-02-14 06:39:03', '', '', 'Drinking Water,Ref Use,Fan'),
(2, 4, 'Green House', 'Tacbuyan Barangay Zone 2 Estancia, Iloilo', 'Si ellen taga batad kag gusto nya mag board di.', '09123456789', 'Glenn Franco', 'franco@gmail.com', 'Whole Room', 1, 7000.00, 500, 875.00, 'Mixed', 'None', 'Water,Electricity,Beddings,Study Table,Private CR', NULL, NULL, '[\"1771142715_house_room1.jpg\",\"1771142715_house_green house.jpg\",\"1771142715_house_landlord2 pfp.jpg\",\"1771142715_house_red house room 1.jpg\"]', 'Active', '2026-02-14 12:14:59', '', '', 'Drinking Water,Fan'),
(3, 6, 'Red House', 'Tacbuyan Barangay Zone 2 Estancia, Iloilo', 'JSHDASJDASJ SADHJASHJDKAS SADKLHASHJK SIODHAS SADASDKJ DKOHJASDHASKL', NULL, NULL, NULL, 'Whole Room', 1, 5000.00, 0, 1000.00, 'Mixed', 'None', 'Water, Electricity, WiFi, Beddings, Cabinet, Study Table', NULL, NULL, '[\"1771142152_house_red house.jpg\"]', 'Active', '2026-02-15 07:55:52', 'Free WiFi', '', 'Drinking Water, Refrigerator Use, Electric Fan');

-- --------------------------------------------------------

--
-- Table structure for table `room_units`
--

CREATE TABLE `room_units` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `total_beds` int(11) NOT NULL DEFAULT 1,
  `occupied_beds` int(11) NOT NULL DEFAULT 0,
  `room_image` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_units`
--

INSERT INTO `room_units` (`id`, `property_id`, `room_name`, `total_beds`, `occupied_beds`, `room_image`) VALUES
(70, 3, 'Room 1', 5, 0, '1771142152_room_0_red house room 1.jpg'),
(71, 3, 'Room 2', 5, 0, '1771142152_room_1_red house room 2.jpg'),
(72, 3, 'Room 3', 5, 0, '1771142152_room_2_red house room 3.jpg'),
(85, 2, 'Room 1', 8, 0, '1771071299_room_0_room1.jpg'),
(86, 2, 'Room 2', 8, 0, '1771071299_room_1_room2.jpg'),
(87, 2, 'Room 3', 8, 0, '1771071299_room_2_room3.jpg'),
(91, 1, 'Room 1', 6, 2, '1771229608_room_0_room1.jpg'),
(92, 1, 'Room 2', 6, 2, '1771054962_room_1_room2.jpg'),
(93, 1, 'Room 3', 6, 0, '1771054962_room_2_room3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `role` enum('Tenant','Landlord','Admin') NOT NULL DEFAULT 'Tenant',
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `email` varchar(255) NOT NULL,
  `facebook_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `first_name`, `last_name`, `phone`, `gender`, `role`, `profile_pic`, `email`, `facebook_name`) VALUES
(1, 'adminpanel', '$2y$10$4FWzsDz7RG417sT6nEMVCuKROjPvWY5AJGT5J/.3y1MHXl3kz978K', '2026-02-14 04:47:57', 'Admin', 'Panel', '09482974137', 'Male', 'Admin', 'adminpfp.png', '', NULL),
(3, 'landlord1', '$2y$10$HHze51XSVm8xU8mrtRuvPOCD9mlQ3uRWsFEvFdo6B3kkVo5KsDTQi', '2026-02-14 04:54:34', 'Land', 'Lord', '09222222222', 'Female', 'Landlord', '1771402967_landlord2pfp.jpg', 'landlord1@gmail.com', 'Kenny Charl'),
(4, 'landlord2', '$2y$10$bpeC7gEqo5HaTPIicel/1OoDdQefrQ1fmW/iHchMIdeHgnGTNk9C6', '2026-02-14 12:05:00', 'Landlord', 'Two', '09112222222', 'Female', 'Landlord', '1771140885_landlord2 pfp.jpg', '', NULL),
(6, 'landlord3', '$2y$10$YC2tv.sMsVTsqyBCoIvOrOJeJkn8MXfr.8O6dRK9yA40o3mOHEXjC', '2026-02-15 07:50:22', 'Joliya', 'Jo', '09333333333', 'Female', 'Landlord', '1771142252_joliya landlord 3.jpg', '', NULL),
(15, 'tenant11', '$2y$10$DqF7AKBOC/yZgF5lSub68.tOHYxESRT4zSPJB3fiRshIUrJYFz4v6', '2026-02-15 14:52:21', 'One', 'Tenant', '09444444444', 'Male', 'Tenant', '1771403133_tenant1pfp.avif', 'one@gmail.com', 'Juan Juan Juan'),
(19, 'tenant22', '$2y$10$9eZaddZrs1OfZmOP6KkIJes2KkYFaxS7O40wETbpoFnsmFDET1UiW', '2026-02-15 15:16:22', 'Two', 'Tenant', '09555555555', 'Male', 'Tenant', '1771403205_tenant2pfp.jpg', '', NULL),
(20, 'tenant33', '$2y$10$q40/bSdtC2BaahlMxavnzOhkVTp6DNlSPgYWFhwMNJOgmA8FRdDMy', '2026-02-18 06:33:16', 'Three', 'Tenant', '09777777777', 'Female', 'Tenant', '1771403274_tenant3pfp.jpg', '', NULL),
(21, 'test1', '$2y$10$nGgPbDdkR219lxReVQAWS..FFWRc82GmXtao1i.VzdSHogemv.R6.', '2026-02-19 16:06:17', 'Test', 'Test', '09111111123', 'Male', 'Tenant', 'default.png', 'test123@gmail.com', 'Joliya Jo'),
(22, 'test2', '$2y$10$/Jgi8UAkrWOkQuUO8g6EOelo3Mt3u5rhLbVXVL9j1W/W5PEK2Cjcq', '2026-02-20 06:00:35', 'frasjdasd', 'dsasdas', '09572342342', 'Male', 'Tenant', 'default.png', 'franco143@gmail.com', 'Franco Glenn');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_units`
--
ALTER TABLE `room_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `room_units`
--
ALTER TABLE `room_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `room_units`
--
ALTER TABLE `room_units`
  ADD CONSTRAINT `room_units_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
