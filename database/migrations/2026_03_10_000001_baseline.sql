-- Generated on 2026-03-09 17:31:00
-- Source database: aiticoresms-lite
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

--
-- Table structure for `article`
--
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug_article` mediumtext DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `subkat_id` int(11) DEFAULT NULL,
  `images` mediumtext DEFAULT NULL,
  `content` longtext NOT NULL,
  `content_en` mediumtext DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  `updated_at` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `publish` varchar(255) NOT NULL,
  `comment_active` varchar(2) NOT NULL,
  `counter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
INSERT INTO `article` (`id`, `slug_article`, `user_id`, `title`, `title_en`, `category_id`, `subkat_id`, `images`, `content`, `content_en`, `tags`, `created_at`, `updated_at`, `deleted_at`, `publish`, `comment_active`, `counter`) VALUES
(11, 'otomasi-workflow-untuk-tim-sales-dummy', 2, 'Otomasi Workflow untuk Tim Sales', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang otomasi follow up, pipeline, dan approval internal tim sales.</p>', NULL, 'erp,website', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 7),
(13, 'strategi-support-teknis-yang-terukur-dummy', 3, 'Strategi Support Teknis yang Terukur', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang SLA, alur support, dan dashboard monitoring tiket.</p>', NULL, 'support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 2),
(15, 'checklist-go-live-aplikasi-operasional-dummy', 3, 'Checklist Go-Live Aplikasi Operasional', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang persiapan teknis sebelum aplikasi operasional dipakai tim harian.</p>', NULL, 'otomasi,support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 1),
(17, 'strategi-support-teknis-yang-terukur-dummy', 3, 'Strategi Support Teknis yang Terukur', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang SLA, alur support, dan dashboard monitoring tiket.</p>', NULL, 'support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 2),
(16, 'otomasi-workflow-untuk-tim-sales-dummy', 2, 'Otomasi Workflow untuk Tim Sales', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang otomasi follow up, pipeline, dan approval internal tim sales.</p>', NULL, 'erp,website', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 7),
(1, 'otomasi-workflow-untuk-tim-sales-dummy', 2, 'Otomasi Workflow untuk Tim Sales', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang otomasi follow up, pipeline, dan approval internal tim sales.</p>', NULL, 'erp,website', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 7),
(2, 'strategi-support-teknis-yang-terukur-dummy', 3, 'Strategi Support Teknis yang Terukur', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang SLA, alur support, dan dashboard monitoring tiket.</p>', NULL, 'support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 2),
(3, 'checklist-go-live-aplikasi-operasional-dummy', 3, 'Checklist Go-Live Aplikasi Operasional', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang persiapan teknis sebelum aplikasi operasional dipakai tim harian.</p>', NULL, 'otomasi,support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 1),
(4, 'membangun-company-profile-yang-jualan-dummy', 2, 'Membangun Company Profile yang Jualan', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang struktur website company profile yang fokus pada konversi lead.</p>', NULL, 'website', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 0),
(5, 'migrasi-sistem-erp-untuk-distribusi-dummy', 3, 'Migrasi Sistem ERP untuk Distribusi', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang migrasi ERP untuk operasional distribusi dan kontrol stok lintas gudang.</p>', NULL, 'erp,otomasi', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 0),
(18, 'checklist-go-live-aplikasi-operasional-dummy', 3, 'Checklist Go-Live Aplikasi Operasional', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang persiapan teknis sebelum aplikasi operasional dipakai tim harian.</p>', NULL, 'otomasi,support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 1),
(19, 'checklist-go-live-aplikasi-operasional-dummy', 3, 'Checklist Go-Live Aplikasi Operasional', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang persiapan teknis sebelum aplikasi operasional dipakai tim harian.</p>', NULL, 'otomasi,support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 1),
(20, 'otomasi-workflow-untuk-tim-sales-dummy', 2, 'Otomasi Workflow untuk Tim Sales', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang otomasi follow up, pipeline, dan approval internal tim sales.</p>', NULL, 'erp,website', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 7),
(21, 'strategi-support-teknis-yang-terukur-dummy', 3, 'Strategi Support Teknis yang Terukur', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang SLA, alur support, dan dashboard monitoring tiket.</p>', NULL, 'support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 2),
(22, 'checklist-go-live-aplikasi-operasional-dummy', 3, 'Checklist Go-Live Aplikasi Operasional', NULL, 24, NULL, '/assets/img/dummy-article.svg', '<p>Artikel dummy tentang persiapan teknis sebelum aplikasi operasional dipakai tim harian.</p>', NULL, 'otomasi,support', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL, 'P', 'Y', 1);

--
-- Table structure for `category`
--
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_category` varchar(255) DEFAULT NULL,
  `img_category` varchar(255) DEFAULT NULL,
  `slug_category` varchar(255) DEFAULT NULL,
  `info_category` int(11) DEFAULT NULL,
  `ket_category` text DEFAULT NULL,
  `urutan` int(11) DEFAULT NULL,
  `url_category` text DEFAULT NULL,
  `meta_lang` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `category` (`id`, `name_category`, `img_category`, `slug_category`, `info_category`, `ket_category`, `urutan`, `url_category`, `meta_lang`, `user_id`, `created_at`, `updated_at`) VALUES
(24, 'Artikel', '/assets/img/dummy-cover.svg', 'blog', 1, 'Kumpulan artikel dummy untuk kebutuhan pengembangan.', 3, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(25, 'Tutorial', '/assets/img/dummy-cover.svg', 'tutorial', 2, 'Kategori tutorial dummy.', 4, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(30, 'Jelajahi Produk', '/assets/img/dummy-cover.svg', 'jelajahi-produk', 2, 'Etalase produk digital dummy.', 1, NULL, 'Indonesia', 1, '2026-03-09 06:45:14', '2026-03-09 06:45:14'),
(31, 'Layanan', '/assets/img/dummy-cover.svg', 'layanan-aitisolutions', 3, 'Halaman layanan Aiti-Solutions.', 2, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(32, 'Support', '/assets/img/dummy-cover.svg', 'support', 3, 'Halaman support Aiti-Solutions.', 6, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(33, 'Perusahaan', '/assets/img/dummy-cover.svg', 'perusahaan', 3, 'Informasi perusahaan Aiti-Solutions.', 5, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(34, 'Legal', '/assets/img/dummy-cover.svg', 'legal', 3, 'Informasi legal dummy.', 7, NULL, 'Indonesia', 1, '2026-03-09 16:38:18', '2026-03-09 16:38:18'),
(35, 'Produk', '/assets/img/dummy-cover.svg', 'produk-aitisolutions', 3, 'Kategori produk utama Aiti-Solutions.', 8, NULL, 'Indonesia', 1, '2026-03-09 06:45:14', '2026-03-09 06:45:14');

--
-- Table structure for `category_sub`
--
DROP TABLE IF EXISTS `category_sub`;
CREATE TABLE `category_sub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `slug_sub` varchar(255) DEFAULT NULL,
  `name_sub` varchar(255) DEFAULT NULL,
  `url_sub` varchar(255) DEFAULT NULL,
  `img_sub` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `ket_sub` text DEFAULT NULL,
  `urutan` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for `comment`
--
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(11) DEFAULT NULL,
  `html` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
INSERT INTO `comment` (`id`, `active`, `html`, `created_at`, `updated_at`) VALUES
(1, 1, '<p>Kolom komentar aktif untuk demo. Gunakan data ini untuk pengujian artikel dan halaman.</p>', '2026-03-09 16:38:18', '2026-03-09 16:38:18');

--
-- Table structure for `file_album`
--
DROP TABLE IF EXISTS `file_album`;
CREATE TABLE `file_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_album` varchar(255) DEFAULT NULL,
  `info_album` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `file_album` (`id`, `name_album`, `info_album`, `created_at`, `updated_at`, `users_id`) VALUES
(1, 'Uncategory', 'berisi tentang file tidak berkategori', '2026-03-09 06:02:20', NULL, 1),
(2, 'Uncategory', 'berisi tentang file tidak berkategori', '2026-03-09 07:34:25', NULL, 3),
(3, 'Artikel', 'Artikel Demo Aiticms-Lite', '2026-03-09 17:20:11', NULL, 3);

--
-- Table structure for `file_manager`
--
DROP TABLE IF EXISTS `file_manager`;
CREATE TABLE `file_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `info_file` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(255) DEFAULT NULL,
  `dir_file` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `album_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `file_manager` (`id`, `info_file`, `file_name`, `file_size`, `dir_file`, `type`, `extension`, `created_at`, `updated_at`, `album_id`, `users_id`) VALUES
(1, '-', 'article_1.webp', '10036', '4113e2ba388c4700399ccc3ae9a5d947.webp', 'Image', 'webp', '2026-03-09 17:20:56', NULL, 3, 3),
(2, '-', 'kompres_aiti-solutions_wp.jpg', '80349', 'c8ce35b879f27a2112180572c1aaada7.jpg', 'Image', 'jpg', '2026-03-09 17:21:21', NULL, 3, 3);

--
-- Table structure for `information`
--
DROP TABLE IF EXISTS `information`;
CREATE TABLE `information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_website` varchar(255) DEFAULT NULL,
  `url_default` varchar(255) DEFAULT NULL,
  `meta_author` varchar(255) DEFAULT NULL,
  `google_site` varchar(255) DEFAULT NULL,
  `meta_keyword` text DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_lang` varchar(25) DEFAULT NULL,
  `footer` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(25) DEFAULT NULL,
  `facebook` text DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `api_ongkir` text DEFAULT NULL,
  `meta_logo` varchar(255) DEFAULT NULL,
  `meta_icon` varchar(255) DEFAULT NULL,
  `meta_image` varchar(255) DEFAULT NULL,
  `footer_show_frontpage` tinyint(1) NOT NULL DEFAULT 1,
  `footer_show_articles` tinyint(1) NOT NULL DEFAULT 1,
  `footer_show_pages` tinyint(1) NOT NULL DEFAULT 1,
  `footer_page_category_id` int(11) DEFAULT NULL,
  `footer_page_category_id_2` int(11) DEFAULT NULL,
  `footer_page_category_id_3` int(11) DEFAULT NULL,
  `footer_page_category_id_4` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `embed_js` text DEFAULT NULL,
  `gmaps` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `base_color` varchar(255) DEFAULT NULL,
  `second_color` varchar(255) DEFAULT NULL,
  `active_theme` varchar(120) NOT NULL DEFAULT 'aiti-themes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `information` (`id`, `title_website`, `url_default`, `meta_author`, `google_site`, `meta_keyword`, `meta_description`, `meta_lang`, `footer`, `whatsapp`, `facebook`, `twitter`, `instagram`, `linkedin`, `youtube`, `email`, `phone`, `api_ongkir`, `meta_logo`, `meta_icon`, `meta_image`, `footer_show_frontpage`, `footer_show_articles`, `footer_show_pages`, `footer_page_category_id`, `footer_page_category_id_2`, `footer_page_category_id_3`, `footer_page_category_id_4`, `address`, `embed_js`, `gmaps`, `created_at`, `updated_at`, `base_color`, `second_color`, `active_theme`) VALUES
(1, 'Aiticms-Lite', 'http://127.0.0.1/aiticore-cms/public', 'Aiti-Solutions', NULL, 'CMS ringan, fleksibel, dan siap untuk Blog, News, Compro, hingga Landing Page', 'Aiticms-Lite adalah CMS ringan dari Aiti-Solutions yang mendukung pembuatan Blog, Portal Berita, Company Profile, dan Landing Page statis. Dirancang cepat, responsif, serta mudah dikustomisasi untuk kebutuhan website modern.', 'id', '{year}. {site_name}. All rights reserved', '6281234567890', 'https://facebook.com/aitisolutions', NULL, 'https://instagram.com/aitisolutions', 'https://linkedin.com/company/aitisolutions', 'https://youtube.com/@aitisolutions', 'admin@mail.com', '6281234567890', NULL, '/assets/img/dummy-logo.svg', '/assets/img/dummy-logo.svg', '/assets/img/dummy-cover.svg', 1, 1, 1, 31, 32, 33, 34, 'Yogyakarta, Indonesia', NULL, NULL, '2026-03-09 16:38:18', '2026-03-09 16:38:24', '#0f766e', '#f59e0b', 'aiti-themes');

--
-- Table structure for `pages`
--
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug_page` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `content_en` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `counter` int(11) DEFAULT NULL,
  `publish` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pages_category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
INSERT INTO `pages` (`id`, `slug_page`, `user_id`, `category_id`, `title`, `content`, `title_en`, `content_en`, `images`, `counter`, `publish`, `created_at`, `updated_at`, `deleted_at`) VALUES
(32, 'syarat-dan-ketentuan-dummy', 1, 34, 'Syarat dan Ketentuan', '<p>Halaman dummy syarat dan ketentuan penggunaan layanan.</p>', NULL, NULL, '/storage/filemanager/3/3/c8ce35b879f27a2112180572c1aaada7.jpg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 17:21:32', NULL),
(31, 'kebijakan-privasi-dummy', 1, 34, 'Kebijakan Privasi', '<p>Halaman dummy legal untuk pengujian footer menu legal.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(30, 'tim-dan-budaya-kerja-dummy', 1, 33, 'Tim dan Budaya Kerja', '<p>Halaman dummy yang menjelaskan budaya kolaborasi, kualitas delivery, dan ownership tim Aiti-Solutions.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(29, 'tentang-aiti-solutions-dummy', 1, 33, 'Tentang Aiti-Solutions', '<p>Halaman dummy profil perusahaan yang menjelaskan fokus layanan, nilai kerja, dan pendekatan tim.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(28, 'support-konsultasi-dummy', 1, 32, 'Support Konsultasi', '<p>Halaman dummy konsultasi teknis untuk maintenance, bug fixing, dan optimasi performa.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(25, 'layanan-pembuatan-website-dummy', 1, 31, 'Layanan Pembuatan Website', '<p>Halaman dummy untuk layanan pembuatan website bisnis, company profile, dan landing page.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(26, 'layanan-pengembangan-aplikasi-dummy', 1, 31, 'Layanan Pengembangan Aplikasi', '<p>Halaman dummy untuk layanan pengembangan aplikasi web, mobile, dan sistem internal perusahaan.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL),
(27, 'support-dokumentasi-dummy', 1, 32, 'Support Dokumentasi', '<p>Halaman dummy dokumentasi implementasi, onboarding, dan panduan penggunaan sistem.</p>', NULL, NULL, '/assets/img/dummy-page.svg', 0, 'Publish', '2026-03-09 16:38:18', '2026-03-09 16:38:18', NULL);

--
-- Table structure for `plugins`
--
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` varchar(50) NOT NULL DEFAULT '1.0.0',
  `description` text DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'upload',
  `installed_path` varchar(500) DEFAULT NULL,
  `manifest_json` longtext DEFAULT NULL,
  `capabilities` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugins_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for `slider`
--
DROP TABLE IF EXISTS `slider`;
CREATE TABLE `slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img_slider` varchar(255) DEFAULT NULL,
  `title_slider` varchar(255) DEFAULT NULL,
  `meta_lang` varchar(25) DEFAULT NULL,
  `button_slider` varchar(255) DEFAULT NULL,
  `url_slider` text DEFAULT NULL,
  `content_slider` text DEFAULT NULL,
  `urutan` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for `tags`
--
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug_tags` varchar(255) DEFAULT NULL,
  `name_tags` varchar(255) DEFAULT NULL,
  `info_tags` text DEFAULT NULL,
  `photo_tags` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
INSERT INTO `tags` (`id`, `slug_tags`, `name_tags`, `info_tags`, `photo_tags`, `created_at`, `updated_at`, `user_id`) VALUES
(16, 'erp', 'ERP', 'Tag dummy ERP.', '/assets/img/dummy-cover.svg', '2026-03-09 16:38:18', '2026-03-09 16:38:18', 1),
(17, 'pos', 'POS', 'Tag dummy POS.', '/assets/img/dummy-cover.svg', '2026-03-09 16:38:18', '2026-03-09 16:38:18', 1),
(18, 'website', 'Website', 'Tag dummy Website.', '/assets/img/dummy-cover.svg', '2026-03-09 16:38:18', '2026-03-09 16:38:18', 1),
(19, 'otomasi', 'Otomasi', 'Tag dummy Otomasi.', '/assets/img/dummy-cover.svg', '2026-03-09 16:38:18', '2026-03-09 16:38:18', 1),
(20, 'support', 'Support', 'Tag dummy Support.', '/assets/img/dummy-cover.svg', '2026-03-09 16:38:18', '2026-03-09 16:38:18', 1);

--
-- Table structure for `themes`
--
DROP TABLE IF EXISTS `themes`;
CREATE TABLE `themes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` varchar(50) NOT NULL DEFAULT '1.0.0',
  `description` text DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'upload',
  `installed_path` varchar(500) DEFAULT NULL,
  `manifest_json` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `themes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=373 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `themes` (`id`, `slug`, `name`, `version`, `description`, `source`, `installed_path`, `manifest_json`, `is_active`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'aiti-themes', 'aiti-themes', '1.0.0', 'Tema bawaan internal untuk Aiticms-Lite.', 'builtin', 'internal', '{\"slug\":\"aiti-themes\",\"name\":\"aiti-themes\",\"version\":\"1.0.0\",\"description\":\"Tema bawaan internal untuk Aiticms-Lite.\"}', 1, 1, '2026-03-09 17:29:02', '2026-03-09 16:41:30'),
(358, 'company-profile-bootstrap', 'Company Profile Bootstrap', '1.0.1', 'Tema company profile untuk bisnis jasa, software house, agensi, dan konsultan.', 'upload', 'D:\\Flyenv\\www\\aiticms-lite\\storage/themes\\company-profile-bootstrap', '{\"slug\":\"company-profile-bootstrap\",\"name\":\"Company Profile Bootstrap\",\"version\":\"1.0.1\",\"description\":\"Tema company profile untuk bisnis jasa, software house, agensi, dan konsultan.\",\"screenshot\":\"assets/screenshot.webp\",\"templates\":{\"home\":\"templates/home.html\",\"page\":\"templates/page.html\"},\"assets\":{\"css\":[\"assets/theme.css\"],\"js\":[\"assets/theme.js\"]}}', 0, 0, '2026-03-09 16:35:56', '2026-03-09 16:41:30'),
(371, 'dot-slash-theme', 'Dot Slash Theme', '1.0.0', 'Tema tambahan hasil upload ZIP.', 'upload', 'C:\\Users\\afandev\\AppData\\Local\\Temp\\aiti-theme-upload-test-ca4e371703cf\\themes\\dot-slash-theme', '{\"name\":\"Dot Slash Theme\",\"slug\":\"dot-slash-theme\",\"version\":\"1.0.0\"}', 0, 0, '2026-03-09 16:45:03', '2026-03-09 17:08:09');

--
-- Table structure for `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `lang` varchar(14) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `roles` int(11) NOT NULL,
  `tim` int(11) DEFAULT NULL,
  `active` int(11) NOT NULL,
  `web` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users` (`id`, `name`, `username`, `email`, `avatar`, `phone`, `address`, `lang`, `description`, `email_verified_at`, `password`, `roles`, `tim`, `active`, `web`, `facebook`, `twitter`, `linkedin`, `youtube`, `instagram`, `github`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', 'admin@mail.com', '/assets/img/dummy-avatar.svg', '6281234567890', 'Yogyakarta, Indonesia', 'id', 'Akun administrator hasil seed migrasi.', '2026-03-09 16:38:18', '$2y$10$vSJryv1UIkdFi5dhuqn1Aed1Fom5u8tXIPhYHIVE3DQ.I5xc6I9va', 1, 1, 1, 'https://aiti-solutions.test', NULL, NULL, NULL, NULL, NULL, 'https://github.com/aiti-solutions', NULL, '2026-03-09 06:45:14', '2026-03-09 16:38:18'),
(2, 'Fauzan (Codekop)', 'fauzan', 'admin@admin.com', '/assets/img/dummy-avatar-fauzan.svg', '6281111111111', 'Bandung, Indonesia', 'id', 'Dummy author seed untuk artikel demo.', '2026-03-09 16:38:18', '$2y$10$vSJryv1UIkdFi5dhuqn1Aed1Fom5u8tXIPhYHIVE3DQ.I5xc6I9va', 2, 1, 1, 'https://codekop.com', NULL, NULL, 'https://www.linkedin.com/in/fauzan-f-20055913a/', NULL, NULL, 'https://github.com/fauzan1892', '7kM9pRZgPWoK7g7TXRxnD0GND494Yy1rbKiArDpW7MQeb6PBU7HQfZCPY4Dv', '2021-08-27 23:24:46', '2026-03-09 16:38:18'),
(3, 'Afan (aitisolutions)', 'afandev', 'afandev@aol.com', '/assets/img/dummy-avatar-afan.svg', '6282222222222', 'Yogyakarta, Indonesia', 'id', 'Dummy author seed untuk artikel demo.', '2026-03-09 16:38:18', '$2y$10$vSJryv1UIkdFi5dhuqn1Aed1Fom5u8tXIPhYHIVE3DQ.I5xc6I9va', 2, 1, 1, 'https://aiti-solutions.com', NULL, NULL, 'https://www.linkedin.com/in/aiti-solutions', NULL, NULL, 'https://github.com/afandisini', 'rWVkEMyrZGO0enuuq02OiLRCEZkHZZDvrQfftHno1rqXYbDqx72n59wOlTNl', '2021-08-27 23:37:54', '2026-03-09 16:38:18');

--
-- Table structure for `users_role`
--
DROP TABLE IF EXISTS `users_role`;
CREATE TABLE `users_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_role` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
INSERT INTO `users_role` (`id`, `name_role`, `created_at`, `updated_at`) VALUES
(1, 'Administrators', '2026-03-09 06:45:14', '2026-03-09 16:38:18'),
(2, 'Users', '2026-03-09 06:45:14', '2026-03-09 16:38:18'),
(3, 'Operators', '2026-03-09 14:51:17', '2026-03-09 16:38:18'),
(4, 'Authors', '2026-03-09 14:51:17', '2026-03-09 16:38:18'),
(5, 'VIP', '2026-03-09 14:51:17', '2026-03-09 16:38:18');

SET FOREIGN_KEY_CHECKS=1;
