-- ============================================
-- 420 Vallarta - Database Updates
-- Add Brand Feature
-- ============================================

-- Create brand table (hierarchical like cat and grp)
CREATE TABLE IF NOT EXISTS `brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `parentOf` int(11) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_parentOf` (`parentOf`),
  KEY `idx_added_by` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add brand_id column to movies table
ALTER TABLE `movies` 
ADD COLUMN `brand_id` int(11) DEFAULT NULL AFTER `group_id`,
ADD KEY `idx_brand_id` (`brand_id`);

-- Insert some sample brands (popular cannabis brands)
INSERT INTO `brand` (`id`, `brand_name`, `parentOf`, `added_by`) VALUES
(1, 'Raw Garden', NULL, 1),
(2, 'Stiiizy', NULL, 1),
(3, 'Cookies', NULL, 1),
(4, 'Jeeter', NULL, 1),
(5, 'Brass Knuckles', NULL, 1),
(6, 'Heavy Hitters', NULL, 1),
(7, 'Plug Play', NULL, 1),
(8, 'Kurvana', NULL, 1),
(9, 'Select', NULL, 1),
(10, 'Beboe', NULL, 1);

-- Add foreign key constraints (optional, for data integrity)
-- Uncomment if you want strict referential integrity
-- ALTER TABLE `movies` ADD CONSTRAINT `fk_movies_brand` 
--   FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `brand` ADD CONSTRAINT `fk_brand_parent` 
--   FOREIGN KEY (`parentOf`) REFERENCES `brand` (`id`) ON DELETE CASCADE;

