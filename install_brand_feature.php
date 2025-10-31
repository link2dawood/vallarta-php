<?php
// 420 Vallarta - Install Brand Feature
// Run this file once to create brand table and add brand_id to movies

require_once('settings/db.php');

echo "Installing Brand Feature...\n\n";

// Create brand table
$sql_create_brand = "
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
";

if (mysqli_query($con, $sql_create_brand)) {
    echo "✓ Brand table created successfully\n";
} else {
    echo "✗ Error creating brand table: " . mysqli_error($con) . "\n";
}

// Add brand_id to movies table
$sql_add_column = "
ALTER TABLE `movies` 
ADD COLUMN `brand_id` int(11) DEFAULT NULL AFTER `group_id`
";

if (mysqli_query($con, $sql_add_column)) {
    echo "✓ brand_id column added to movies table\n";
} else {
    if (strpos(mysqli_error($con), 'Duplicate column') !== false) {
        echo "✓ brand_id column already exists\n";
    } else {
        echo "✗ Error adding brand_id column: " . mysqli_error($con) . "\n";
    }
}

// Add index on brand_id
$sql_add_index = "ALTER TABLE `movies` ADD KEY `idx_brand_id` (`brand_id`)";
if (mysqli_query($con, $sql_add_index)) {
    echo "✓ Index added on brand_id\n";
} else {
    if (strpos(mysqli_error($con), 'Duplicate key') !== false) {
        echo "✓ Index already exists\n";
    } else {
        echo "✗ Error adding index: " . mysqli_error($con) . "\n";
    }
}

// Insert sample brands
$sample_brands = [
    'Raw Garden',
    'Stiiizy',
    'Cookies',
    'Jeeter',
    'Brass Knuckles',
    'Heavy Hitters',
    'Plug Play',
    'Kurvana',
    'Select',
    'Beboe',
    'PAX',
    'Kingpen',
    'Blue Dream',
    'Gorilla Glue',
    'Girl Scout Cookies'
];

$inserted = 0;
foreach ($sample_brands as $brand) {
    $brand_escaped = mysqli_real_escape_string($con, $brand);
    
    // Check if brand already exists
    $check = mysqli_query($con, "SELECT id FROM brand WHERE brand_name = '$brand_escaped'");
    if (mysqli_num_rows($check) == 0) {
        $sql = "INSERT INTO brand (brand_name, parentOf, added_by) VALUES ('$brand_escaped', NULL, 1)";
        if (mysqli_query($con, $sql)) {
            $inserted++;
        }
    }
}

echo "✓ Inserted $inserted sample brands\n";

echo "\n=== Installation Complete ===\n";
echo "You can now:\n";
echo "1. Manage brands in admin/addbrand.php\n";
echo "2. Assign brands to products\n";
echo "3. Filter by brand on the website\n";

mysqli_close($con);
?>

