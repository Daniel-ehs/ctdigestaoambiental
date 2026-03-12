CREATE TABLE `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` VARCHAR(45) COLLATE utf8_unicode_ci NOT NULL,
  `attempt_time` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `email_idx` (`email`),
  INDEX `ip_address_idx` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

