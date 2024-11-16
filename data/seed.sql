CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `default_expanded` tinyint(1) DEFAULT 0,
    `content_type` enum('text','list','nested') NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_section_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `text` text NOT NULL,
    `year` varchar(50) DEFAULT NULL,
    `link` varchar(255) DEFAULT NULL,
    `year_link` varchar(255) DEFAULT NULL,
    `indent` int(11) DEFAULT 0,
    PRIMARY KEY  (`id`),
    FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}resume_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_section_text_content` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `label` varchar(255) DEFAULT NULL,
    `text` text NOT NULL,
    PRIMARY KEY  (`id`),
    FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}resume_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_nested_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `section_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `link_title` varchar(255) NOT NULL,
    `href` varchar(255) NOT NULL,
    `sub_title` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`),
    FOREIGN KEY (`section_id`) REFERENCES `{$wpdb->prefix}resume_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}resume_nested_section_details` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nested_section_id` int(11) NOT NULL,
    `text` text DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `sub_title` varchar(255) DEFAULT NULL,
    `indent` int(11) DEFAULT 0,
    PRIMARY KEY  (`id`),
    FOREIGN KEY (`nested_section_id`) REFERENCES `{$wpdb->prefix}resume_nested_sections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- dummy data
-- Insert Skills section
INSERT INTO `{$wpdb->prefix}resume_sections` 
(id, title, default_expanded, content_type) 
VALUES (1, 'Skills', 0, 'text');

INSERT INTO `{$wpdb->prefix}resume_section_text_content` 
(section_id, label, text) VALUES
(1, 'Languages', 'TypeScript, JavaScript, HTML, CSS, Python, SQL'),
(1, 'Software/Frameworks', 'React.js, Next.js, Node.js, Svelte.js, PostgreSQL, SQL Server, MongoDB, .Net, Docker, AWS');

-- Insert Experience section
INSERT INTO `{$wpdb->prefix}resume_sections` 
(id, title, default_expanded, content_type) 
VALUES (2, 'Experience', 1, 'nested');

-- Insert Homeroom Teacher position
INSERT INTO `{$wpdb->prefix}resume_nested_sections`
(section_id, title, link_title, href, sub_title)
VALUES (2, 'Software Engineer', 'Freelance', 'https://www.me.com', '2024 - Present');

INSERT INTO `{$wpdb->prefix}resume_nested_section_details`
(nested_section_id, text) VALUES
(1, 'Built incredible Wordpress plugins with the illustrious and modern PHP');

-- Insert Technical Lead position
INSERT INTO `{$wpdb->prefix}resume_nested_sections`
(section_id, title, link_title, href, sub_title)
VALUES (2, 'Principal Software Engineer', 'IBM', 'https://www.ibm.com', '2012 - 2023');

INSERT INTO `{$wpdb->prefix}resume_nested_section_details`
(nested_section_id, text, title, sub_title) VALUES
(2, "Worked on IBM\'s mainframe systems", NULL, NULL),
(2, NULL, 'Junior Engineer', '2011 - 2012'),
(2, 'Wrote a lot of code', NULL, NULL);

-- Insert Achievements section
INSERT INTO `{$wpdb->prefix}resume_sections` 
(id, title, default_expanded, content_type) 
VALUES (3, 'Achievements', 0, 'list');

INSERT INTO `{$wpdb->prefix}resume_section_items`
(section_id, text, year) VALUES
(3, 'Built this incredible Wordpress plugin', '2024'),
(3, 'First-degree black belt in karate', '2011');

-- Insert Certifications section
INSERT INTO `{$wpdb->prefix}resume_sections` 
(id, title, default_expanded, content_type) 
VALUES (4, 'Certifications', 0, 'list');

INSERT INTO `{$wpdb->prefix}resume_section_items`
(section_id, text, link, year, year_link) VALUES
(4, 'AWS Certified Cloud Practitioner', 'https://aws.amazon.com/certification/certified-cloud-practitioner/', '2024', 'https://www.credly.com');

-- Insert Formal Education section
INSERT INTO `{$wpdb->prefix}resume_sections` 
(id, title, default_expanded, content_type) 
VALUES (5, 'Formal Education', 0, 'nested');

-- Insert UNH education
INSERT INTO `{$wpdb->prefix}resume_nested_sections`
(section_id, title, link_title, href, sub_title)
VALUES (5, 'BS, Business Admin', 'University of New Hampshire', 'https://unh.edu', '2015 - 2019');

INSERT INTO `{$wpdb->prefix}resume_nested_section_details`
(nested_section_id, text, indent) VALUES
(3, 'Studied Information Technology as a minor with the College of Engineering and Physical Sciences', 0),
(3, 'Courses on web development, DBMS, coding in Python, internet protocols, and computer architecture', 5);

-- Insert BLCU education
INSERT INTO `{$wpdb->prefix}resume_nested_sections`
(section_id, title, link_title, href, sub_title)
VALUES (5, 'Certificate in Mandarin', 'Beijing Language and Culture University', 'http://english.blcu.edu.cn/', 'January 2019');

INSERT INTO `{$wpdb->prefix}resume_nested_section_details`
(nested_section_id, text) VALUES
(4, '80 hours with the College of Intensive Chinese Language Studies');