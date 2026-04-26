-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 02:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartsupplysolutions`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_visits`
--

CREATE TABLE `daily_visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_status` varchar(20) DEFAULT NULL,
  `follow_up_done_at` datetime DEFAULT NULL,
  `follow_up_action_note` text DEFAULT NULL,
  `area` varchar(190) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `clinic_name` varchar(255) NOT NULL,
  `visit_number` varchar(20) NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `job_title` varchar(190) NOT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `interest` varchar(50) NOT NULL,
  `visit_type` varchar(190) NOT NULL,
  `visit_result` varchar(190) NOT NULL,
  `execution_status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_visits`
--

INSERT INTO `daily_visits` (`id`, `user_id`, `visit_date`, `follow_up_date`, `follow_up_status`, `follow_up_done_at`, `follow_up_action_note`, `area`, `address`, `clinic_name`, `visit_number`, `person_name`, `job_title`, `mobile`, `interest`, `visit_type`, `visit_result`, `execution_status`, `notes`, `created_at`) VALUES
(4, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Dr Huda Al Safar', '2', 'yasmin', '', '99415010', 'عالي', 'متابعة', 'مهتم', 'متابعة', '• 11/02A4:O4/2026: كتير مهتمة ولازم عيد عليها واتصال بها\n• 12/04/2026: اتصل للموعد - yasmin 99415010 - rim 99915180', '2026-04-22 14:14:03'),
(5, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 3', 'Eterna 3', '3', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'مهتم', 'متابعة', '• 15/02/2026: اجريت موعد وقال الدكتور سننظر للامر\n• 11/02/2026: الاحد ساعة 3\n• 12/04/2026: لح ياخد مني سيناشور باقرب وقت احكيه ب 16.4.2026', '2026-04-22 14:14:03'),
(6, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Shaab', 'F. 13', 'Cure Clinic', '2', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'تم', 'تم', '• 12/02/2026: اخدت عينات السيناشور لح تعمل سبونسر وتشوف التفاعل\n• 20/04/2026: لح ياخد عن قريب', '2026-04-22 14:14:03'),
(7, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 9', 'Nova', '1', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'عرض سعر', 'متابعة', '• 15/03/2026: دكتور جمال عبد الرازق سيطلب فيما بعد يريد سعر مميز - ramadan', '2026-04-22 14:14:03'),
(8, 1, '2026-02-16', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', 'F. 17', 'Cure Clinic', '1', 'غير محدد', '', '66975660', 'عالي', 'عرض منتج', 'طلب عرض سعر', 'متابعة', '• 16/02/2026: طلب مني عرض سعر ب 5 بوكسات جورج', '2026-04-22 14:14:03'),
(9, 1, '2026-02-16', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 13', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'أخذ عينة', 'متابعة', '• 16/02/2026: اخدت عينات واحبت المنتج سيتم الطلب عن قريب', '2026-04-22 14:14:03'),
(10, 1, '2026-02-16', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 16', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'مهتم', 'متابعة', '• 16/02/2026: الختيارة انصدمت قالت سعرنا جيد بس تخلص بتطلب', '2026-04-22 14:14:03'),
(11, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Mazaya Bneid Al', 'Gar', 'beauty clinic-', '1', 'غير محدد', '', NULL, 'عالي', 'عرض منتج', 'مهتم', 'متابعة', '• 20/04/2026: حبو المنتج كثيرا لح يحكو الادارة - مهتمة جدا', '2026-04-22 14:14:03'),
(12, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Sherifa Al Awadi', '1', 'غير محدد', '', '94975755', 'متوسط', 'متابعة', 'مهتم', 'متابعة', '• 11/02/2026: filipin candella call me\n• 12/04/2026: اهتمت وابعت لها على الواتس', '2026-04-22 14:14:03'),
(13, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Al Andalus', '3', 'غير محدد', '', '94471604', 'متوسط', 'عرض منتج', 'موعد', 'متابعة', '• 11/02/2026: اتصل واخذ موعد كانديلا\n• 15/02/2026: قال الدكتور سيتصل بي - My Skin Andalos\n• 12/04/2026: يجب الاتصال اخذ موعد', '2026-04-22 14:14:03'),
(14, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Renew', '3', 'غير محدد', '', NULL, 'متوسط', 'متابعة', 'موعد', 'متابعة', '• 11/02/2026: الثلاثاء ساعة 3\n• 17/02/2026: الثلاثاء قالت اجي بعد رمضان بس لازم اجي ب 24.2.2026\n• 12/04/2026: appointment talete se3a 3', '2026-04-22 14:14:03'),
(15, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'My Skin', '2', 'غير محدد', '', '1805544', 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 11/02/2026: زيارة مرة اخرى\n Gherfe Tenye 2 • 12/04/2026: مرة اخرى مشغول', '2026-04-22 14:14:03'),
(16, 1, '2026-02-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Shaab', NULL, 'Cure Clinic 8-Shaab', '1', 'غير محدد', '', '66821232', 'متوسط', 'عرض منتج', 'مهتم', 'متابعة', '• 12/02/2026: احبت الفكرة كتير و عندهن سيناشور لازم يجربو', '2026-04-22 14:14:03'),
(17, 1, '2026-02-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 10', 'Cure Clinic', '3', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'عرض', 'متابعة', '• 19/08/2025: After 4PM\n• 28/08/2025: doctor msefar\n• 12/02/2026: اخدت البرشور وقالت عندهن منها ستعرضها على الدكتور', '2026-04-22 14:14:03'),
(18, 1, '2026-02-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 11', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 12/02/2026: قالت المسوول بطابق 17', '2026-04-22 14:14:03'),
(19, 1, '2026-02-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 12', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'موعد', 'متابعة', '• 12/02/2026: الاحد بعد ساعة 1 - الاحد بعد ساعة 3', '2026-04-22 14:14:03'),
(20, 1, '2026-02-12', '2026-04-30', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Rose Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'موعد', 'متابعة', '• 12/02/2026: الاحد ساعة 3', '2026-04-22 14:14:03'),
(21, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Sherifa Al Awadi', '2', 'غير محدد', '', '94975755', 'متوسط', 'متابعة', 'مهتم', 'متابعة', '• 11/02/2026: filipin candella call me\n• 12/04/2026: اهتمت وابعت لها على الواتس', '2026-04-22 14:14:03'),
(22, 1, '2026-04-14', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Arkan', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'مهتم', 'متابعة', '• 14/04/2026: لح تطلب اخدت مني كل المعلومات', '2026-04-22 14:14:03'),
(23, 1, '2026-02-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'My Skin - Andalos', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 15/02/2026: قال الدكتور سيتصل بي', '2026-04-22 14:14:03'),
(24, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 4', 'Nova', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'مهتم', 'متابعة', '• 15/03/2026: كتير اهتمت بدها تطلب ترد خبر مرة تانية - ramadan', '2026-04-22 14:14:03'),
(25, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 3', 'Nova', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: المصري عندهم وسيطلب عند النفاذ - ramadan', '2026-04-22 14:14:03'),
(26, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 8', 'Nova', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: بس تخلص بتطلب - ramadan', '2026-04-22 14:14:03'),
(27, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 7', 'Nova', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: العيادة جديدة احبوها زيارة مرة اخرى - ramadan', '2026-04-22 14:14:03'),
(28, 1, '2026-02-16', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 15', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 16/02/2026: زيارة غدا', '2026-04-22 14:14:03'),
(29, 1, '2026-02-16', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 14', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 16/02/2026: عندهن عيادة هيفا بنوفا وهني احبو القطعة لازم روح عند نوفا شوف شو بدهن', '2026-04-22 14:14:03'),
(30, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 16', 'Cure Clinic', '2', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'مهتم', 'متابعة', '• 20/04/2026: جايب جهاذ سيناشور ولح يطلب البروتكتف عن قريب', '2026-04-22 14:14:03'),
(31, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 6', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'متوسط', 'متابعة', 'موعد', 'متابعة', '• 20/04/2026: suzi doctor appointment wp', '2026-04-22 14:14:03'),
(32, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Mazaya Bneid Al', 'Gar', 'Medical Care', '2', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'مهتم', 'متابعة', '• 19/08/2025: زيارة أولى\n• 20/04/2026: احبوها كثيرا يجب اتي موعد اخر', '2026-04-22 14:14:03'),
(33, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Mazaya Bneid Al', 'Gar', 'Pristine', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'متابعة', 'متابعة', '• 20/04/2026: العيادة جديدة احبوها زيارة مرة اخرى', '2026-04-22 14:14:03'),
(34, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Mazaya Bneid Al', 'Gar', 'Dr. Khouloud', '1', 'غير محدد', '', NULL, 'متوسط', 'عرض منتج', 'موعد', 'متابعة', '• 20/04/2026: appointment 9 or 3', '2026-04-22 14:14:03'),
(35, 1, '2026-04-03', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', 'F. 14', 'Vogue Clinic 14-Hawally', '2', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'مغلق', 'مغلق', '• 20/01/2026: الادارة احبو المنتج ويريدون منه اخدو عينات للتجربة\n• 03/04/2026: ramadan-بالصيانة', '2026-04-22 14:14:03'),
(36, 1, '2026-04-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'My Skin - Fahad Al-Mutawaa', '1', 'غير محدد', '', NULL, 'منخفض', 'متابعة', 'متابعة', 'متابعة', '• 12/04/2026: مرة اخرى مشغول', '2026-04-22 14:14:03'),
(37, 1, '2025-07-09', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Jarallah Al-Almani', '1', 'غير محدد', '', '1844445', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 07/09/2025: kandella, cynasure', '2026-04-22 14:14:03'),
(38, 1, '2025-01-10', NULL, NULL, NULL, 'تم البيع', 'Hawally', NULL, 'Jarallah Al-Almani', '2', 'غير محدد', '', '50311393', 'عالي', 'بيع', 'تم', 'تم', '• 01/10/2025: 1 box candella size 20 - تم البيع', '2026-04-22 14:14:03'),
(39, 1, '2026-01-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'I Care', '2', 'غير محدد', '', '976010393', 'منخفض', 'متابعة', 'متابعة', 'متابعة', '• 12/11/2025: call dr marina\n• 20/01/2026: الادارة احبو المنتج ويريدون منه - الاردنية يجب المتابعة', '2026-04-22 14:14:03'),
(40, 1, '2025-12-11', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Labeuty', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 12/11/2025: broshure clarity', '2026-04-22 14:14:03'),
(41, 1, '2026-02-12', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Wejdan', '1', 'غير محدد', '', NULL, 'منخفض', 'متابعة', 'متابعة', 'متابعة', '• 12/02/2026: لح يتصلو فيني ومهمة اكيد لح يتصلو', '2026-04-22 14:14:03'),
(42, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 5', 'Nova', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: البنانية اخدت قبل - ramadan', '2026-04-22 14:14:03'),
(43, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 2', 'Nova', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: مش مهتمة اجي بعد العيد - ramadan', '2026-04-22 14:14:03'),
(44, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 10', 'Nova', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: زيارة بعد العيد - ramadan', '2026-04-22 14:14:03'),
(45, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 1', 'Nova', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 15/03/2026: هبة كلاريتي - ramadan', '2026-04-22 14:14:03'),
(46, 1, '2026-03-15', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Saleh Jurawiy', '1', 'غير محدد', '', '96116099', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', NULL, '2026-04-22 14:14:03'),
(47, 1, '2026-02-18', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Shaab', 'F. 15', 'Cure Clinic', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 18/02/2026: بعد المغرب من 9 لل 12 - ramadan', '2026-04-22 14:14:03'),
(48, 1, '2026-02-18', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Sahar Clinic', '2', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'مغلق', 'مغلق', '• 18/02/2026: كتير مهتمة ولازم عيد عليها واتصال بها كانت مسكرة', '2026-04-22 14:14:03'),
(49, 1, '2025-08-18', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Prime', '1', 'غير محدد', '', '96677073', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 18/08/2025: Candela', '2026-04-22 14:14:03'),
(50, 1, '2025-08-19', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Global', '1', 'غير محدد', '', '1871111', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 19/08/2025: Visit again', '2026-04-22 14:14:03'),
(51, 1, '2025-08-19', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Alhayat Medical', '1', 'غير محدد', '', '22212888', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 19/08/2025: After 2 pm', '2026-04-22 14:14:03'),
(52, 1, '2025-08-19', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Germania Clinic', '1', 'غير محدد', '', '50489890', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 19/08/2025: Cadela & Cynosure', '2026-04-22 14:14:03'),
(53, 1, '2025-08-19', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Haydra Clinic', '1', 'غير محدد', '', '22275140', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 19/08/2025: Cynosure Candela', '2026-04-22 14:14:03'),
(54, 1, '2025-08-19', '2026-04-23', 'next', NULL, 'اتصال متابعة', 'Hawally', NULL, 'Laboute', '1', 'غير محدد', '', '1888855', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', NULL, '2026-04-22 14:14:03'),
(55, 1, '2025-08-28', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Hawally', NULL, 'Blau Clinic', '2', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 19/08/2025: Dr Back on sep. / qty to be finished\n• 28/08/2025: kandella.cynossure', '2026-04-22 14:14:03'),
(56, 1, '2025-08-19', '2026-04-23', 'done', '2026-04-23 13:55:10', 'processed successfully', 'العاصمة', NULL, 'Bella', '1', 'غير محدد', 'مدير', '22666446', 'كبير', 'طلب عرض سعر', 'بداية العلاقة وإصدار طلب', 'قيد التنفيذ', 'hello', '2026-04-22 14:14:03'),
(57, 1, '2026-04-20', '2026-04-23', 'next', NULL, 'زيارة ميدانية', 'Mazaya Bneid Al', 'Gar', 'Lumora', '1', 'غير محدد', '', NULL, 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 20/04/2026: BROSHURE masriye al ha tetwsal', '2026-04-22 14:14:03'),
(58, 1, '2026-04-20', '2026-04-23', 'cancelled', NULL, 'زيارة ميدانية', 'العاصمة', 'Gar', 'Yarrow', '1', 'sdfsd', 'مدير', '22666447', 'كبير', 'طلب عرض سعر', 'بداية العلاقة وإصدار طلب', 'قيد التنفيذ', '• 20/04/2026: close', '2026-04-22 14:14:03'),
(59, 1, '2025-08-28', '2026-04-30', 'next', NULL, 'محمد علي', 'Hawally', NULL, 'Derma Ker', '1', 'غير محدد', '', '1830003', 'منخفض', 'عرض منتج', 'متابعة', 'متابعة', '• 28/08/2025: Thursday 5pm', '2026-04-22 14:14:03'),
(60, 1, '2026-04-22', '2026-04-30', 'done', '2026-04-23 12:39:20', 'TESTING', 'حولي', 'Kuwait City', 'باتو', '1', 'محمد ابراهيم', 'مدير', '66680241', 'كبير', 'طلب عرض سعر', 'بداية العلاقة وإصدار طلب', 'قيد التنفيذ', 'test', '2026-04-23 09:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `daily_visit_contacts`
--

CREATE TABLE `daily_visit_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `daily_visit_id` bigint(20) UNSIGNED NOT NULL,
  `person_name` varchar(255) NOT NULL,
  `job_title` varchar(190) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_visit_contacts`
--

INSERT INTO `daily_visit_contacts` (`id`, `daily_visit_id`, `person_name`, `job_title`, `mobile`, `created_at`) VALUES
(11, 4, 'yasmin', '', '99415010', '2026-04-22 14:14:03'),
(12, 60, 'محمد ابراهيم', 'مدير', '66680241', '2026-04-23 09:28:39'),
(13, 56, 'غير محدد', 'مدير', '22666446', '2026-04-23 09:48:51'),
(14, 58, 'sdfsd', 'مدير', '22666447', '2026-04-23 10:56:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`, `is_admin`) VALUES
(1, 'admin', '$2y$10$pvkw62KQArrjPKMfqeUxueZpq3vVfY1Hs6bmZxRIaQxxj0n5MR.a6', '2026-04-22 06:40:38', 1),
(2, 'sara', '$2y$10$.ahct04yGP.fb6hbkd5zyOnvrwWcpAzC1aIIs8lAdfGxbzOweML46', '2026-04-22 07:14:13', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_visits`
--
ALTER TABLE `daily_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_daily_visits_visit_date` (`visit_date`),
  ADD KEY `idx_daily_visits_user_id` (`user_id`);

--
-- Indexes for table `daily_visit_contacts`
--
ALTER TABLE `daily_visit_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_daily_visit_contacts_visit_id` (`daily_visit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_visits`
--
ALTER TABLE `daily_visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `daily_visit_contacts`
--
ALTER TABLE `daily_visit_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_visits`
--
ALTER TABLE `daily_visits`
  ADD CONSTRAINT `fk_daily_visits_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `daily_visit_contacts`
--
ALTER TABLE `daily_visit_contacts`
  ADD CONSTRAINT `fk_daily_visit_contacts_visit_id` FOREIGN KEY (`daily_visit_id`) REFERENCES `daily_visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
