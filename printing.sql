-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 05, 2026 at 04:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `printing`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `address` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `full_name`, `age`, `address`, `email`, `phone`, `username`, `password`) VALUES
(1, 'Mike Luna', 21, 'Tupi South Cot', 'gars@gmail.com', '09030303033', 'mike', 'mike');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `quantity_on_hand` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `unit`, `quantity_on_hand`, `reorder_level`, `date_added`) VALUES
(1, 'Tarpaulin', 'sqft', 450.00, 100.00, '2026-09-05 05:37:42'),
(2, 'Sticker Paper', 'pcs', 198.00, 50.00, '2026-09-05 05:37:42');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `item_id`, `order_id`, `staff_id`, `quantity`, `created_at`) VALUES
(5, 1, 16, 4, 10.00, '2026-09-05 10:38:06'),
(6, 1, 15, 4, 20.00, '2026-09-05 10:49:02');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product_type` varchar(50) NOT NULL,
  `design_file` varchar(255) DEFAULT NULL,
  `width` decimal(6,2) DEFAULT NULL,
  `height` decimal(6,2) DEFAULT NULL,
  `shape` varchar(30) DEFAULT NULL,
  `quoted_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `fulfillment_method` varchar(20) NOT NULL DEFAULT 'pickup',
  `notes` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `delivery_status` varchar(20) DEFAULT NULL,
  `date_ordered` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `client_id`, `product_type`, `design_file`, `width`, `height`, `shape`, `quoted_price`, `quantity`, `fulfillment_method`, `notes`, `status`, `delivery_status`, `date_ordered`) VALUES
(13, 1, 'Tarpaulin', 'design_6a9b7f925cc52.png', 20.00, 11.00, NULL, NULL, 1, 'delivery', 'Panami ah ha!!!\r\n', 'pending', NULL, '2026-09-05 10:33:54'),
(14, 1, 'Sticker', 'design_6a9b7fb47cea4.png', 10.00, 10.00, 'rectangle', NULL, 1, 'delivery', 'xx', 'pending', NULL, '2026-09-05 10:34:28'),
(15, 1, 'Tarpaulin', 'design_6a9b7fce1d1ac.png', 10.00, 11.00, NULL, 2000.00, 1, 'delivery', 'Ja', 'preparing', 'completed', '2026-09-05 10:34:54'),
(16, 1, 'Tarpaulin', 'design_6a9b7fe23d4fd.png', 1.00, 1.00, NULL, 100.00, 1, 'delivery', '', 'preparing', 'completed', '2026-09-05 10:35:14'),
(17, 1, 'Sticker', 'design_6a9b7ffcdfcad.png', 10.00, 1.00, 'circle', 10.00, 1, 'pickup', 'lapu lapu', 'waiting', NULL, '2026-09-05 10:35:40');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `full_name`, `age`, `address`, `contact_number`, `email`, `username`, `password`) VALUES
(2, 'Juan Dela Cruz', 21, 'Banga, South Cotabato', '09069191999', 'juan@gmail.com', 'juan', 'juan'),
(3, 'Hilario Manansala', 52, '123 Bayanihan St., Brgy. Kapalaran, Tarlac City', '09171234567', 'hilario.manansala@jdprint.com', 'hilario.m', 'password123'),
(4, 'Gertrudes Macapagal', 47, '456 Kalikasan Ave., Brgy. Sampaguita, Pampanga', '09182345678', 'gertrudes.macapagal@jdprint.com', 'gertrudes.m', 'password123'),
(5, 'Eusebio Dimatulac', 38, '789 Pag-asa St., Brgy. Liwanag, Bulacan', '09193456789', 'eusebio.dimatulac@jdprint.com', 'eusebio.d', 'password123'),
(6, 'Arsenia Jimenez', 44, '321 Diwa St., Brgy. Silangan, Rizal', '09204567890', 'arsenia.jimenez@jdprint.com', 'arsenia.j', 'password123'),
(7, 'Epifanio Delos Reyes', 55, '654 Lakambini St., Brgy. Maynila, Cavite', '09215678901', 'epifanio.delosreyes@jdprint.com', 'epifanio.dr', 'password123'),
(8, 'Alejandra Villanueva', 29, '987 Bituin St., Brgy. Mapayapa, Laguna', '09226789012', 'alejandra.villanueva@jdprint.com', 'alejandra.v', 'password123'),
(9, 'Perfecto Aguinaldo', 41, '147 Magsaysay St., Brgy. Maligaya, Nueva Ecija', '09237890123', 'perfecto.aguinaldo@jdprint.com', 'perfecto.a', 'password123'),
(10, 'Leonila Natividad', 36, '258 Silangan St., Brgy. Kapayapaan, Batangas', '09248901234', 'leonila.natividad@jdprint.com', 'leonila.n', 'password123'),
(11, 'Celestino Marasigan', 49, '369 Tanyag St., Brgy. Pagkakaisa, Quezon', '09259012345', 'celestino.marasigan@jdprint.com', 'celestino.m', 'password123'),
(12, 'Adoracion Santiago', 33, '741 Pintig St., Brgy. Samahang, Isabela', '09260123456', 'adoracion.santiago@jdprint.com', 'adoracion.s', 'password123'),
(13, 'Alfonso Bautista', 57, '852 Lakambini St., Brgy. Kalayaan, Pangasinan', '09271234567', 'alfonso.bautista@jdprint.com', 'alfonso.b', 'password123'),
(14, 'Concepcion Hernandez', 40, '963 Laya St., Brgy. Masagana, Ilocos Sur', '09282345678', 'concepcion.hernandez@jdprint.com', 'concepcion.h', 'password123'),
(15, 'Felicisimo Soriano', 62, '159 Silid St., Brgy. Paglingap, Cebu', '09293456789', 'felicisimo.soriano@jdprint.com', 'felicisimo.s', 'password123'),
(16, 'Guillerma Mendez', 35, '753 Tanglaw St., Brgy. Liwanag, Davao', '09304567890', 'guillerma.mendez@jdprint.com', 'guillerma.m', 'password123'),
(17, 'Augusto Velasco', 46, '951 Tinig St., Brgy. Pag-ibig, Iloilo', '09315678901', 'augusto.velasco@jdprint.com', 'augusto.v', 'password123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `inventory_logs_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;