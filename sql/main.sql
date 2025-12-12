-- Create database
CREATE DATABASE bank_simulator
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bank_simulator;

-- TABLE: users
DROP TABLE IF EXISTS users;
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'customer',
  `email` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('Active','Hold') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;



-- TABLE: profile
DROP TABLE IF EXISTS profile;
CREATE TABLE `profile` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `full_name` VARCHAR(50) NOT NULL,
  `DOB` DATE NOT NULL,
  `phone` TEXT NOT NULL,
  `Address` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `profile_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- TABLE: account
DROP TABLE IF EXISTS account;
CREATE TABLE `account` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `account_type` ENUM('savings','current','salary','system') NOT NULL,
  `account_number` BIGINT NOT NULL,
  `balance` INT NOT NULL,
  `min_balance` INT DEFAULT NULL,
  `status` ENUM('Active','Pending','Declined') NOT NULL,
  `ifsc_code` VARCHAR(20) NOT NULL DEFAULT 'INDB0000323',
  `account_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `profile_id` (`profile_id`),
  CONSTRAINT `account_ibfk_1`
    FOREIGN KEY (`profile_id`)
    REFERENCES `profile` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;


-- Transaction table
DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `account_id` INT NOT NULL,
  `transaction_type` ENUM('deposit','withdraw','transfer','fee') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `related_tx_id` INT DEFAULT NULL,
  `transaction_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `performed_by` INT NOT NULL,
  `status` ENUM('completed','pending','cancelled') NOT NULL DEFAULT 'completed',
  PRIMARY KEY (`id`),
  KEY `idx_related_tx_id` (`related_tx_id`),
  CONSTRAINT `fk_transaction_related`
    FOREIGN KEY (`related_tx_id`)
    REFERENCES `transaction` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;




-- Users table Data
INSERT INTO users (id, username, role, email, created_at, last_login, password_hash, status) VALUES
(1, 'Admin', 'admin', 'admin@gmail.com', '2025-11-18 12:31:04', '2025-12-12 12:07:44', '$2y$12$dy8UcRaiczzu8NgHQjaQh.icZKuNdkLt0/40Ji0ubH9...', 'Active'),
(2, 'Ajay01', 'customer', 'ajay@gmail.com', '2025-11-18 16:52:26', '2025-12-11 10:28:55', '$2y$12$/1/1mDHUhBVfPGPQC4yWUe.GQM3wTZ5ypvhsYtRYXGh...', 'Hold'),
(4, 'aman01', 'customer', 'aman@gmail.com', '2025-11-18 11:28:15', '2025-12-11 15:13:10', '$2y$12$LpPBASFmagrncoReoQL0tekGz25PvSiIVIpm2S7bM7m...', 'Active'),
(5, 'sumit', 'customer', 'sumitgarg@gmail.com', '2025-11-19 09:37:53', '2025-12-12 12:06:32', '$2y$12$rpOV5313faN0BbfSu3IaLOnttcOpUvQKcEIipOoqbbS...', 'Active'),
(6, 'chandan', 'customer', 'chandan@gmail.com', '2025-11-20 17:38:37', '2025-12-09 17:46:21', '$2y$12$81uphfxKzF8iGuEUvNSjduXNSFD/uLtNXK4SgYFOt.A...', 'Active'),
(7, 'viany', 'customer', 'vinay@gmail.com', '2025-11-21 10:32:43', '2025-11-21 10:32:43', '$2y$12$u/CN0Zr5U1h8lJB7UoMIT.Ak/ulL00cbZkMzJeuKARF...', 'Active'),
(8, 'kashish', 'customer', 'kashish@gmail.com', '2025-11-21 11:53:21', '2025-12-09 17:47:50', '$2y$12$g5tuFOy3Girl0FTzUELAKebplUpN74wFTUM/FFG16b2...', 'Active'),
(9, 'Mohit', 'customer', 'mohit@gmail.com', '2025-11-21 14:33:37', '2025-11-28 16:20:31', '$2y$12$IhV6cNmc6UyQsdDzLyOvQ.keeHDrZ69wYVhCkXlJ5BL...', 'Active'),
(10, 'Garry', 'customer', 'garima@gmail.com', '2025-11-21 15:26:33', '2025-12-02 10:16:20', '$2y$12$dS/AQR0872qzyDumJfoDHORhm9SmrRSolmrpzp7tBsc...', 'Active'),
(11, 'Vikrant', 'customer', 'vikrant@gmail.com', '2025-11-28 17:45:45', '2025-11-28 17:45:45', '$2y$12$d/TqChUI92clZf/H5TIDu.TvW8Kkvb/YOITBnXg1.Ld...', 'Active'),
(12, 'sammy', 'customer', 'sammy@gmail.com', '2025-12-05 10:07:59', '2025-12-05 10:07:59', '$2y$12$uXaPIQ4Y54Jlujob2cXav.QymNtiIAu.LSBKDduSOqw...', 'Active'),
(14, 'amitbatra', 'customer', 'amitbatra121@yopmail.com', '2025-12-05 17:06:06', '2025-12-08 16:00:43', '$2y$12$0b2GiEPdsBTjYGj3vOzChOAmR.ITHzCaFUpMcIg4A8V...', 'Active'),
(15, 'raghav', 'customer', 'raghav@gmail.com', '2025-12-08 09:22:27', '2025-12-11 17:34:15', '$2y$12$4uj7Gb1bF07VJS.rEZOGxuT4DEIncNNKvyC8THSupT3...', 'Active'),
(16, 'yash', 'customer', 'yash@gmail.com', '2025-12-08 09:28:03', '2025-12-08 17:27:50', '$2y$12$NS8JtJjZVURyonl5R9FtH.XxRVN85xjgLFPVLXbpQkE...', 'Active'),
(17, 'amitk', 'customer', 'amitbatra121@yopmail.com', '2025-12-08 15:44:12', '2025-12-08 15:44:12', '$2y$12$JS0HNcPwxq8GOq4G8eFoc.yYlrIcw9ffp7iSycOG2Lt...', 'Active'),
(18, 'amitk', 'customer', 'amitbatra121@yopmail.com', '2025-12-08 15:44:27', '2025-12-08 15:44:27', '$2y$12$oFOx.4Q/ZUZAzSVJ0UCL2uXQeAB43HhqKOmSSpvb4Yq...', 'Active'),
(19, 'amitk#########################3', 'customer', 'amitbatra121######################################...', '2025-12-08 15:47:16', '2025-12-08 15:47:16', '$2y$12$UVnd4sAxgmWzj6054eOx/..JYX7qvcxo47qzSgczkGl...', 'Active'),
(20, 'amitbatra', 'customer', 'amitbatra11222222222222222222222222222221@yopmail....', '2025-12-08 18:13:30', '2025-12-08 18:13:30', '$2y$12$FWEr.385xScLKC7nVPT8GelosDsEZr2tQfiXeEyZ4Q0...', 'Active'),
(21, 'amit', 'customer', 'amitbatra121555555555555555555555@yopmail.com', '2025-12-08 18:16:58', '2025-12-10 16:35:24', '$2y$12$6Dkd6Oms.fvq3Y0Hkg8RGeHLBL6tJWDvvdnULFByrM3...', 'Active'),
(22, 'Rajat', 'customer', 'rajat@gmail.com', '2025-12-10 12:20:34', '2025-12-10 12:21:15', '$2y$12$CskLwXUIZw3.X6459zS.J.v8Fxwj2A.bFRjsoQ5bab2...', 'Active'),
(23, 'Abhijith', 'customer', 'abhijith@gmail.com', '2025-12-10 15:07:18', '2025-12-11 12:15:11', '$2y$12$BWgGAJKXVbZ8iuJ5PfCc7upCKDybqldj0aKuSzv/KfA...', 'Active'),
(24, 'Liza', 'customer', 'liza@gmail.com', '2025-12-10 17:15:31', '2025-12-11 16:14:13', '$2y$12$dxAyOCztjFnGvsd8GLkco.VnpeTca64DdVbHTIXQAP7...', 'Hold'),
(25, 'Akshay', 'customer', 'akshay0123456@gmail.com', '2025-12-11 11:33:28', '2025-12-11 11:33:47', '$2y$12$WMm.Q4ei5PRsa5S4pl8ymO8goXKaAVxMxe1Q2EOPFBM...', 'Active'),
(26, 'Ankit', 'customer', 'ankitrawat@gmail.com', '2025-12-12 09:27:37', '2025-12-12 11:25:36', '$2y$12$4IVSn92fmvlPBdzD/Hp5M.YZ/E5zDYCJQRLt1nzQ80x...', 'Active');


--  Profile table Data
INSERT INTO profile (id, user_id, full_name, DOB, phone, address) VALUES
(1, 5, 'Sumit Garg', '2000-01-10', '9564823654', '101, bilaspur, india'),
(2, 4, 'Aman Rawat', '1999-02-02', '7906722964', '105, New Delhi, India'),
(3, 2, 'Ajay Roy', '2001-03-06', '8569456237', '502, New Delhi, india0'),
(4, 6, 'chandan', '2003-02-11', '9756248915', '11, haryana'),
(5, 7, 'Vinay', '2002-07-25', '8569456820', '203, Haryana, India'),
(6, 8, 'Kashish Mittal', '2004-03-21', '7596458123', '101, hansi, India'),
(7, 9, 'Mohit Kumar', '2002-03-07', '7546952365', '101, Chandigarh, India'),
(8, 10, 'Garima Tomar', '2003-08-13', '9562348567', '502, meerut, India'),
(9, 11, 'Vikrant Yadav', '2005-06-01', '9564875623', '203, meerut'),
(10, 14, 'Amit Batra', '1990-05-19', '8569456237', '3205, chandigarh'),
(11, 16, 'Yash', '2003-03-05', '9564265865', '02, dehradun, india'),
(12, 22, 'Rajat Rawat', '2003-02-12', '9564236598', '803, sunny enclave'),
(13, 23, 'Abhijith', '2007-12-05', '8564975623', '203, Modern enclave'),
(14, 21, 'Amit Batra', '2007-11-26', '9564856759', 'agrdasgcgdf'),
(15, 24, 'Liza Liza', '2005-08-11', '7594685589', 'ajdaingfcggd'),
(16, 15, 'Raghav Sharma', '2002-05-08', '7564859625', '101, shanti nagar'),
(17, 1, 'Admin', '2000-01-01', '9876543210', 'Indian bank headquaters');





--  Account table Data
INSERT INTO account (id, profile_id, account_type, account_number, balance, min_balance, status, ifsc_code, account_date) VALUES
(1, 1, 'savings', '5510894543', 800, NULL, 'Active', 'INDB0000323', '2025-11-21 10:32:43'),
(3, 2, 'savings', '1604531907', 1740, 1000, 'Active', 'INDB0000323', '2025-11-21 10:32:43'),
(8, 4, 'current', '5040695771', 1000, 1000, 'Active', 'INDB0000323', '2025-11-21 10:32:43'),
(9, 4, 'savings', '1382350014', 2000, 1500, 'Active', 'INDB0000323', '2025-11-21 10:32:43'),
(10, 5, 'savings', '2505450134', 10800, 10, 'Active', 'INDB0000323', '2025-11-21 10:32:43'),
(11, 2, 'savings', '1999445611', 3650, NULL, 'Active', 'INDB0000323', '2025-11-18 11:28:15'),
(12, 1, 'savings', '6073305580', 700, 10, 'Active', 'INDB0000323', '2025-11-19 09:37:53'),
(13, 6, 'salary', '2915132205', 11310, 500, 'Active', 'INDB0000323', '2025-11-21 11:53:21'),
(15, 7, 'salary', '9198726596', 20000, 200, 'Active', 'INDB0000323', '2025-11-21 14:33:37'),
(16, 8, 'savings', '2834645532', 1000, 500, 'Active', 'INDB0000323', '2025-11-21 15:26:33'),
(17, 1, 'savings', '3599136868', 1000, 500, 'Active', 'INDB0000323', '2025-11-19 09:37:53'),
(18, 3, 'savings', '3415149038', 600, NULL, 'Active', 'INDB0000323', '2025-11-18 16:52:26'),
(19, 9, 'savings', '9263465112', 10000, NULL, 'Active', 'INDB0000323', '2025-11-28 17:45:45'),
(21, 10, 'salary', '8202123458', 1900, 3000, 'Active', 'INDB0000323', '2025-12-05 17:06:06'),
(23, 11, 'savings', '8527502494', 1400, 500, 'Active', 'INDB0000323', '2025-12-08 09:28:03'),
(24, 12, 'salary', '3270516966', 600, 0, 'Active', 'INDB0000323', '2025-12-10 12:31:38'),
(31, 15, 'salary', '8201582711', 1300, 0, 'Active', 'INDB0000323', '2025-12-10 18:07:16'),
(32, 2, 'salary', '5990283855', 0, 0, 'Declined', 'INDB0000323', '2025-12-11 15:13:20'),
(33, 16, 'salary', '9921002039', 400, 0, 'Active', 'INDB0000323', '2025-12-11 15:25:06'),
(34, 16, 'savings', '5616504183', 5000, 1000, 'Active', 'INDB0000323', '2025-12-11 15:31:09'),
(35, 17, 'system', '999999999', 0, 0, 'Active', 'INDB0000000', '2025-12-11 18:24:09');




-- Transaction table
INSERT INTO `transaction` (id, account_id, transaction_type, amount, related_tx_id, transaction_date, performed_by, status) VALUES
(1, 3, 'deposit', 500.00, NULL, '2025-11-24 11:40:53', 1, 'completed'),
(2, 3, 'deposit', 500.00, NULL, '2025-11-24 11:41:10', 1, 'completed'),
(3, 3, 'withdraw', 100.00, NULL, '2025-11-24 11:41:47', 1, 'completed'),
(4, 3, 'transfer', 100.00, NULL, '2025-11-24 14:38:11', 1, 'completed'),
(5, 11, 'transfer', 100.00, NULL, '2025-11-24 14:38:11', 1, 'completed'),
(6, 8, 'transfer', 400.00, NULL, '2025-11-25 09:28:40', 6, 'completed'),
(7, 9, 'transfer', 400.00, NULL, '2025-11-25 09:28:40', 6, 'completed'),
(8, 9, 'withdraw', 100.00, NULL, '2025-11-25 09:35:38', 6, 'completed'),
(9, 3, 'withdraw', 100.00, NULL, '2025-11-25 10:07:56', 1, 'completed'),
(10, 10, 'deposit', 500.00, NULL, '2025-11-25 10:25:31', 1, 'completed'),
(11, 3, 'transfer', 200.00, NULL, '2025-11-25 10:27:03', 1, 'completed'),
(12, 10, 'transfer', 200.00, NULL, '2025-11-25 10:27:03', 1, 'completed'),
(13, 10, 'deposit', 100.00, NULL, '2025-11-25 11:20:54', 1, 'completed'),
(14, 10, 'withdraw', 100.00, NULL, '2025-11-25 11:21:18', 1, 'completed'),
(15, 13, 'transfer', 150.00, NULL, '2025-11-25 11:24:55', 1, 'completed'),
(16, 1, 'transfer', 150.00, NULL, '2025-11-25 11:24:55', 1, 'completed'),
(17, 1, 'deposit', 120.00, NULL, '2025-11-25 17:14:49', 1, 'completed'),
(18, 11, 'withdraw', 100.00, NULL, '2025-11-28 09:48:55', 1, 'completed'),
(19, 16, 'deposit', 100.00, NULL, '2025-11-28 10:44:17', 1, 'completed'),
(20, 13, 'deposit', 500.00, NULL, '2025-11-28 11:04:56', 1, 'completed'),
(21, 13, 'withdraw', 100.00, NULL, '2025-11-28 11:09:20', 1, 'completed'),
(22, 13, 'withdraw', 10.00, NULL, '2025-11-28 11:10:42', 1, 'completed'),
(23, 3, 'transfer', 100.00, NULL, '2025-11-28 11:15:53', 1, 'completed'),
(24, 10, 'transfer', 100.00, NULL, '2025-11-28 11:15:53', 1, 'completed'),
(25, 13, 'withdraw', 10.00, NULL, '2025-11-28 12:42:54', 1, 'completed'),
(26, 3, 'withdraw', 100.00, NULL, '2025-11-28 12:45:10', 1, 'completed'),
(27, 11, 'withdraw', 100.00, NULL, '2025-11-28 12:53:40', 1, 'completed'),
(28, 11, 'withdraw', 1.00, NULL, '2025-11-28 12:59:15', 1, 'completed'),
(29, 3, 'withdraw', 1.00, NULL, '2025-11-28 13:02:19', 1, 'completed'),
(30, 3, 'withdraw', 1.00, NULL, '2025-11-28 13:04:42', 1, 'completed'),
(31, 3, 'withdraw', 2.00, NULL, '2025-11-28 13:04:56', 1, 'completed'),
(32, 3, 'withdraw', 6.00, NULL, '2025-11-28 14:46:04', 4, 'completed'),
(33, 3, 'withdraw', 10.00, NULL, '2025-11-28 14:46:43', 4, 'completed'),
(34, 3, 'withdraw', 10.00, NULL, '2025-11-28 14:55:29', 4, 'completed'),
(35, 3, 'withdraw', 10.00, NULL, '2025-11-28 14:55:59', 4, 'completed'),
(36, 3, 'transfer', 10.00, NULL, '2025-11-28 15:00:26', 4, 'completed'),
(37, 11, 'transfer', 10.00, NULL, '2025-11-28 15:00:26', 4, 'completed'),
(38, 11, 'withdraw', 9.00, NULL, '2025-11-28 15:00:46', 4, 'completed'),
(39, 11, 'deposit', 100.00, NULL, '2025-11-28 15:01:16', 1, 'completed'),
(40, 11, 'withdraw', 10.00, NULL, '2025-11-28 15:03:50', 1, 'completed'),
(41, 3, 'withdraw', 10.00, NULL, '2025-11-28 15:05:33', 1, 'completed'),
(42, 3, 'withdraw', 10.00, NULL, '2025-11-28 15:19:29', 1, 'completed'),
(43, 18, 'deposit', 5000.00, NULL, '2025-11-28 16:18:18', 1, 'completed'),
(44, 19, 'deposit', 10000.00, NULL, '2025-11-28 17:48:39', 1, 'completed'),
(45, 1, 'deposit', 5000.00, NULL, '2025-11-29 12:14:28', 1, 'completed'),
(46, 10, 'deposit', 5000.00, NULL, '2025-11-29 12:21:54', 1, 'completed'),
(47, 13, 'withdraw', 30.00, NULL, '2025-11-29 12:26:48', 1, 'completed'),
(48, 10, 'deposit', 5000.00, NULL, '2025-11-29 12:27:21', 1, 'completed'),
(49, 12, 'withdraw', 20.00, NULL, '2025-11-29 12:29:57', 5, 'completed'),
(50, 11, 'deposit', 2000.00, NULL, '2025-11-30 19:40:16', 1, 'completed'),
(51, 11, 'deposit', 500.00, NULL, '2025-11-30 19:42:34', 1, 'completed'),
(52, 11, 'deposit', 500.00, NULL, '2025-11-30 19:43:11', 1, 'completed'),
(53, 11, 'deposit', 200.00, NULL, '2025-11-30 19:43:48', 1, 'completed'),
(54, 12, 'deposit', 500.00, NULL, '2025-11-30 19:51:19', 1, 'completed'),
(55, 16, 'deposit', 400.00, NULL, '2025-11-30 19:59:56', 1, 'completed'),
(56, 10, 'withdraw', 10.00, NULL, '2025-11-30 20:16:59', 1, 'completed'),
(57, 12, 'withdraw', 20.00, NULL, '2025-11-30 20:27:04', 1, 'completed'),
(58, 13, 'transfer', 200.00, NULL, '2025-11-30 20:27:52', 1, 'completed'),
(59, 9, 'transfer', 200.00, NULL, '2025-11-30 20:27:52', 1, 'completed'),
(60, 3, 'withdraw', 30.00, NULL, '2025-12-01 16:38:28', 1, 'completed'),
(61, 18, 'withdraw', 100.00, NULL, '2025-12-01 17:01:07', 1, 'completed'),
(62, 13, 'deposit', 9500.00, NULL, '2025-12-03 16:42:31', 1, 'completed'),
(63, 13, 'deposit', 500.00, NULL, '2025-12-03 16:45:06', 1, 'completed'),
(64, 13, 'withdraw', 500.00, NULL, '2025-12-05 14:40:46', 1, 'completed'),
(65, 21, 'deposit', 1200.00, NULL, '2025-12-05 17:14:26', 1, 'completed'),
(66, 21, 'withdraw', 800.00, NULL, '2025-12-05 17:17:16', 14, 'completed'),
(67, 21, 'withdraw', 400.00, NULL, '2025-12-05 17:18:41', 1, 'completed'),
(68, 22, 'deposit', 5000.00, NULL, '2025-12-05 17:20:29', 1, 'completed'),
(69, 22, 'transfer', 500.00, NULL, '2025-12-05 17:21:11', 14, 'completed'),
(70, 17, 'transfer', 500.00, NULL, '2025-12-05 17:21:11', 14, 'completed'),
(71, 22, 'withdraw', 600.00, NULL, '2025-12-05 17:24:07', 14, 'completed'),
(72, 18, 'withdraw', 100.00, NULL, '2025-12-08 12:40:33', 1, 'completed'),
(73, 24, 'deposit', 500.00, NULL, '2025-12-10 15:00:56', 1, 'completed'),
(74, 24, 'deposit', 100.00, NULL, '2025-12-10 15:02:16', 1, 'completed'),
(75, 11, 'transfer', 40.00, NULL, '2025-12-10 15:02:49', 1, 'completed'),
(76, 3, 'transfer', 40.00, NULL, '2025-12-10 15:02:49', 1, 'completed'),
(77, 31, 'deposit', 500.00, NULL, '2025-12-10 18:20:52', 1, 'completed'),
(78, 13, 'transfer', 100.00, NULL, '2025-12-11 12:16:46', 1, 'completed'),
(79, 31, 'transfer', 100.00, NULL, '2025-12-11 12:16:46', 1, 'completed'),
(80, 13, 'transfer', 100.00, NULL, '2025-12-11 12:23:16', 1, 'completed'),
(81, 31, 'transfer', 100.00, NULL, '2025-12-11 12:23:16', 1, 'completed'),
(82, 31, 'withdraw', 10.00, NULL, '2025-12-11 12:50:51', 1, 'completed'),
(83, 31, 'withdraw', 10.00, NULL, '2025-12-11 12:52:39', 1, 'completed'),
(84, 31, 'deposit', 500.00, NULL, '2025-12-11 13:06:47', 1, 'completed'),
(85, 31, 'withdraw', 30.00, NULL, '2025-12-11 13:16:52', 1, 'completed'),
(86, 31, 'withdraw', 10.00, NULL, '2025-12-11 13:30:32', 1, 'completed'),
(87, 31, 'withdraw', 10.00, NULL, '2025-12-11 14:33:38', 1, 'completed'),
(88, 31, 'deposit', 80.00, NULL, '2025-12-11 14:37:24', 1, 'completed'),
(89, 31, 'transfer', 10.00, NULL, '2025-12-11 14:42:50', 1, 'completed'),
(90, 13, 'transfer', 10.00, NULL, '2025-12-11 14:42:50', 1, 'completed'),
(91, 33, 'deposit', 500.00, NULL, '2025-12-11 17:06:25', 1, 'completed'),
(92, 34, 'deposit', 5000.00, NULL, '2025-12-11 17:06:32', 1, 'completed'),
(93, 33, 'withdraw', 100.00, NULL, '2025-12-11 17:07:13', 1, 'completed'),
(94, 31, 'deposit', 100.00, NULL, '2025-12-11 17:39:19', 1, 'completed'),
(95, 18, 'deposit', 500.00, NULL, '2025-12-11 17:39:44', 1, 'completed'),
(96, 18, 'withdraw', 100.00, NULL, '2025-12-11 17:39:56', 1, 'completed'),
(97, 21, 'withdraw', 500.00, NULL, '2025-12-12 11:12:57', 1, 'completed'),
(98, 21, 'transfer', 100.00, NULL, '2025-12-12 11:14:47', 1, 'completed'),
(99, 23, 'transfer', 100.00, NULL, '2025-12-12 11:14:47', 1, 'completed'),
(100, 21, 'transfer', 100.00, NULL, '2025-12-12 11:18:16', 1, 'completed'),
(101, 23, 'transfer', 100.00, NULL, '2025-12-12 11:18:16', 1, 'completed'),
(102, 21, 'transfer', 100.00, NULL, '2025-12-12 11:19:32', 1, 'completed'),
(103, 23, 'transfer', 100.00, NULL, '2025-12-12 11:19:32', 1, 'completed'),
(104, 21, 'transfer', 100.00, NULL, '2025-12-12 11:19:40', 1, 'completed'),
(105, 23, 'transfer', 100.00, NULL, '2025-12-12 11:19:40', 1, 'completed'),
(106, 21, 'withdraw', 100.00, NULL, '2025-12-12 11:20:53', 1, 'completed'),
(107, 21, 'withdraw', 100.00, NULL, '2025-12-12 11:45:30', 1, 'complete');
