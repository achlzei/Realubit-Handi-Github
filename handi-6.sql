-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 06:58 AM
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
-- Database: `handi`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_ID` int(11) NOT NULL,
  `category_name` varchar(55) NOT NULL,
  `category_description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_ID`, `category_name`, `category_description`) VALUES
(1, 'Bags', 'Handmade bags'),
(2, 'Head Accessories', 'Handcrafted Head Accessories'),
(3, 'Footwears', 'Abaca Footwears'),
(4, 'Pamaypay', 'Handmade Abaniko');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `Item_ID` int(11) NOT NULL,
  `items_name` varchar(100) NOT NULL,
  `item_price` decimal(6,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `item_description` varchar(255) NOT NULL,
  `item_img` varchar(255) NOT NULL,
  `category_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`Item_ID`, `items_name`, `item_price`, `stock_quantity`, `item_description`, `item_img`, `category_ID`) VALUES
(17, 'Lala Bag', 1499.00, 99, 'Lala (Bikolano: Weave). Simple and direct, paying homage to the core crafting technique.	\r\n\r\nHand-woven abaca structured bag and mini-companion set. Features a sturdy, flat base and a decorative knotted flap closure. Showcases the durability of abaca fibe', 'item_692a82a4210d5_1764393636_item_6926c56308b48_1764148579_1763726081_bags.png.png', 1),
(18, 'Paypay', 199.00, 99, 'Paypay (Tagalog/Bicolano: Fan or To Fan). Direct, authentic, and easily understood.	\r\n\r\nThe essential cooling accessory. A collection of hand-woven abaca fans showcasing diverse weaves and natural wood handles. Functional art for tropical living.', 'item_692a84a19429f_1764394145_1764140818_paypay.png.png', 4),
(19, 'Salóg Sandal', 249.00, 99, 'Salóg (Visayan/Bicolano: Floor covering or mat). Implies flat, comfortable grounding.	\r\n\r\nThe essential warm-weather flat. Features a comfortable, wide abaca woven strap and secure ankle buckle. Naturally breathable and lightweight for daily wear.', 'item_692a853833359_1764394296_sandal.png.png', 3),
(20, 'Siklot Lace-Up', 699.00, 99, 'Siklot (Tagalog for Macrame knot/Weave) or Sikát (Visayan for Prominent/Famous).	\r\n\r\nArtisan-made lace-up shoe with a stunning, open-lattice weave. The natural texture and breathable design make it a standout for formal summer occasions.', 'item_692a8574b62bf_1764394356_sapatos,png.png', 3),
(21, 'Payag Loafer', 350.00, 99, 'Payag (Bicolano: Small Nipa Hut/Shelter). Evokes a relaxing, casual, and tropical feel.	\r\n\r\nThe ultimate resort slipper. Naturally breathable abaca woven in a distinct pattern, with a sturdy jute sole. Perfect for relaxed, warm-weather wear.', 'item_692a85b197ffe_1764394417_tsinelas.png.png', 3),
(22, 'Lakbay Sling', 529.00, 88, 'Lakbay (Tagalog: Journey/Travel). Emphasizes its use as a stylish travel or daily companion.	\r\n\r\nThe modern traveler\'s essential. Durable abaca weave and strong leather construction designed for hands-free movement. Carry hydration and essentials in one c', 'item_692a85f5a7433_1764394485_1763724823_cover for thumbker.png', 1),
(23, 'Abaca Sumbrero', 259.00, 75, 'The essential Filipino cap. Expertly hand-woven from durable abaca fiber for exceptional breathability and a natural, lightweight structure.', 'item_692a86212b082_1764394529_1763724359_hero_crafts.jpg.png', 2);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_ID` int(11) NOT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `sender_name` varchar(100) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message_content` text NOT NULL,
  `date_sent` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_ID`, `user_ID`, `sender_name`, `sender_email`, `subject`, `message_content`, `date_sent`) VALUES
(4, NULL, 'Aira', 'aira@gmail.com', 'Web System', 'hhdhdhhf', '2025-12-01'),
(5, NULL, 'Aira', 'aira@gmail.com', 'Web System', 'hvcbvhvg', '2025-12-01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `Order_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `item_ID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `shipping_address` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`Order_ID`, `user_ID`, `item_ID`, `quantity`, `total_price`, `order_status`, `shipping_address`, `contact_number`, `order_date`) VALUES
(33, 7, 22, 2, 1058.00, 'Delivered', 'qqq', '', '2025-11-29 06:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_address` varchar(255) NOT NULL,
  `user_email_address` varchar(255) NOT NULL,
  `user_contact_number` int(11) NOT NULL,
  `date_joined` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `fullname`, `username`, `user_role`, `password`, `user_address`, `user_email_address`, `user_contact_number`, `date_joined`) VALUES
(1, 'Allysa', 'ly', '', '0', '', '', 0, '2025-11-05 08:12:22'),
(2, 'Arleczar', 'ally', '', '$2y$10$rKIN5xy7QXoo/7dbRchRKuI8jkOepWKIYBdoC8qgHd2wXPaXdo3sq', '', '', 123456789, '2025-11-05 08:14:27'),
(3, 'Aina Shanell Cortes', 'shan', '', '$2y$10$qQflURX.NEcifFe6Y.IFkudrzjZjRQNkPf0l1uis2XEmQH5ulUvXq', 'Polangui', 'shan@gmail.com', 987654321, '2025-11-05 08:16:51'),
(4, 'Admin', 'admin', 'admin', 'happyako101', 'Bu polangui', 'admin@gmail.com', 639104556, '2025-11-05 09:08:27'),
(5, 'Admin', 'admin', 'admin', 'happyako101', 'Bu polangui', 'admin@gmail.com', 639104556, '2025-11-05 09:08:41'),
(7, 'aira', 'aira', 'user', '$2y$10$lguvHSgqwcZbonqMn5tsu.u5onduWYs0w4br3DHbqd/cg31HYRTo.', 'uranos', 'aira@gmail.com', 123456789, '2025-11-18 02:40:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_ID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`Item_ID`),
  ADD KEY `items_category_id` (`category_ID`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_ID`),
  ADD KEY `fk_user` (`user_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `order_user_id` (`user_ID`),
  ADD KEY `order_item_id` (`item_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `Item_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id` FOREIGN KEY (`category_ID`) REFERENCES `category` (`category_ID`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `order_item_id` FOREIGN KEY (`item_ID`) REFERENCES `items` (`Item_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_user_id` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
