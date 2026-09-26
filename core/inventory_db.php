<?php
/**
 * EIMBox - Inventory & Stock Database Initializer
 * Ensures all required tables and indexes exist with multi-tenant support.
 */

if (!function_exists('init_inventory_database')) {
    function init_inventory_database($conn, $tenant_sccode = null) {
        if (!$conn) return false;

        // 1. Categories Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `category_name` VARCHAR(120) NOT NULL,
            `category_type` ENUM('Asset', 'Consumable', 'SaleItem') NOT NULL DEFAULT 'SaleItem',
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `category_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Units Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_units` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `unit_name` VARCHAR(50) NOT NULL,
            `unit_symbol` VARCHAR(20) NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            INDEX (`sccode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Items Master Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `item_code` VARCHAR(50) NOT NULL,
            `barcode` VARCHAR(80) NULL,
            `item_name` VARCHAR(191) NOT NULL,
            `category_id` INT NOT NULL DEFAULT 0,
            `unit_id` INT NOT NULL DEFAULT 0,
            `item_type` ENUM('Asset', 'Consumable', 'SaleItem') NOT NULL DEFAULT 'SaleItem',
            `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `sale_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `current_stock` INT NOT NULL DEFAULT 0,
            `reorder_level` INT NOT NULL DEFAULT 5,
            `location_rack` VARCHAR(100) NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`sccode`, `item_type`),
            INDEX (`barcode`),
            INDEX (`item_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Suppliers Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_suppliers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `supplier_name` VARCHAR(150) NOT NULL,
            `company_name` VARCHAR(150) NULL,
            `phone` VARCHAR(30) NOT NULL,
            `email` VARCHAR(100) NULL,
            `address` TEXT NULL,
            `opening_balance` DECIMAL(12,2) DEFAULT 0.00,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            INDEX (`sccode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Purchases Master Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_purchases` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `purchase_no` VARCHAR(60) NOT NULL,
            `supplier_id` INT NOT NULL DEFAULT 0,
            `purchase_date` DATE NOT NULL,
            `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `net_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `due_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `payment_method` ENUM('Cash', 'Bank', 'Cheque', 'Due') NOT NULL DEFAULT 'Cash',
            `cashbook_entry_id` INT NULL,
            `remarks` TEXT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `entryby` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `purchase_no`),
            INDEX (`purchase_date`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Ensure status column exists in inv_purchases if table was already created
        $chk_p_status = $conn->query("SHOW COLUMNS FROM `inv_purchases` LIKE 'status'");
        if ($chk_p_status && $chk_p_status->num_rows == 0) {
            $conn->query("ALTER TABLE `inv_purchases` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1 AFTER `remarks`");
        }

        // 6. Purchase Items Details
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_purchase_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `purchase_id` INT NOT NULL,
            `item_id` INT NOT NULL,
            `qty` INT NOT NULL,
            `unit_price` DECIMAL(12,2) NOT NULL,
            `subtotal` DECIMAL(12,2) NOT NULL,
            INDEX (`sccode`, `purchase_id`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Sales Master Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_sales` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `invoice_no` VARCHAR(60) NOT NULL,
            `sale_date` DATE NOT NULL,
            `student_id` INT NULL,
            `student_stid` VARCHAR(50) NULL,
            `customer_name` VARCHAR(150) NOT NULL DEFAULT 'Counter Customer',
            `customer_phone` VARCHAR(30) NULL,
            `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `net_payable` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `change_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `payment_mode` ENUM('Cash', 'bKash', 'Nagad', 'Card', 'StudentFeeAccount') NOT NULL DEFAULT 'Cash',
            `cashbook_entry_id` INT NULL,
            `sale_by` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `invoice_no`),
            INDEX (`sale_date`),
            INDEX (`student_stid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Sales Items Details
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_sale_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `sale_id` INT NOT NULL,
            `item_id` INT NOT NULL,
            `qty` INT NOT NULL,
            `unit_price` DECIMAL(12,2) NOT NULL,
            `purchase_rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `subtotal` DECIMAL(12,2) NOT NULL,
            INDEX (`sccode`, `sale_id`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Departmental Issues Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_issues` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `issue_no` VARCHAR(60) NOT NULL,
            `issue_date` DATE NOT NULL,
            `issued_to_type` ENUM('Teacher', 'Staff', 'Department', 'Classroom') NOT NULL DEFAULT 'Department',
            `issued_to_name` VARCHAR(150) NOT NULL,
            `issued_by` VARCHAR(100) NULL,
            `purpose` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `issue_no`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 10. Issue Items Details
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_issue_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `issue_id` INT NOT NULL,
            `item_id` INT NOT NULL,
            `qty` INT NOT NULL,
            INDEX (`sccode`, `issue_id`),
            INDEX (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 11. Fixed Assets Table
        $conn->query("CREATE TABLE IF NOT EXISTS `fixed_assets` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `asset_tag_code` VARCHAR(80) NOT NULL,
            `asset_name` VARCHAR(191) NOT NULL,
            `category_id` INT NOT NULL DEFAULT 0,
            `purchase_date` DATE NOT NULL,
            `purchase_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `location_room` VARCHAR(120) NOT NULL,
            `custodian_name` VARCHAR(120) NULL,
            `warranty_expiry` DATE NULL,
            `asset_condition` ENUM('Good', 'Under Repair', 'Damaged', 'Disposed') NOT NULL DEFAULT 'Good',
            `depreciation_rate_pct` DECIMAL(5,2) DEFAULT 0.00,
            `current_valuation` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `remarks` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `asset_tag_code`),
            INDEX (`location_room`),
            INDEX (`asset_condition`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 12. Stock Adjustments Log Table
        $conn->query("CREATE TABLE IF NOT EXISTS `inv_stock_adjustments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` INT NOT NULL,
            `item_id` INT NOT NULL,
            `adjustment_type` ENUM('Addition', 'Reduction', 'Damage', 'Audit') NOT NULL,
            `qty` INT NOT NULL,
            `reason` TEXT NULL,
            `adjusted_by` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`sccode`, `item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Auto seed global default units & categories (sccode = 0)
        seed_default_inventory_master_data($conn);

        return true;
    }
}

if (!function_exists('seed_default_inventory_master_data')) {
    function seed_default_inventory_master_data($conn) {
        if (!$conn) return;

        // 1. Seed Global Units (sccode = 0) if none exist
        $chk_u = $conn->query("SELECT COUNT(*) as c FROM inv_units WHERE sccode = 0");
        if ($chk_u && ($r = $chk_u->fetch_assoc()) && intval($r['c']) === 0) {
            $default_units = [
                ['Piece', 'Pcs'],
                ['Packet', 'Pkt'],
                ['Dozen', 'Dzn'],
                ['Set', 'Set'],
                ['Box', 'Box'],
                ['Ream', 'Ream'],
                ['Roll', 'Roll'],
                ['Pair', 'Pair'],
                ['Kilogram', 'Kg'],
                ['Gram', 'gm'],
                ['Litre', 'Ltr'],
                ['Meter', 'Mtr'],
                ['Bundle', 'Bndl']
            ];

            $stmt_u = $conn->prepare("INSERT INTO inv_units (sccode, unit_name, unit_symbol) VALUES (0, ?, ?)");
            foreach ($default_units as $du) {
                $stmt_u->bind_param("ss", $du[0], $du[1]);
                $stmt_u->execute();
            }
        }

        // 2. Seed Global Categories (sccode = 0) if none exist
        $chk_c = $conn->query("SELECT COUNT(*) as c FROM inv_categories WHERE sccode = 0");
        if ($chk_c && ($rc = $chk_c->fetch_assoc()) && intval($rc['c']) === 0) {
            $default_categories = [
                ['Stationery & Writing', 'SaleItem'],
                ['Books & Notebooks', 'SaleItem'],
                ['School Bags & Pouches', 'SaleItem'],
                ['Uniforms, Badges & Ties', 'SaleItem'],
                ['Office & Administrative Supplies', 'Consumable'],
                ['Exam Papers & Answer Sheets', 'Consumable'],
                ['Classroom Teaching Materials', 'Consumable'],
                ['ICT & Computer Equipment', 'Asset'],
                ['Science Lab Apparatus', 'Asset'],
                ['Furniture & Fixtures', 'Asset']
            ];

            $stmt_c = $conn->prepare("INSERT INTO inv_categories (sccode, category_name, category_type) VALUES (0, ?, ?)");
            foreach ($default_categories as $dc) {
                $stmt_c->bind_param("ss", $dc[0], $dc[1]);
                $stmt_c->execute();
            }
        }
    }
}
