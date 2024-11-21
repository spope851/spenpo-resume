CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `default_expanded` tinyint(1) DEFAULT 0,
    `content_type` enum('text','list','nested') NOT NULL,
    `display_order` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `unique_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_section_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `text` text NOT NULL,
    `year` YEAR DEFAULT NULL,
    `link` varchar(255) DEFAULT NULL,
    `year_link` varchar(255) DEFAULT NULL,
    `indent` int(11) DEFAULT 0,
    `display_order` int(11) DEFAULT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_section_text_content` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `label` varchar(255) DEFAULT NULL,
    `text` text NOT NULL,
    `display_order` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_nested_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `link_title` varchar(255) NOT NULL,
    `href` varchar(255) NOT NULL,
    `start_year` YEAR DEFAULT NULL,
    `end_year` YEAR DEFAULT NULL,
    `custom_sub_title` varchar(255),
    `sub_title` varchar(255) GENERATED ALWAYS AS (
        CASE 
            WHEN custom_sub_title IS NOT NULL
                THEN custom_sub_title
            WHEN start_year IS NOT NULL AND end_year IS NOT NULL 
                THEN CONCAT(start_year, ' - ', end_year)
            WHEN start_year IS NOT NULL AND end_year IS NULL
                THEN CONCAT(start_year, ' - present') 
            ELSE NULL
        END
    ) STORED,
    `display_order` int(11) DEFAULT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_nested_section_details` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nested_section_id` int(11) NOT NULL,
    `text` text DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `sub_title` varchar(255) DEFAULT NULL,
    `indent` int(11) DEFAULT 0,
    `display_order` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;