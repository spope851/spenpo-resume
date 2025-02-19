-- dummy data
-- Insert Skills section
INSERT INTO `{$wpdb->prefix}spcv_resume_sections` 
(id, title, default_expanded, content_type, display_order) 
VALUES (1, 'Skills', 0, 'text', 10);

INSERT INTO `{$wpdb->prefix}spcv_resume_section_text_content` 
(section_id, label, text, display_order) VALUES
(1, 'Languages', 'TypeScript, JavaScript, HTML, CSS, Python, SQL', 10),
(1, 'Software/Frameworks', 'React.js, Next.js, Node.js, Svelte.js, PostgreSQL, SQL Server, MongoDB, .Net, Docker, AWS', 20);

-- Insert Experience section
INSERT INTO `{$wpdb->prefix}spcv_resume_sections` 
(id, title, default_expanded, content_type, display_order) 
VALUES (2, 'Experience', 1, 'nested', 20);

-- Insert Freelancer position
INSERT INTO `{$wpdb->prefix}spcv_resume_nested_sections`
(section_id, title, link_title, href, start_year, end_year)
VALUES (2, 'Software Engineer', 'Freelance', 'https://www.me.com', 2024, NULL);

INSERT INTO `{$wpdb->prefix}spcv_resume_nested_section_details`
(nested_section_id, text, display_order) VALUES
(1, 'Built incredible Wordpress plugins with the illustrious and modern PHP', 10),
(1, 'Used Cursor to generate amazing resume copy', 20),
(1, 'Deployed a Wordpress site to AWS for some reason', 30);

-- Insert Principal position
INSERT INTO `{$wpdb->prefix}spcv_resume_nested_sections`
(section_id, title, link_title, href, start_year, end_year)
VALUES (2, 'Principal Software Engineer', 'IBM', 'https://www.ibm.com', 2012, 2023);

INSERT INTO `{$wpdb->prefix}spcv_resume_nested_section_details`
(nested_section_id, text, title, sub_title, indent, display_order) VALUES
(2, "Worked on IBM\'s mainframe systems", NULL, NULL, NULL, 10),
(2, "Developed a lot of code based on the IBM mainframe", NULL, NULL, NULL, 20),
(2, "Engineered many innovative solutions to complex problems", NULL, NULL, NULL, 30),
(2, "Mentored many junior developers", NULL, NULL, NULL, 40),
(2, NULL, 'Junior Engineer', '2011 - 2012', 5, 50),
(2, 'Endured the horrors of the cubicle farm', NULL, NULL, NULL, 60),
(2, 'Wrote a lot of code', NULL, NULL, NULL, 70),
(2, 'Learned a lot about the mainframe', NULL, NULL, NULL, 80),
(2, 'Brewed a lot of coffee', NULL, NULL, NULL, 90);

-- Insert Achievements section
INSERT INTO `{$wpdb->prefix}spcv_resume_sections` 
(id, title, default_expanded, content_type, display_order) 
VALUES (3, 'Achievements', 0, 'list', 30);

INSERT INTO `{$wpdb->prefix}spcv_resume_section_items`
(section_id, text, year) VALUES
(3, 'Built this incredible Wordpress plugin', 2024),
(3, 'First-degree black belt in karate', 2011);

-- Insert Certifications section
INSERT INTO `{$wpdb->prefix}spcv_resume_sections` 
(id, title, default_expanded, content_type, display_order) 
VALUES (4, 'Certifications', 0, 'list', 40);

INSERT INTO `{$wpdb->prefix}spcv_resume_section_items`
(section_id, text, link, year, year_link) VALUES
(4, 'AWS Certified Cloud Practitioner', 'https://aws.amazon.com/certification/certified-cloud-practitioner/', 2024, 'https://www.credly.com');

-- Insert Formal Education section
INSERT INTO `{$wpdb->prefix}spcv_resume_sections` 
(id, title, default_expanded, content_type, display_order) 
VALUES (5, 'Formal Education', 0, 'nested', 50);

-- Insert UNH education
INSERT INTO `{$wpdb->prefix}spcv_resume_nested_sections`
(section_id, title, link_title, href, start_year, end_year, display_order)
VALUES (5, 'BS, Business Admin', 'University of New Hampshire', 'https://unh.edu', 2015, 2019, 10);

INSERT INTO `{$wpdb->prefix}spcv_resume_nested_section_details`
(nested_section_id, text, indent, display_order) VALUES
(3, 'Studied Information Technology as a minor with the College of Engineering and Physical Sciences', 0, 10),
(3, 'Courses on web development, DBMS, coding in Python, internet protocols, and computer architecture', 5, 20);

-- Insert BLCU education
INSERT INTO `{$wpdb->prefix}spcv_resume_nested_sections`
(section_id, title, link_title, href, start_year, end_year, custom_sub_title, display_order)
VALUES (5, 'Certificate, Mandarin', 'Beijing Language and Culture University', 'http://english.blcu.edu.cn/', 2018, 2019, 'January 2019', 20);

INSERT INTO `{$wpdb->prefix}spcv_resume_nested_section_details`
(nested_section_id, text, display_order) VALUES
(4, '80 hours with the College of Intensive Chinese Language Studies', 10);
