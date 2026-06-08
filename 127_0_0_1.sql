-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 06:42 AM
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
-- Database: `assignment`
--
CREATE DATABASE IF NOT EXISTS `assignment` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `assignment`;

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `data_id` int(11) NOT NULL,
  `app_name` varchar(30) NOT NULL,
  `image` varchar(30) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data`
--

INSERT INTO `data` (`data_id`, `app_name`, `image`, `description`) VALUES
(1, 'facebook', 'F.png', '\r\n\r\nFacebook is a social media platform founded by Mark Zuckerberg and his college roommates Eduardo Saverin, Andrew McCollum, Dustin Moskovitz, and Chris Hughes. It was launched in 2004 initially as \"TheFacebook\" and was exclusively for Harvard University students. Over time, it expanded to other colleges, then to the general public, and eventually became one of the largest and most influential social media platforms globally.\r\n\r\nUsers can create profiles, connect with friends and family, share updates, photos, and videos, join groups, and follow pages of interest. Facebook has faced scrutiny over various issues, including privacy concerns, its handling of user data, and its role in spreading misinformation. Despite these controversies, it remains one of the most widely used social media platforms worldwide, with billions of active users.'),
(2, 'Youtube', 'YTT.jpg', 'YouTube is a video-sharing platform founded in 2005 by former PayPal employees Steve Chen, Chad Hurley, and Jawed Karim. It allows users to upload, view, rate, share, and comment on videos. YouTube offers a wide variety of content, including music videos, vlogs, tutorials, entertainment, educational videos, and more.\r\n\r\nOver the years, YouTube has grown into one of the largest and most popular websites globally, attracting billions of users who watch billions of hours of video every day. In addition to user-generated content, YouTube also hosts content from media corporations, individual creators, and independent filmmakers.'),
(3, 'instagram', 'instagram.png', 'Instagram is a social media platform focused on sharing photos and videos. It was launched in 2010 by Kevin Systrom and Mike Krieger and was later acquired by Facebook in 2012. Instagram allows users to upload photos and videos, apply filters and editing effects, and share them publicly or with their followers.\r\n\r\nOne of Instagram\'s key features is its emphasis on visual content, making it popular among users who enjoy photography, art, fashion, and lifestyle content. In addition to individual users, businesses, brands, and influencers also use Instagram as a platform for marketing and promoting products and services.\r\n\r\nInstagram has introduced various features over the years, including Instagram Stories, IGTV (Instagram TV), Reels, and shopping features, expanding its functionality beyond just photo sharing.'),
(4, 'X', 'twitter.png', '\r\nTwitter is a social media platform founded in 2006 by Jack Dorsey, Biz Stone, and Evan Williams. It allows users to post and interact with messages known as \"tweets,\" which are limited to 280 characters (originally 140 characters until 2017). Users can follow other accounts to see their tweets in their timeline, and they can also like, retweet, or reply to tweets.\r\n\r\nTwitter is known for its real-time nature, making it popular for sharing news, opinions, and updates on various topics. It has become a key platform for public figures, organizations, and individuals to communicate, engage with their audience, and participate in public discourse.\r\n\r\nTwitter has introduced features like hashtags to categorize tweets, Twitter Lists to organize accounts, and Twitter Moments to curate collections of tweets around specific topics or events. It has also faced challenges related to harassment, misinformation, and content moderation, prompting the platform to implement various policies and tools to address these issues.'),
(5, 'telegram', 't.png', 'Telegram is a cloud-based instant messaging app and platform that prioritizes security and privacy. It was developed by the Russian entrepreneur Pavel Durov and his brother Nikolai Durov, and it was launched in 2013. Telegram allows users to send messages, photos, videos, voice messages, and files of any type up to 2GB in size.\r\n\r\nOne of Telegram\'s key features is its focus on security. It offers end-to-end encryption for secret chats, which means that only the sender and recipient can read the messages. It also supports self-destructing messages, which are automatically deleted after a specified period.\r\n\r\nTelegram has gained popularity for its robust security features, user-friendly interface, and customizable options. It supports group chats with up to 200,000 members, channels for broadcasting messages to large audiences, and bots for automating tasks and providing services.'),
(6, 'tiktok', 'tiktok.png', 'TikTok is a social media platform for short-form mobile videos. It was launched in 2016 by the Chinese company ByteDance and has since gained tremendous popularity worldwide. TikTok allows users to create and share videos ranging from 15 seconds to one minute in length, often featuring music, filters, and various effects.\r\n\r\nThe platform is known for its user-friendly interface and its algorithm, which quickly learns user preferences and serves them a personalized feed of content. TikTok has a diverse range of content, including lip-syncing, comedy sketches, dance challenges, cooking tutorials, and more.\r\n\r\nTikTok\'s popularity has surged, particularly among younger demographics, making it one of the fastest-growing social media platforms. It has been praised for its ability to democratize content creation, allowing users to become viral sensations overnight.'),
(7, 'linkedin', 'linkedin.png', 'LinkedIn is a social networking platform primarily focused on professional networking and career development. It was founded in 2002 and launched in 2003 by Reid Hoffman, Allen Blue, Konstantin Guericke, Eric Ly, and Jean-Luc Vaillant. LinkedIn is designed for professionals to connect with colleagues, employers, potential clients, and job seekers.\r\n\r\nUsers create profiles that serve as digital resumes, highlighting their work experience, education, skills, and professional accomplishments. They can connect with other users they know professionally and join industry-specific groups to network, share knowledge, and discuss trends.\r\n\r\nLinkedIn offers various features to facilitate professional networking and career advancement, including job listings, company pages, skill endorsements, and recommendations. Users can also publish articles and share content related to their field of expertise to establish themselves as thought leaders and expand their professional network.'),
(8, 'reddit', 'reddit.png', '\r\nReddit is a social news aggregation, discussion, and content rating website where registered members can submit content, such as text posts or direct links, and engage in discussions with other users. It was founded in 2005 by Steve Huffman and Alexis Ohanian.\r\n\r\nReddit is organized into thousands of communities called \"subreddits,\" each focusing on a specific topic or interest. Users can subscribe to these subreddits to tailor their feed to their interests. Content on Reddit can be upvoted or downvoted by users, with the most popular content rising to the top of the subreddit or the site\'s front page.\r\n\r\nOne of Reddit\'s defining features is its commitment to user anonymity and pseudonymity. Users are typically identified by their usernames, and while they can share personal information, many choose to remain anonymous.\r\n\r\nReddit has a diverse user base and is known for its wide range of content, from news and politics to memes, niche hobbies, and discussions on virtually any topic imaginable. It has also been a platform for activism and community support, with users organizing fundraisers, spreading awareness about social issues, and providing assistance during crises.'),
(9, 'whatsapp', 'whatsapp.png', 'WhatsApp is a messaging app that allows users to send text messages, voice messages, images, videos, documents, and make voice and video calls over the internet. It was founded in 2009 by Brian Acton and Jan Koum, who later sold it to Facebook in 2014.\r\n\r\nWhatsApp is available on various platforms, including iOS, Android, and web browsers, and it uses end-to-end encryption to secure user communications. This means that only the sender and recipient can read the messages, ensuring privacy and security.\r\n\r\nWhatsApp has grown to become one of the most popular messaging apps globally, with billions of users around the world. It is widely used for personal communication, group chats, and business messaging, offering features such as status updates, WhatsApp Web for desktop use, and WhatsApp Business for small businesses to connect with customers.'),
(10, 'pinterest', 'pinterest.png', '\r\nPinterest is a social media platform and visual discovery engine that allows users to discover, save, and share ideas and inspiration in the form of images and videos. It was founded in 2010 by Ben Silbermann, Evan Sharp, and Paul Sciarra.\r\n\r\nOn Pinterest, users can create virtual pinboards based on their interests, hobbies, and projects. They can then browse through content shared by other users and \"pin\" items they like to their own boards. Pins can include images, videos, articles, recipes, DIY projects, fashion inspiration, home decor ideas, and much more.\r\n\r\nOne of Pinterest\'s key features is its visual search technology, which allows users to search for similar images by clicking on specific elements within a pin. This makes it easy to discover related content and find inspiration based on specific visual elements.\r\n\r\nPinterest is used by individuals for various purposes, including planning weddings, home renovations, travel itineraries, and creative projects. It is also popular among businesses and marketers for showcasing products, driving website traffic, and engaging with potential customers.');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `name` varchar(60) NOT NULL,
  `email` varchar(50) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`name`, `email`, `description`) VALUES
('sss', 'sss@', '12345r'),
('HlaHla', 'mgmg1234@gmail.com', 'asdfasdf');

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

CREATE TABLE `search_history` (
  `s_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `search_name` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `Firstname` varchar(30) NOT NULL,
  `Surname` varchar(30) NOT NULL,
  `Email` varchar(45) NOT NULL,
  `Password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `Firstname`, `Surname`, `Email`, `Password`) VALUES
(3, 'Mg', 'Ba', 'mgmg123@gmail.com', '12345'),
(5, 'sa', 'sa', 'asd', 'asdf'),
(6, 'mg', 'ko', 'mgko12345@gmail.com', '112233'),
(7, 'ma', 'ma', 'ma223@gmail.com', '12345'),
(8, 'asdf', 'asdf', 'asdfasdf', 'asdf'),
(9, 'GGH', 'GGG', 'mga123@gmail.com', '223344'),
(10, 'Mg', 'Kg', 'mgkg112@gmail.com', '123456');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`data_id`);

--
-- Indexes for table `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`s_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
  MODIFY `data_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `search_history`
--
ALTER TABLE `search_history`
  MODIFY `s_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `search_history`
--
ALTER TABLE `search_history`
  ADD CONSTRAINT `search_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE;
--
-- Database: `foodlink_myanmar`
--
CREATE DATABASE IF NOT EXISTS `foodlink_myanmar` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `foodlink_myanmar`;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `street_address` text NOT NULL,
  `city` varchar(50) DEFAULT 'Yangon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `category_name` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT 'assets/default_category.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `restaurant_id`, `category_name`, `image_url`) VALUES
(1, 1, 'Hot Drink', 'uploads/categories/cat_69b8535e11440.png'),
(2, 1, 'Snacks', 'uploads/categories/cat_69b8535911482.png'),
(3, NULL, 'Cold Drink', 'uploads/categories/cat_69b85315cd057.png'),
(4, NULL, 'Fried Rice', 'uploads/categories/cat_69bf1cd16dcad.png'),
(5, NULL, 'Burger', 'uploads/categories/cat_69bf1cfb05a74.png'),
(6, NULL, 'Soup', 'uploads/categories/cat_69bf23a3c258f.png'),
(8, NULL, 'Noodles', 'uploads/categories/cat_69bf41e32e550.png'),
(9, NULL, 'Pizza', 'uploads/categories/cat_69c07452aa778.png'),
(10, NULL, 'Fried Chicken', 'uploads/categories/cat_69c0c544d5f52.png'),
(11, NULL, 'Combo Set', 'uploads/categories/cat_69c0c55d8e41b.png'),
(12, NULL, 'Juice', 'uploads/categories/cat_69c0c81917795.png');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `availability_status` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT 'assets/default_food.png',
  `restaurant_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_special` tinyint(1) DEFAULT 0,
  `discount_percent` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `category_id`, `name`, `description`, `price`, `availability_status`, `image_url`, `restaurant_id`, `is_available`, `is_special`, `discount_percent`) VALUES
(1, 1, 'Latte', 'Coffee,Nourish Milk', 4000.00, 1, 'uploads/items/item_69b5174d10d15.jpg', NULL, 1, 0, 0),
(2, 2, 'Fried Dough (E Kyarr Kwayy)', 'Fried Dough (2pcs)', 1500.00, 1, 'uploads/items/item_69b517d8e2060.jpg', NULL, 1, 0, 0),
(3, 3, 'Milk Tea', 'A chilled version of Myanmar’s classic milk tea, perfectly brewed and blended with creamy milk, served over ice for a refreshing and flavorful treat.', 4200.00, 1, 'uploads/items/item_69b864ba67db1.jpeg', 1, 1, 0, 0),
(4, 2, 'Fried Dough (E Kyarr Kwayy)', 'Enjoy authentic Burmese fried dough, freshly prepared with a crispy outside and soft center. Commonly served with tea, this timeless snack is loved for its simple yet delicious taste.\r\nCrispy and Delicious (2pcs)', 1300.00, 1, 'uploads/items/item_69b86561e1f85.jpeg', 1, 1, 0, 0),
(5, 1, 'Milk Tea (Burmese Style)', 'Enjoy the authentic taste of Burmese tea with our freshly brewed hot milk tea, prepared in the traditional style and served warm for a comforting experience.\r\nOption: Normal, Sweeter, Bitter', 2000.00, 1, 'uploads/items/item_69b86628a8b5e.jpeg', 1, 1, 0, 0),
(6, 1, 'Milk Tea (Burmese Style)', 'Enjoy the authentic taste of Burmese tea with our freshly brewed hot milk tea, prepared in the traditional style and served warm for a comforting experience.\r\nSpecial with more potion\r\nOption- Normal, Sweeter, Bitter,', 3000.00, 1, 'uploads/items/item_69b866f903fb9.jpeg', 1, 1, 0, 0),
(7, 4, 'Burmese fried rice with egg', 'A classic Burmese favorite featuring aromatic golden fried rice cooked with fresh beans and traditional spices, crowned with a crispy fried egg with a soft yolk—comforting, flavorful, and perfectly balanced.', 4500.00, 1, 'assets/uploads/menu/food_69bf318eec6f4.png', 1, 1, 1, 0),
(9, 6, 'Monhinga', 'Experience the taste of Myanmar with our authentic Mohinga, a fragrant fish-based soup with rice noodles, served with egg and traditional fish cake. A timeless favorite enjoyed for breakfast or any time of the day.', 3400.00, 1, 'assets/uploads/menu/food_69bf360382da9.png', 1, 1, 0, 5),
(10, 8, 'Shan Noodle', 'Shan noodles with minced chicken sauce, garnished with crushed peanuts, chili flakes, and fresh herbs', 3000.00, 1, 'assets/uploads/menu/food_69bf422940c22.jpeg', 3, 1, 1, 0),
(11, 8, 'San See', 'Shan glass noodles (San See) with minced meat, lightly stir-fried, glossy noodles with vegetables and herb', 3000.00, 1, 'assets/uploads/menu/food_69bf440a33834.jpeg', 3, 1, 0, 0),
(12, 1, 'Honey Lemon Tea(ပျားလီမွန်)', 'A comforting blend of hot tea, natural honey, and fresh lemon—perfectly balanced for a sweet and soothing drink.', 2000.00, 1, 'assets/uploads/menu/food_69c0aaa8afdd1.jpeg', 4, 1, 0, 5),
(13, 5, 'Crispy Chicken Burger', 'Golden crispy chicken fillet with fresh lettuce and creamy sauce, served in a soft bun for a crunchy and delicious experience.', 5500.00, 1, 'assets/uploads/menu/food_69c0b057adbe3.jpeg', 5, 1, 0, 0),
(14, 5, 'Spicy Chicken Burger', 'Crispy chicken paired with a bold spicy sauce, fresh lettuce, and soft bun—perfect for those who love a little heat.', 5500.00, 1, 'assets/uploads/menu/food_69c0b0ab00f56.jpeg', 5, 1, 1, 0),
(15, 5, 'Bacon Beef Burger', 'Savory beef patty topped with crispy bacon and melted cheese, delivering a rich, smoky, and indulgent flavor.', 6000.00, 1, 'assets/uploads/menu/food_69c0b0dbbc610.jpeg', 5, 1, 0, 5),
(16, 5, 'Veggie Burger', 'A wholesome plant-based patty with fresh vegetables and a soft bun, offering a light yet satisfying option.', 5000.00, 1, 'assets/uploads/menu/food_69c0b0f91fd04.jpeg', 5, 1, 0, 0),
(18, 6, 'Coconut Milk Noodles (Ohn No Khao Swe)', 'Creamy coconut noodle soup with tender chicken, soft noodles, and rich flavors, topped with egg and crispy garnishes for a satisfying dish.', 3000.00, 1, 'assets/uploads/menu/food_69c0b38496197.jpeg', 3, 1, 0, 0),
(19, 6, 'Shan Noodle Soup (Shan Style Broth)', 'Light and flavorful Shan-style noodle soup with tender meat, soft noodles, and aromatic herbs, offering a balanced and comforting taste.', 3500.00, 1, 'assets/uploads/menu/food_69c0b39b81258.jpeg', 3, 1, 0, 5),
(20, 6, 'Burmese Vegetable Soup (Hin Cho)', 'A simple and nourishing Burmese soup made with fresh vegetables in a light, savory broth—perfect for a warm and healthy option.', 2000.00, 1, 'assets/uploads/menu/food_69c0b3bfeebcc.jpeg', 3, 1, 0, 0),
(21, 9, 'Classic Pepperoni Pizza', 'A timeless favorite topped with rich tomato sauce, melted cheese, and crispy pepperoni for a bold and satisfying flavor.', 20000.00, 1, 'assets/uploads/menu/food_69c0b9c3c4236.jpeg', 6, 1, 0, 0),
(22, 9, 'Chicken Sausage Pizza', 'Savory chicken sausage paired with melted cheese and fresh toppings, offering a delicious and hearty bite.', 20000.00, 1, 'assets/uploads/menu/food_69c0b9da75a71.jpeg', 6, 1, 0, 0),
(23, 9, 'Spicy Chicken Pizza (Local Style)', 'A local favorite with tender chicken and a touch of spice, topped with chili flakes for a bold and exciting flavor.', 18000.00, 1, 'assets/uploads/menu/food_69c0b9ef5f0d1.jpeg', 6, 1, 1, 0),
(24, 9, 'Extra Cheese Pizza', 'Loaded with rich, gooey cheese on a perfectly baked crust—simple, creamy, and incredibly satisfying.', 18000.00, 1, 'assets/uploads/menu/food_69c0ba08e96a5.jpeg', 6, 1, 0, 0),
(25, 9, 'Chicken Corn Pizza', 'A delightful combination of tender chicken and sweet corn with melted cheese, creating a balanced and flavorful taste.', 19000.00, 1, 'assets/uploads/menu/food_69c0ba21f2b64.jpeg', 6, 1, 0, 2),
(26, 9, 'Veggie Delight Pizza', 'A fresh mix of vegetables layered over melted cheese, offering a light and flavorful option for veggie lovers.', 15000.00, 1, 'assets/uploads/menu/food_69c0ba3335c88.jpeg', 6, 1, 0, 0),
(27, 2, 'French Fries', 'Golden and crispy fries, lightly seasoned and perfectly cooked for a simple and satisfying snack.', 2000.00, 1, 'assets/uploads/menu/food_69c0bbb975962.jpeg', 6, 1, 0, 0),
(28, 2, 'Chicken Nuggets', 'Bite-sized chicken nuggets with a crispy coating, juicy inside, and perfect for dipping.', 3500.00, 1, 'assets/uploads/menu/food_69c0bbd5ba5d2.jpeg', 6, 1, 0, 0),
(29, 2, 'Garlic Bread', 'Warm toasted bread with garlic and butter, offering a rich and comforting flavor.', 4500.00, 1, 'assets/uploads/menu/food_69c0c4320e1ed.jpeg', 7, 1, 0, 0),
(30, 2, 'French Fries & Fried Fish', 'Crispy golden fries, lightly seasoned and crispy battered fish fillet with a golden crust and tender inside—light and satisfying.', 6500.00, 1, 'assets/uploads/menu/food_69c0c5139ef6d.jpeg', 7, 1, 0, 0),
(32, 10, 'Fried Chicken(Original)', '4 pcs\r\nCrispy fried chicken with a golden crunchy coating and juicy, tender inside—perfectly seasoned for a classic and satisfying taste.', 12000.00, 1, 'assets/uploads/menu/food_69c0c60d9dea1.jpeg', 7, 1, 0, 0),
(33, 10, 'Fried Chicken(Original)', '1pc\r\nCrispy fried chicken with a golden crunchy coating and juicy, tender inside—perfectly seasoned for a classic and satisfying taste.', 3000.00, 1, 'assets/uploads/menu/food_69c0c6aca01c4.jpeg', 7, 1, 0, 0),
(34, 10, 'Fried Chicken(Original)', '6pcs\r\nCrispy fried chicken with a golden crunchy coating and juicy, tender inside—perfectly seasoned for a classic and satisfying taste.', 18000.00, 1, 'assets/uploads/menu/food_69c0c6d2b5b98.jpeg', 7, 1, 0, 2),
(35, 10, 'Fried Chicken(Spicy)', 'Crispy fried chicken coated with bold spicy seasoning, delivering a crunchy texture and a flavorful kick in every bite.', 12000.00, 1, 'assets/uploads/menu/food_69c0c718abf0c.jpeg', 7, 1, 0, 0),
(36, 10, 'Fried Chicken(Spicy)', 'Crispy fried chicken coated with bold spicy seasoning, delivering a crunchy texture and a flavorful kick in every bite.', 3000.00, 1, 'assets/uploads/menu/food_69c0c751a207e.jpeg', 7, 1, 0, 0),
(37, 10, 'Fried Chicken(Spicy)', 'Crispy fried chicken coated with bold spicy seasoning, delivering a crunchy texture and a flavorful kick in every bite.', 18000.00, 1, 'assets/uploads/menu/food_69c0c77329c6b.jpeg', 7, 1, 0, 2),
(38, 11, 'Classic Combo', 'A perfect everyday combo with crispy fried chicken, golden fries, and a refreshing drink.\r\nOption-Cola, Sprite', 10000.00, 1, 'assets/uploads/menu/food_69c0c8ae80a2e.jpeg', 7, 1, 0, 0),
(39, 11, 'Spicy Combo', 'Enjoy the bold flavors of spicy fried chicken paired with crispy fries and a cool drink.\r\nOptional-Cola, Sprite', 10000.00, 1, 'assets/uploads/menu/food_69c0c8d8ee397.jpeg', 7, 1, 0, 0),
(40, 11, 'Double Chicken Combo', 'A hearty combo perfect for sharing, featuring extra crispy chicken, larger fries, and two refreshing drinks.\r\nOptional-Cola, Sprite', 16500.00, 1, 'assets/uploads/menu/food_69c0c91e162f2.jpeg', 7, 1, 0, 0),
(41, 3, 'Cola', 'Refreshing chilled cola with fizzy bubbles and a bold, classic taste.', 2000.00, 1, 'assets/uploads/menu/food_69c0c95974699.jpeg', 7, 1, 0, 0),
(42, 3, 'Sprite', 'Cool and refreshing lemon-lime soda with a crisp and clean taste.', 2000.00, 1, 'assets/uploads/menu/food_69c0c9789c4e2.jpeg', 7, 1, 0, 0),
(43, 3, 'Lemon Tea(Cold)', 'Smooth iced tea infused with fresh lemon for a refreshing and slightly tangy flavor.', 2200.00, 1, 'assets/uploads/menu/food_69c0c9918c77c.jpeg', 7, 1, 0, 0),
(44, 3, 'Banana Milk', 'cold banana milk in glass, creamy light yellow color, smooth texture, minimal setup, clean background, 1:1 square composition, centered drink, premium commercial style, elegant text overlay “Banana Milk”', 2400.00, 1, 'assets/uploads/menu/food_69c0c9a4e241c.jpeg', 7, 1, 0, 0),
(45, 12, 'Orange Juice', 'Freshly squeezed orange juice with a bright, sweet, and citrusy flavor.', 2400.00, 1, 'assets/uploads/menu/food_69c0cb900565f.jpeg', 4, 1, 0, 0),
(46, 12, 'Watermelon Juice', 'Cool and refreshing watermelon juice, naturally sweet and perfect for a hot day.', 3000.00, 1, 'assets/uploads/menu/food_69c0cba4772da.jpeg', 4, 1, 0, 0),
(47, 12, 'Pineapple Juice', 'Tropical pineapple juice with a sweet and tangy flavor, served fresh and chilled.', 3000.00, 1, 'assets/uploads/menu/food_69c0cbb8451b0.jpeg', 4, 1, 0, 0),
(48, 12, 'Mango Juice', 'Smooth and rich mango juice with a naturally sweet and refreshing taste.', 3500.00, 1, 'assets/uploads/menu/food_69c0cbf2b4b47.jpeg', 4, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `rider_id`, `total_amount`, `delivery_address`, `phone_number`, `notes`, `payment_method`, `status`, `created_at`) VALUES
(1, 1, 5, 5000.00, 'Shwepyithar', '09443229110', 'a', 'COD', 'delivered', '2026-03-16 20:47:24'),
(5, 2, 5, 5000.00, 'Shwepyithar', '09778221304', '', 'KBZPay', 'delivered', '2026-03-17 04:13:50'),
(7, 2, 5, 7000.00, 'Bago', '09778221304', 's', 'COD', 'delivered', '2026-03-17 05:00:05'),
(8, 3, 5, 3300.00, 'Dagon', '09778221304', 'Less crispy', 'KBZPay', 'delivered', '2026-03-20 03:12:03'),
(9, 3, 5, 10400.00, 'Tanyin', '094450332110', 'less seet', 'COD', 'delivered', '2026-03-21 22:16:36'),
(10, 3, 5, 3300.00, 'test', '09778221304', 'test', 'KBZPay', 'delivered', '2026-03-21 22:18:31'),
(11, 3, 5, 5000.00, 'r', '09443229110', '', 'WavePay', 'delivered', '2026-03-21 22:20:00'),
(12, 3, 5, 8000.00, 'Shwepyithar', '09778221304', 'less spicy', 'COD', 'delivered', '2026-03-22 23:30:21'),
(13, 3, 5, 7500.00, 'Thakete', '09778221306', '', 'WavePay', 'delivered', '2026-03-22 23:38:25'),
(14, 3, 5, 4500.00, 'Shwepyithar', '09778221304', '', 'COD', 'delivered', '2026-03-23 01:13:59'),
(15, 3, 5, 4500.00, 'Shwepyithar', '09778221304', '', 'COD', 'delivered', '2026-03-23 01:14:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `price`) VALUES
(1, 1, 6, 1, 3000.00),
(5, 5, 6, 1, 3000.00),
(7, 7, 5, 1, 2000.00),
(8, 7, 6, 1, 3000.00),
(9, 8, 4, 1, 1300.00),
(10, 9, 3, 2, 4200.00),
(11, 10, 4, 1, 1300.00),
(12, 11, 6, 1, 3000.00),
(13, 12, 10, 2, 3000.00),
(14, 13, 10, 2, 3000.00),
(15, 14, 10, 1, 3000.00),
(16, 15, 10, 1, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` enum('cod','kpay','wavepay') NOT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotional_banners`
--

CREATE TABLE `promotional_banners` (
  `banner_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotional_banners`
--

INSERT INTO `promotional_banners` (`banner_id`, `restaurant_id`, `image_url`, `is_active`, `created_at`) VALUES
(1, 1, 'assets/uploads/banners/1774133797_premium_Burmese_fried_202603220552.png', 1, '2026-03-21 22:56:37'),
(2, 1, 'assets/uploads/banners/1774137806_premium_Burmese_oil_202603220631.png', 1, '2026-03-22 00:03:26'),
(3, 1, 'assets/uploads/banners/1774137853_authentic_Burmese_mohinga_202603220552.png', 1, '2026-03-22 00:04:13'),
(4, 3, 'assets/uploads/banners/1774141845_luxury_Shan_noodles_202603220747.png', 1, '2026-03-22 01:10:45'),
(5, 1, 'assets/uploads/banners/1774142413_Burmese_coconut_milk_202603220816.jpeg', 1, '2026-03-22 01:20:13'),
(6, 7, 'assets/uploads/banners/1774240408_Fast_food_banner_202603231133.jpeg', 1, '2026-03-23 04:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `restaurant_id` int(11) NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `image_url` varchar(255) DEFAULT 'assets/default_restaurant.png',
  `city` varchar(50) DEFAULT 'Yangon',
  `phone` varchar(20) DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `delivery_fee` int(11) DEFAULT 0,
  `est_time` varchar(50) DEFAULT '20-30 min'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`restaurant_id`, `vendor_id`, `name`, `description`, `status`, `image_url`, `city`, `phone`, `is_open`, `delivery_fee`, `est_time`) VALUES
(1, 2, 'Yangon Tea House', 'Good Quality Burmese Tea', 'approved', 'uploads/restaurants/rest_69b51bcc1b20e.jpeg', 'Yangon', '094501123', 1, 2000, '20-30 min'),
(2, 4, 'Shwe Gabar Restaurant', 'BBQ and Drinks', 'approved', 'uploads/restaurants/rest_69b51cd2191b1.png', 'Mandalay', NULL, 1, 1200, '20-30 min'),
(3, 6, 'Daw Shann May', NULL, 'approved', 'uploads/restaurants/rest_69bf3ee3d4b36.jpeg', 'Yangon', '094601223', 1, 1500, '20-30 min'),
(4, 8, 'U Aung Cafe', NULL, 'approved', 'uploads/restaurants/rest_69c0aa090c5ed.jpeg', 'Yangon', '09778221332', 1, 1400, '20-30 min'),
(5, 11, 'Mandalay\'s Burger', NULL, 'approved', 'uploads/restaurants/rest_69c0b01b815df.jpeg', 'Mandalay', '09730332212', 1, 3000, '20-30 min'),
(6, 14, 'Mee\'s Pizza', NULL, 'approved', 'uploads/restaurants/rest_69c0b8c0f04ec.jpeg', 'Yangon', '094562219', 1, 1000, '20-30 min'),
(7, 15, 'U.S Snack House', NULL, 'approved', 'uploads/restaurants/rest_69c0bdf1c1240.jpeg', 'Yangon', '09778221332', 1, 1200, '20-30 min');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `restaurant_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 3, 5, 'NIce Quality Food', '2026-03-22 23:12:53'),
(2, 1, 3, 5, 'Milk Tea is very delicious', '2026-03-22 23:13:45'),
(3, 1, 12, 4, 'Food are good but a little expensive for a local shop.', '2026-03-23 04:17:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'rider',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'Master Admin', 'admin@foodlink.com', '09123456789', '$2y$10$MXHKn4cw4V/O6MZ3pUdCQuEFCtSCRT7HWzZZwLLFQwICfYLwZwvbO', 'admin', '2026-03-11 22:13:22', 'active'),
(2, 'Vendor1', 'vendor1@foodlink.com', '09778221304', '$2y$10$R5S.sW52hiVZJKw6YWEy6ej82/pp9Cx8Zjn4DCbo9/.fUdhb80UVW', 'vendor', '2026-03-11 22:17:21', 'active'),
(3, 'User1', 'user1@gmail.com', '09443229110', '$2y$10$wfrfKJ5U1g.IMKX4E6SFDeIc3B5gSwSMMGTafbzFWoLHI.fx4thyW', 'customer', '2026-03-11 22:39:46', 'active'),
(4, 'Shwe Gabar Restaurant', 'vendor2@foodlink.com', '094450332110', '$2y$10$dLS8yKDNHP.YZKTuu33yP.Yw5EozhXMW/D57.x0NxLdqeByzbHVKS', 'vendor', '2026-03-14 08:28:14', 'active'),
(5, 'Zaw Zaw', 'rider1@gmail.com', '09778221221', '$2y$10$j2ePmlgndGucqu8udeUDW.7sei7tSi2tEPe3JOmW5n83PHzZinvt2', 'rider', '2026-03-17 04:25:30', 'active'),
(6, 'Daw Shann May', 'vendor3@foodlink.com', '', '$2y$10$NPhJdYKWnlJ2J0abxlXH7uy1Bv.Jn.m8pdqZo8F6u6u7OzCtRGUXi', 'vendor', '2026-03-22 00:50:59', 'active'),
(7, 'Mg Admin1', 'admin1@gmail.com', '', '$2y$10$WbzvOps5XPYtCOtCcBsrwutoG5unIitCIKQJJZU5SrJ/AZJEjuzgu', 'admin', '2026-03-22 22:40:53', 'active'),
(8, 'U Aung Aung', 'vendor4@foodlink.com', '', '$2y$10$e2vKK5EExC8p34tiVh5kE.TgjjGvGAcvG2SIDVBOYR.yHkLPlG7qu', 'vendor', '2026-03-23 02:42:04', 'active'),
(11, 'Michel', 'vendor5@foodlink.com', '', '$2y$10$I4Qdz2V4QUbeiqcrQOPOXOwL5ECcMJko5waEAwQUvp4XGyc/4lX1S', 'vendor', '2026-03-23 03:09:12', 'active'),
(12, 'Mg Kaung', 'user2@gmail.com', '', '$2y$10$KjLyvDXE5yZHYP7jceRuYOWF64SqJPSh34iV/LffiFwHBPXZMH8vu', 'customer', '2026-03-23 03:20:52', 'active'),
(14, 'Daw Khin', 'vendor6@foodlink.com', '', '$2y$10$CwGlROJv0Szoic83ey16J.ZbTW0oz65t7doyAjz3mEKiuYlG5TkqG', 'vendor', '2026-03-23 03:44:09', 'active'),
(15, 'Ko Nway', 'vendor7@foodlink.com', '', '$2y$10$OFiqwrsF3tiR9oBtw4fEouJLfBJ1osD7TSfx3ALncnH7A38atOALm', 'vendor', '2026-03-23 04:11:32', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `promotional_banners`
--
ALTER TABLE `promotional_banners`
  ADD PRIMARY KEY (`banner_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotional_banners`
--
ALTER TABLE `promotional_banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD CONSTRAINT `menu_categories_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `promotional_banners`
--
ALTER TABLE `promotional_banners`
  ADD CONSTRAINT `promotional_banners_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
--
-- Database: `food_ordering`
--
CREATE DATABASE IF NOT EXISTS `food_ordering` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `food_ordering`;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `restaurant_name` varchar(150) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `restaurant_name`, `food_name`, `description`, `price`, `category`, `image`, `status`, `created_at`) VALUES
(4, 6, 'Yangoon Local', 'Monhinga', 'Burmese Food', 2000.00, 'Local', 'Mon.webp', 1, '2026-01-16 06:06:15');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','supplier','admin') NOT NULL,
  `restaurant_image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `restaurant_image`, `status`, `created_at`) VALUES
(1, 'System Admin', 'admin@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'admin', NULL, 1, '2026-01-16 05:11:30'),
(4, 'John Customer', 'customer@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'customer', NULL, 1, '2026-01-16 05:11:30'),
(5, 'MgMG', 'mgmg@gmail.com', '$2y$10$kxIlxo77oAUH4qg50Vn9FestAEijlVnOppJ4dfeU4PFropS5kQOae', 'customer', NULL, 1, '2026-01-16 05:53:55'),
(6, 'Yangoon Local', 'yangoon@gmail.com', 'yangoon123', 'supplier', 'YG.png', 1, '2026-01-16 06:01:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);
--
-- Database: `local_food_mm`
--
CREATE DATABASE IF NOT EXISTS `local_food_mm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `local_food_mm`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `food_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Database: `lvl5cproj`
--
CREATE DATABASE IF NOT EXISTS `lvl5cproj` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lvl5cproj`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `full_name` varchar(101) NOT NULL,
  `username` varchar(101) NOT NULL,
  `password` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `image_name` varchar(254) NOT NULL,
  `featured` varchar(10) NOT NULL,
  `active` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food`
--

CREATE TABLE `food` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_name` varchar(254) NOT NULL,
  `category_id` int(11) NOT NULL,
  `featured` varchar(10) NOT NULL,
  `active` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `food` varchar(160) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` varchar(52) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_contact` varchar(22) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_address` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `food`
--
ALTER TABLE `food`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food`
--
ALTER TABLE `food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `phpmyadmin`;
--
-- Database: `smc`
--
CREATE DATABASE IF NOT EXISTS `smc` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smc`;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `Name` varchar(20) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`Name`, `Email`, `Message`) VALUES
('Kaung', 'ksethan004@gmail.com', 'mnvuvuih'),
('', '', ''),
('', '', ''),
('ksh', 'ksh@gmail.com', 'iug78f76'),
('VUC', 'VUC@gmail.com', 'iougyug'),
('Kaung Set', 'kaungset11226677@gmail.com', 'oiwegh4hgiveni'),
('Kaung', 'ksethan900@gmail.com', 'aass'),
('', '', ''),
('', '', ''),
('', '', ''),
('dd', 'kk2@gmail.com', 'gg');

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `Username` varchar(20) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `register`
--

INSERT INTO `register` (`Username`, `Email`, `Password`) VALUES
('', 'gghh@gmail.com', 'iuqwgfiuw3g'),
('', 'gghh@gmail.com', 'kjqgwfiug'),
('', 'Kk@gmail.com', 'uwgiu'),
('Koko', 'Koko@gmail.com', '11221122'),
('Koko', 'Koko@gmail.com', '11221122'),
('Irene', 'irene@gmail.com', '121212'),
('wunna', 'kaungset11226677@gmail.com', 'kbgiu'),
('wunna', 'kaungset11226677@gmail.com', 'kbgiuw3g'),
('baba', 'sdfg@gmail.com', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `search`
--

CREATE TABLE `search` (
  `category` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search`
--

INSERT INTO `search` (`category`, `description`) VALUES
('Educate Yourself', 'Stay informed about the latest online threats and security best practices.\r\nBe aware of common scams and techniques used by cybercriminals.'),
('Use Secure Wi-Fi Connections', 'Ensure your home Wi-Fi network is secured with a strong password and uses WPA3 encryption.'),
('Regularly Review App Permissions', 'Periodically review the permissions granted to apps on your devices and revoke unnecessary access.');

-- --------------------------------------------------------

--
-- Table structure for table `techonology`
--

CREATE TABLE `techonology` (
  `category` varchar(30) NOT NULL,
  `description` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `techonology`
--

INSERT INTO `techonology` (`category`, `description`) VALUES
('Use Strong Password', 'Create complex passwords with a combination of uppercase and lowercase letters, numbers, and symbols.\r\nUse a unique password for each of your online accounts.'),
('Use Secure Wi-Fi Connections', 'Ensure your home Wi-Fi network is secured with a strong password and uses WPA3 encryption.'),
('Secure Your Devices', 'Use biometric authentication (fingerprint, face recognition) on your devices if available.\r\nSet up device passcodes or PINs for added security.'),
('Use a Virtual Private Network ', 'Use a VPN to encrypt your internet connection and protect your data when using public Wi-Fi networks.'),
('Educate Yourself', 'Stay informed about the latest online threats and security best practices.\r\nBe aware of common scams and techniques used by cybercriminals.'),
('Educate Yourself', 'Stay informed about the latest online threats and security best practices.\r\nBe aware of common scams and techniques used by cybercriminals.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Firstname` varchar(200) DEFAULT NULL,
  `Surname` varchar(200) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Database: `tutorial`
--
CREATE DATABASE IF NOT EXISTS `tutorial` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tutorial`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Username` varchar(200) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Age` int(11) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `Username`, `Email`, `Age`, `Password`) VALUES
(1, 'Mgmg', 'pp123@gmail.com', 5, '12345'),
(2, 'mg', 'qq123@gmail.com', 2, '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Database: `uni_magazine_db`
--
CREATE DATABASE IF NOT EXISTS `uni_magazine_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `uni_magazine_db`;

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `year_id` int(11) NOT NULL,
  `year_label` varchar(20) NOT NULL,
  `closure_date` datetime(3) NOT NULL,
  `final_closure_date` datetime(3) NOT NULL,
  `closure_date_local` datetime(3) DEFAULT NULL,
  `final_closure_date_local` datetime(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`year_id`, `year_label`, `closure_date`, `final_closure_date`, `closure_date_local`, `final_closure_date_local`) VALUES
(1, '2025-2026', '2027-01-21 13:40:00.000', '2027-11-07 15:35:00.000', '2026-03-09 20:40:00.000', '2026-04-07 21:35:00.000');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `contribution_id` int(11) DEFAULT NULL,
  `coordinator_id` int(11) DEFAULT NULL,
  `comment_text` varchar(191) NOT NULL,
  `comment_date` datetime(3) DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `contribution_id`, `coordinator_id`, `comment_text`, `comment_date`) VALUES
(2, 3, 2, 'This is comment by Coordinator Bob', '2026-03-02 08:18:39.000');

-- --------------------------------------------------------

--
-- Table structure for table `contributions`
--

CREATE TABLE `contributions` (
  `contribution_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `year_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `agreed_to_terms` tinyint(1) DEFAULT 0,
  `submitted_at` datetime(3) DEFAULT current_timestamp(3),
  `image_path` varchar(191) DEFAULT NULL,
  `document_path` varchar(191) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contributions`
--

INSERT INTO `contributions` (`contribution_id`, `user_id`, `faculty_id`, `year_id`, `status`, `agreed_to_terms`, `submitted_at`, `image_path`, `document_path`, `title`) VALUES
(3, NULL, 1, 1, 'pending', 1, '2026-03-01 13:57:36.000', NULL, NULL, 'Alice\'s test 1');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `faculty_id` int(11) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`faculty_id`, `faculty_name`, `is_active`) VALUES
(1, 'Computing', 1),
(2, 'Business', 1),
(3, 'Arts & Design', 1),
(4, 'Science', 1);

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `contribution_id` int(11) DEFAULT NULL,
  `file_path` varchar(191) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` datetime(3) DEFAULT current_timestamp(3),
  `uploaded_by` varchar(50) DEFAULT NULL,
  `uploader_name` varchar(100) DEFAULT NULL,
  `version_number` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`file_id`, `contribution_id`, `file_path`, `file_type`, `uploaded_at`, `uploaded_by`, `uploader_name`, `version_number`) VALUES
(3, 3, 'uploads/1772373456623-alice_comp.docx', 'document', '2026-03-01 13:57:36.000', NULL, NULL, 1),
(4, 3, 'uploads/1772373456624-TIP.jpg', 'image', '2026-03-01 13:57:36.000', NULL, NULL, 1),
(5, 3, 'uploads/1772439490529-1772373456623-alice_comp.docx', 'edited_document', '2026-03-02 08:18:10.000', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(191) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('manager','coordinator','student','admin','guest') NOT NULL,
  `faculty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `full_name`, `email`, `role`, `faculty_id`) VALUES
(2, 'bob_coord', '$2b$10$2i7FJs74rrRsS7SqTZxAt.ipuog..9ixDh0NB2jZ9bCT8OZrXd/Vm', 'Bob Smith', 'bob@staff.com', 'coordinator', 1),
(3, 'jane_mgr', '$2b$10$fSUn2v1RIVdS7abrzS84xeQ2nWGBHWshAXRKx6L5d64a8Ykeob7u6', 'Jane Makerie', 'jane@admin.com', 'manager', NULL),
(4, 'sai_admin', '$2b$10$ZtaGVAQFOFLS7q.dCQHYrOPglz0EKkmBpRDBHR8h/hoTMxyr8TZPW', 'Sai Mang', 'admin@uni.com', 'admin', NULL),
(5, 'guest_biz', '$2b$10$p05o41PA1Jt/yGb9r02Rh.TZfy8qfKSOFiekG.GVj8cQ1INtqJlc2', 'Guest Observer', 'guest@external.com', 'guest', 2),
(6, 'jane_busi', '$2b$10$hS3mYATf6GrUSwA01uWg3eSA5GtYWL0Ek5NwVM733nMMb4yQWlI7K', 'Jane Williams', 'jane@student.com', 'student', 2),
(7, 'mg_ko', '$2b$10$PZmbNe4NGO0KVCP1SdI4EuFJUTok4pxa9ZCjXqKzd4fKBoX.id1uO', 'MgKoWin', 'mgko@gmail.com', 'student', 2),
(8, 'John_w', '$2b$10$aIo4a6R0u8M8V51ByVimVe9nYhfd9d1ht9RA2kDI1NjmhHZuR2hry', 'JohnW', 'jwtest@gmail.com', 'student', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`year_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `comments_contribution_id_fkey` (`contribution_id`),
  ADD KEY `comments_coordinator_id_fkey` (`coordinator_id`);

--
-- Indexes for table `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`contribution_id`),
  ADD KEY `contributions_faculty_id_fkey` (`faculty_id`),
  ADD KEY `contributions_user_id_fkey` (`user_id`),
  ADD KEY `contributions_year_id_fkey` (`year_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculties_faculty_name_key` (`faculty_name`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `files_contribution_id_fkey` (`contribution_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_email_key` (`email`),
  ADD UNIQUE KEY `users_username_key` (`username`),
  ADD KEY `users_faculty_id_fkey` (`faculty_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contributions`
--
ALTER TABLE `contributions`
  MODIFY `contribution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_contribution_id_fkey` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`contribution_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_coordinator_id_fkey` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `contributions`
--
ALTER TABLE `contributions`
  ADD CONSTRAINT `contributions_faculty_id_fkey` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `contributions_user_id_fkey` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `contributions_year_id_fkey` FOREIGN KEY (`year_id`) REFERENCES `academic_years` (`year_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_contribution_id_fkey` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`contribution_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_faculty_id_fkey` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
