<?php
/**
 * Dynamic Academic Portfolio, CV Generator & Course Booking System
 * Target: Prof. Hassan Sheikh Ali Hilowle
 * Base URL: https://hassansheekhali.kesug.com/
 */

define('BASE_URL', 'https://hassansheekhali.kesug.com/');
define('WHATSAPP_LINK', 'https://wa.link/5gw3zh');

$db_host = '';
$db_name = '';
$db_user = '';
$db_pass = '';
$pdo = null;
$db_connected = false;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
}

// Process Form Submissions (Consultations, Enrollments, Inquiries)
// Ensure session is started at the very top of your file before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash_message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']); // Clear it so it doesn't persist on subsequent page refreshes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Book Consultation Action
    if ($action === 'book_consultation') {
        $name = trim($_POST['student_name'] ?? '');
        $email = trim($_POST['student_email'] ?? '');
        $phone = trim($_POST['student_phone'] ?? '');
        $type = trim($_POST['consultation_type'] ?? '');
        $date = trim($_POST['preferred_date'] ?? '');
        $time = trim($_POST['preferred_time'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($db_connected && $pdo) {
            $stmt = $pdo->prepare("INSERT INTO appointment_bookings (student_name, student_email, student_phone, consultation_type, preferred_date, preferred_time, institution, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $type, $date, $time, $institution, $notes]);
        }
        
        $_SESSION['flash_message'] = "Your 1-on-1 consultation request has been submitted successfully!";
        header("Location: " . BASE_URL . "#contact");
        exit();
    }

    // 2. Course Enrollment Action
    if ($action === 'enroll_course') {
        $course_id = intval($_POST['course_id'] ?? 0);
        $name = trim($_POST['student_name'] ?? '');
        $email = trim($_POST['student_email'] ?? '');
        $phone = trim($_POST['student_phone'] ?? '');
        $bg = trim($_POST['academic_background'] ?? '');
        $schedule = trim($_POST['preferred_schedule'] ?? '');

        if ($db_connected && $pdo) {
            $stmt = $pdo->prepare("INSERT INTO course_enrollments (course_id, student_name, student_email, student_phone, academic_background, preferred_schedule) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $name, $email, $phone, $bg, $schedule]);
        }
        
        $_SESSION['flash_message'] = "Your course enrollment request has been registered successfully!";
        header("Location: " . BASE_URL . "#workshops");
        exit();
    }

    // 3. Send Inquiry Action
    if ($action === 'send_inquiry') {
        $name = trim($_POST['sender_name'] ?? '');
        $email = trim($_POST['sender_email'] ?? '');
        $inst = trim($_POST['institution'] ?? '');
        $subj = trim($_POST['subject_type'] ?? '');
        $msg = trim($_POST['message'] ?? '');

        if ($db_connected && $pdo) {
            $stmt = $pdo->prepare("INSERT INTO contact_inquiries (sender_name, sender_email, institution, subject_type, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $inst, $subj, $msg]);
        }
        
        $_SESSION['flash_message'] = "Thank you! Your message has been sent to Prof. Hassan Sheikh Ali Hilowle.";
        header("Location: " . BASE_URL . "#contact");
        exit();
    }
}

// Fetch Dynamic Data from Database with Complete Fallbacks
$profile = [
    'full_name' => 'Professor Hassan Sheikh Ali Hilowle',
    'short_name' => 'Prof. Hassan Hilowle',
    'title' => 'University Lecturer | Applied Statistician',
    'subtitle' => 'Research Methodology Specialist',
    'bio_summary' => 'With over 15 years of dedicated experience in tertiary education, leadership, and statistical methodology, Professor Hassan Sheikh Ali Hilowle has established a robust reputation for academic excellence and research guidance in Mogadishu, Somalia.',
    'location' => 'Mogadishu, Banaadir, Somalia',
    'email' => 'xasancali0008@gmail.com',
    'phone' => '+252 615 562 416',
    'years_exp' => 15,
    'students_count' => '1,000+',
    'profile_img' => 'Gemini_Generated_Image_76hkjm76hkjm76hk.jpg',
    'hero_bg' => 'WhatsApp Image 2026-10-21 at 4.23.04 PM.jpeg'
];

$appointments = [];
$education = [];
$teaching_courses = [];
$software_courses = [];
$software_skills = [];
$leadership = [];
$research_papers = [];

if ($db_connected && $pdo) {
    try {
        $db_profile = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();
        if ($db_profile) $profile = array_merge($profile, array_filter($db_profile));

        $appointments = $pdo->query("SELECT * FROM appointments ORDER BY display_order ASC, id DESC")->fetchAll();
        $education = $pdo->query("SELECT * FROM education ORDER BY id DESC")->fetchAll();
        $teaching_courses = $pdo->query("SELECT * FROM teaching_courses ORDER BY display_order ASC, id DESC")->fetchAll();
        $software_courses = $pdo->query("SELECT * FROM software_courses WHERE status = 'Active' ORDER BY id DESC")->fetchAll();
        $software_skills = $pdo->query("SELECT * FROM software_skills ORDER BY percentage DESC, id DESC")->fetchAll();
        $leadership = $pdo->query("SELECT * FROM leadership ORDER BY id DESC")->fetchAll();
        $research_papers = $pdo->query("SELECT * FROM research_papers ORDER BY publication_year DESC, id DESC")->fetchAll();
    } catch (Exception $e) {
        // Fallback or handle quietly
    }
}

// Normalize image file paths to prevent 'images/images/' duplication bugs
$raw_profile_img = $profile['profile_img'] ?? 'Gemini_Generated_Image_76hkjm76hkjm76hk.jpg';
$raw_profile_img = ltrim($raw_profile_img, '/');
if (strpos($raw_profile_img, 'images/') === 0) {
    $raw_profile_img = substr($raw_profile_img, 7);
}
$profile_img_url = BASE_URL . 'images/' . $raw_profile_img;

$raw_hero_bg = $profile['hero_bg'] ?? '';
$raw_hero_bg = ltrim($raw_hero_bg, '/');
if (strpos($raw_hero_bg, 'images/') === 0) {
    $raw_hero_bg = substr($raw_hero_bg, 7);
}
$hero_bg_url = !empty($raw_hero_bg) ? BASE_URL . 'images/' . $raw_hero_bg : '';

// Static Fallbacks if DB tables are empty
if (empty($appointments)) {
    $appointments = [
        ['category' => 'Postgraduate Studies', 'role_title' => 'Lecturer in Statistics & Research Methods', 'institution' => 'Hormuud University', 'description' => 'Delivering advanced quantitative research methodology, statistical inference, and thesis data analysis support for master\'s candidates.'],
        ['category' => 'Faculty Lecturer', 'role_title' => 'Lecturer in Applied Mathematics & Statistics', 'institution' => 'Jamhuriya University of Science and Technology (JUST)', 'description' => 'Instructing undergraduate and postgraduate courses in Business Statistics, Mathematical Inference, and Regression Analysis.']
    ];
}

if (empty($education)) {
    $education = [
        ['badge_label' => 'Master\'s Degree', 'degree_title' => 'Master of Applied Statistics', 'institution' => 'Kampala International University (KIU)', 'description' => 'Advanced specialization in quantitative analytical models, probability distributions, multivariate analysis, regression diagnostics, and applied econometric methods.', 'status' => 'Completed', 'location' => 'Kampala, Uganda'],
        ['badge_label' => 'Postgraduate Diploma', 'degree_title' => 'Postgraduate Diploma in Monitoring & Evaluation (M&E)', 'institution' => 'Uganda Technology & Management University (UTAMU)', 'description' => 'Focused on strategic project impact assessment, data collection frameworks, logical framework approaches, indicator design, and statistical audit methodologies.', 'status' => 'Completed', 'location' => 'Kampala, Uganda'],
        ['badge_label' => 'Bachelor\'s Degree', 'degree_title' => 'Bachelor of Education (Mathematics & Physics)', 'institution' => 'Mogadishu University', 'description' => 'Comprehensive background in pure & applied mathematics, classical mechanics, educational psychology, and specialized pedagogical techniques for scientific instruction.', 'status' => 'Completed', 'location' => 'Mogadishu, Somalia']
    ];
}

if (empty($leadership)) {
    $leadership = [
        ['duration' => '2013 – 2022 (9 Years)', 'role_title' => 'School Principal', 'organization' => 'El-Der School', 'details' => 'Managed daily administrative, academic, and operational governance for nearly a decade. Designed and implemented standardized mathematics and science curricula.'],
        ['duration' => 'Educational Programs', 'role_title' => 'Educational Programme Director / Specialist', 'organization' => 'Manar Foundation', 'details' => 'Directed educational development initiatives aimed at upgrading institutional standards. Formulated M&E systems to assess programme delivery effectiveness.']
    ];
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['full_name']); ?> | Academic Portfolio & Applied Statistician</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 800: '#0f172a', 900: '#0a0f1d' },
                        academic: { blue: '#1e3a8a', gold: '#d97706', goldHover: '#b45309', slate: '#475569', lightBg: '#f8fafc' },
                        whatsapp: '#25D366'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .academic-gradient { 
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); 
            <?php if (!empty($hero_bg_url)): ?>
            background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.9)), url('<?php echo $hero_bg_url; ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        .gold-border-glow:hover { border-color: #d97706; box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.15); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-amber-100 selection:text-amber-900 relative">

    <?php if ($flash_message): ?>
        <div id="flashAlert" class="fixed top-24 right-5 z-50 max-w-md bg-amber-500 text-slate-950 px-6 py-4 rounded-xl shadow-2xl flex items-center justify-between">
            <p class="text-xs font-bold"><?php echo htmlspecialchars($flash_message); ?></p>
            <button onclick="document.getElementById('flashAlert').remove()" class="ml-4 font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <!-- WhatsApp Floating Action Button -->
    <a href="<?php echo WHATSAPP_LINK; ?>" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:scale-110 hover:bg-[#20ba5a] transition-all duration-300 flex items-center justify-center group" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp text-3xl"></i>
        <span class="max-w-0 overflow-hidden whitespace-nowrap group-hover:max-w-xs group-hover:ml-3 text-xs font-bold transition-all duration-300">Chat with Prof. Hassan</span>
    </a>

    <!-- Sticky Navigation Bar -->
    <header class="sticky top-0 z-50 bg-navy-800/95 backdrop-blur-md text-white border-b border-slate-800 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Branding -->
                <a href="#hero" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-lg bg-amber-600 flex items-center justify-center font-serif font-bold text-xl text-white shadow-md group-hover:bg-amber-500 transition-colors">H</div>
                    <div>
                        <span class="block text-lg font-bold tracking-tight text-white font-serif"><?php echo htmlspecialchars($profile['short_name']); ?></span>
                        <span class="block text-xs text-amber-400 font-medium tracking-wider uppercase">Applied Statistician & Lecturer</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                    <a href="#about" class="text-slate-300 hover:text-amber-400 transition-colors">About</a>
                    <a href="#education" class="text-slate-300 hover:text-amber-400 transition-colors">Education</a>
                    <a href="#teaching" class="text-slate-300 hover:text-amber-400 transition-colors">Teaching</a>
                    <a href="#tools" class="text-slate-300 hover:text-amber-400 transition-colors">Research Tools</a>
                    <a href="#software" class="text-slate-300 hover:text-amber-400 transition-colors">Software</a>
                    <a href="#workshops" class="text-slate-300 hover:text-amber-400 transition-colors">Workshops</a>
                    <a href="#leadership" class="text-slate-300 hover:text-amber-400 transition-colors">Leadership</a>
                    <a href="#contact" class="text-slate-300 hover:text-amber-400 transition-colors">Contact</a>
                </nav>

                <!-- Actions -->
                <div class="hidden lg:flex items-center space-x-3">
                    <a href="<?php echo WHATSAPP_LINK; ?>" target="_blank" class="inline-flex items-center justify-center px-3 py-2.5 rounded-md text-xs font-semibold text-white bg-[#25D366] hover:bg-[#20ba5a] shadow-md transition-all">
                        <i class="fa-brands fa-whatsapp mr-1.5 text-sm"></i> WhatsApp
                    </a>
                    <button onclick="generateAndOpenCV()" class="inline-flex items-center justify-center px-4 py-2.5 rounded-md text-xs font-semibold uppercase tracking-wider text-navy-800 bg-amber-500 hover:bg-amber-400 shadow-md transition-all">
                        <i class="fa-solid fa-file-arrow-down mr-2 text-sm"></i> Download CV (PDF)
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" type="button" class="text-slate-300 hover:text-white focus:outline-none p-2 rounded-md">
                        <i id="menu-icon-open" class="fa-solid fa-bars text-xl"></i>
                        <i id="menu-icon-close" class="fa-solid fa-xmark text-xl hidden"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile dropdown menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-navy-900 border-b border-slate-800 px-4 pt-2 pb-6 space-y-3">
            <a href="#about" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">About</a>
            <a href="#education" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Education</a>
            <a href="#teaching" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Teaching</a>
            <a href="#tools" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Research Tools</a>
            <a href="#software" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Software & Skills</a>
            <a href="#workshops" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Workshops</a>
            <a href="#leadership" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Leadership</a>
            <a href="#contact" class="mobile-nav-link block px-3 py-2 rounded-md text-base font-medium text-slate-200 hover:bg-slate-800 hover:text-amber-400">Contact</a>
            <div class="pt-2 flex flex-col gap-2">
                <a href="<?php echo WHATSAPP_LINK; ?>" target="_blank" class="w-full text-center block px-4 py-3 rounded-md text-sm font-semibold uppercase tracking-wider text-white bg-[#25D366]">
                    <i class="fa-brands fa-whatsapp mr-2"></i> Chat on WhatsApp
                </a>
                <button onclick="generateAndOpenCV()" class="w-full text-center block px-4 py-3 rounded-md text-sm font-semibold uppercase tracking-wider text-navy-800 bg-amber-500 hover:bg-amber-400">
                    <i class="fa-solid fa-file-arrow-down mr-2"></i> Download Full CV (PDF)
                </button>
                <a href="<?php echo BASE_URL; ?>login.php" class="w-full text-center block px-4 py-3 rounded-md text-sm font-semibold uppercase tracking-wider text-amber-400 border border-amber-500/50">Admin Login</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="academic-gradient text-white py-16 lg:py-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#d97706_1px,transparent_1px)] [background-size:16px_16px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-amber-400 text-xs font-semibold tracking-wide uppercase">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span><?php echo intval($profile['years_exp']); ?>+ Years Higher Education & Applied Research</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold font-serif leading-tight tracking-tight text-white">
                        <?php echo htmlspecialchars($profile['full_name']); ?>
                    </h1>

                    <p class="text-lg sm:text-xl text-amber-400 font-medium tracking-wide">
                        <?php echo htmlspecialchars($profile['title']); ?> | <?php echo htmlspecialchars($profile['subtitle']); ?>
                    </p>

                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Empowering postgraduate researchers, undergraduate scholars, and academic institutions through rigorous statistical inference, quantitative methodology, and data-driven analytical decision-making.
                    </p>

                    <div class="pt-2 flex flex-wrap justify-center lg:justify-start gap-4 text-xs sm:text-sm text-slate-300">
                        <span class="flex items-center space-x-2 bg-slate-800/60 px-3.5 py-2 rounded-lg border border-slate-700">
                            <i class="fa-solid fa-location-dot text-amber-400"></i>
                            <span><?php echo htmlspecialchars($profile['location']); ?></span>
                        </span>
                        <a href="mailto:<?php echo htmlspecialchars($profile['email']); ?>" class="flex items-center space-x-2 bg-slate-800/60 px-3.5 py-2 rounded-lg border border-slate-700 hover:border-amber-400 transition-colors">
                            <i class="fa-solid fa-envelope text-amber-400"></i>
                            <span><?php echo htmlspecialchars($profile['email']); ?></span>
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($profile['phone']); ?>" class="flex items-center space-x-2 bg-slate-800/60 px-3.5 py-2 rounded-lg border border-slate-700 hover:border-amber-400 transition-colors">
                            <i class="fa-solid fa-phone text-amber-400"></i>
                            <span><?php echo htmlspecialchars($profile['phone']); ?></span>
                        </a>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                        <a href="<?php echo WHATSAPP_LINK; ?>" target="_blank" class="inline-flex items-center justify-center px-6 py-3.5 rounded-lg text-sm font-semibold text-white bg-[#25D366] hover:bg-[#20ba5a] shadow-lg shadow-green-900/30 transition-all">
                            Chat on WhatsApp <i class="fa-brands fa-whatsapp ml-2 text-base"></i>
                        </a>
                        <button onclick="generateAndOpenCV()" class="inline-flex items-center justify-center px-6 py-3.5 rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-500 shadow-lg shadow-amber-900/30 transition-all">
                            Download Photo CV (PDF) <i class="fa-solid fa-file-pdf ml-2 text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute -inset-1 bg-gradient-to-r from-amber-500 to-blue-600 rounded-2xl blur opacity-30"></div>
                        <div class="relative bg-navy-900/90 border border-slate-700/80 rounded-2xl p-6 shadow-2xl backdrop-blur-sm text-center">
                            <div class="relative w-60 h-72 sm:w-64 sm:h-80 mx-auto rounded-xl overflow-hidden bg-slate-800 border-4 border-amber-500/80 shadow-inner mb-6">
                                <img src="<?php echo $profile_img_url; ?>" alt="Professor Hassan Sheikh Ali Hilowle" class="w-full h-full object-cover object-top">
                            </div>
                            <h3 class="text-xl font-bold font-serif text-white"><?php echo htmlspecialchars($profile['short_name']); ?></h3>
                            <p class="text-xs text-amber-400 uppercase tracking-widest font-semibold mt-1">Postgraduate Lecturer</p>
                            <div class="mt-4 pt-4 border-t border-slate-800 text-xs text-slate-300 space-y-2 text-left">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Current Role 1:</span>
                                    <span class="font-medium text-slate-200">Hormuud University (PG)</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Current Role 2:</span>
                                    <span class="font-medium text-slate-200">Jamhuriya University (JUST)</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Specialization:</span>
                                    <span class="font-medium text-amber-400">Statistics & M&E</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academic Profile / About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-600 tracking-widest uppercase mb-2">Scholarly Profile</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-slate-900">Academic Background & Philosophy</h3>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                <div class="lg:col-span-7 space-y-6 text-slate-600 leading-relaxed text-base sm:text-lg">
                    <div class="float-none sm:float-right sm:ml-6 sm:mb-4 mb-6 flex justify-center">
                        <div class="w-48 sm:w-52 h-60 sm:h-64 rounded-2xl overflow-hidden border-2 border-amber-500 shadow-md bg-slate-100">
                            <img src="<?php echo $profile_img_url; ?>" alt="Professor Hassan Sheikh Ali Hilowle" class="w-full h-full object-cover object-top">
                        </div>
                    </div>
                    
                    <p class="font-serif text-slate-800 text-lg leading-relaxed">
                        <?php echo htmlspecialchars($profile['bio_summary']); ?>
                    </p>
                    <p>
                        Specializing in <strong>Applied Statistics, Mathematical Statistics, and Quantitative Research Methodology</strong>, Professor Hassan bridges theoretical statistical modeling with empirical, real-world data applications. He has spearheaded postgraduate research coursework and supervised dozens of master’s theses across key academic disciplines.
                    </p>
                    <p>
                        His educational methodology centers on empowering students with critical analytical thinking, mastering complex statistical software suites (SPSS, STATA, EViews), and enforcing rigorous Monitoring & Evaluation (M&E) standards required in contemporary scholarly research[cite: 4].
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 pt-6 clear-both">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-3xl font-extrabold text-slate-900 font-serif"><?php echo intval($profile['years_exp']); ?>+</span>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1 block">Years Experience</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center">
                            <span class="block text-3xl font-extrabold text-slate-900 font-serif"><?php echo htmlspecialchars($profile['students_count']); ?></span>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1 block">Students Mentored</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-center col-span-2 sm:col-span-1">
                            <span class="block text-3xl font-extrabold text-slate-900 font-serif">5+</span>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1 block">Software Suites</span>
                        </div>
                    </div>
                </div>

                <!-- Academic Appointments Sidebar -->
                <div class="lg:col-span-5 bg-slate-900 text-white rounded-2xl p-8 shadow-xl border border-slate-800">
                    <h4 class="text-xl font-bold font-serif text-amber-400 mb-6 flex items-center">
                        <i class="fa-solid fa-university mr-3 text-amber-500"></i> Institutional Appointments
                    </h4>
                    <div class="space-y-6">
                        <?php foreach ($appointments as $app): ?>
                            <div class="relative pl-6 border-l-2 border-amber-500/80">
                                <span class="text-xs text-amber-400 font-semibold uppercase tracking-wider"><?php echo htmlspecialchars($app['category']); ?></span>
                                <h5 class="text-lg font-bold text-white mt-1"><?php echo htmlspecialchars($app['role_title']); ?></h5>
                                <p class="text-sm text-slate-300 font-medium"><?php echo htmlspecialchars($app['institution']); ?></p>
                                <p class="text-xs text-slate-400 mt-2"><?php echo htmlspecialchars($app['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Research & Statistical Tools Section -->
    <section id="tools" class="py-20 bg-slate-900 text-white border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-400 tracking-widest uppercase mb-2">Academic Toolkit</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-white">Interactive Research Calculators</h3>
                <p class="mt-4 text-slate-400 text-base">Free interactive quantitative tools created for students and researchers to calculate sample sizes and select statistical tests[cite: 4].</p>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl font-bold">
                                <i class="fa-solid fa-calculator"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold font-serif text-white">Sample Size Calculator</h4>
                                <p class="text-xs text-amber-400">Sloven / Yamane & Cochran Formulas[cite: 4]</p>
                            </div>
                        </div>
                        <div class="space-y-4 text-sm">
                            <div>
                                <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">Target Population Size (N)</label>
                                <input type="number" id="pop-size" value="1000" min="1" oninput="calculateSampleSize()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">Confidence Level</label>
                                    <select id="conf-level" onchange="calculateSampleSize()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                                        <option value="1.96" selected>95% Confidence (z = 1.96)</option>
                                        <option value="2.576">99% Confidence (z = 2.576)</option>
                                        <option value="1.645">90% Confidence (z = 1.645)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">Margin of Error (e)</label>
                                    <select id="margin-error" onchange="calculateSampleSize()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                                        <option value="0.05" selected>5% (0.05)</option>
                                        <option value="0.01">1% (0.01)</option>
                                        <option value="0.10">10% (0.10)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-700/80 bg-slate-900/60 p-5 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block uppercase font-medium">Recommended Sample Size</span>
                            <span id="sample-result" class="text-3xl font-extrabold text-amber-400 font-serif">286</span>
                            <span class="text-xs text-slate-500 block">Respondents required</span>
                        </div>
                        <div class="text-right text-xs text-slate-400">
                            <span class="block">Formula: <span class="text-amber-400 font-mono">Yamane / Cochran</span></span>
                            <span class="block mt-1">High statistical power</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl font-bold">
                                <i class="fa-solid fa-diagram-project"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold font-serif text-white">Statistical Test Selector</h4>
                                <p class="text-xs text-amber-400">Choose the right test for thesis data[cite: 4]</p>
                            </div>
                        </div>
                        <div class="space-y-4 text-sm">
                            <div>
                                <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">1. Primary Research Objective</label>
                                <select id="test-goal" onchange="recommendStatTest()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                                    <option value="compare">Compare Group Means / Differences</option>
                                    <option value="relate">Measure Relationship / Association</option>
                                    <option value="predict">Predict Outcome (Impact / Effect)</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">2. Variable Type</label>
                                    <select id="var-type" onchange="recommendStatTest()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                                        <option value="continuous">Continuous (Interval / Ratio)</option>
                                        <option value="categorical">Categorical (Nominal / Ordinal)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-300 uppercase font-semibold mb-1">3. Number of Groups</label>
                                    <select id="num-groups" onchange="recommendStatTest()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white">
                                        <option value="2">2 Independent Groups</option>
                                        <option value="3+">3 or More Groups</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-700/80 bg-slate-900/60 p-5 rounded-xl">
                        <span class="text-xs text-slate-400 block uppercase font-medium">Recommended Statistical Procedure</span>
                        <h5 id="recommended-test-title" class="text-xl font-bold text-amber-400 font-serif mt-1">Independent Samples t-Test</h5>
                        <p id="recommended-test-desc" class="text-xs text-slate-300 mt-1 leading-relaxed">Compares the means between two independent groups to determine if there is statistical evidence that the associated population means are significantly different[cite: 4].</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Education & Credentials Section -->
    <section id="education" class="py-20 bg-slate-50 border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-600 tracking-widest uppercase mb-2">Qualifications</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-slate-900">Academic Background</h3>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($education as $ed): ?>
                    <div class="bg-white rounded-xl p-8 border border-slate-200 shadow-sm hover:shadow-md transition-shadow gold-border-glow border-t-4 border-t-amber-500 flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl mb-6">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full mb-3"><?php echo htmlspecialchars($ed['badge_label']); ?></span>
                            <h4 class="text-xl font-bold font-serif text-slate-900 mb-2"><?php echo htmlspecialchars($ed['degree_title']); ?></h4>
                            <p class="text-sm font-semibold text-amber-600 mb-4"><?php echo htmlspecialchars($ed['institution']); ?></p>
                            <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($ed['description']); ?></p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex justify-between items-center">
                            <span>Status: <?php echo htmlspecialchars($ed['status']); ?></span>
                            <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($ed['location']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Teaching & Courses Catalog -->
    <section id="teaching" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-600 tracking-widest uppercase mb-2">Pedagogical Expertise</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-slate-900">Teaching & Course Catalog</h3>
                <p class="mt-4 text-slate-600 text-base">Key statistical and methodological modules taught across undergraduate and postgraduate university programs[cite: 4].</p>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($teaching_courses as $tc): ?>
                    <div class="p-6 rounded-xl bg-slate-50 border border-slate-200 hover:border-amber-500 transition-all group">
                        <div class="text-amber-600 text-2xl mb-4 group-hover:scale-110 transition-transform">
                            <i class="<?php echo htmlspecialchars($tc['icon_class']); ?>"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-blue-100 text-blue-800 mb-2 inline-block"><?php echo htmlspecialchars($tc['category_badge']); ?></span>
                        <h4 class="text-lg font-bold font-serif text-slate-900 mb-2"><?php echo htmlspecialchars($tc['course_title']); ?></h4>
                        <p class="text-xs text-slate-600 leading-relaxed"><?php echo htmlspecialchars($tc['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Software Competencies & Computational Tools -->
    <section id="software" class="py-20 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-400 tracking-widest uppercase mb-2">Technical Proficiency</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-white">Statistical Software & Tools</h3>
                <p class="mt-4 text-slate-400 text-base">Expert-level mastery in computational statistical packages for complex data analysis, forecasting, and academic research[cite: 4].</p>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($software_skills as $sk): ?>
                    <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-6 relative hover:border-amber-500 transition-colors">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                                    <i class="<?php echo htmlspecialchars($sk['icon_class']); ?>"></i>
                                </div>
                                <h4 class="text-lg font-bold text-white font-serif"><?php echo htmlspecialchars($sk['tool_name']); ?></h4>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 bg-amber-500/20 text-amber-400 rounded-full"><?php echo htmlspecialchars($sk['proficiency']); ?></span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4"><?php echo htmlspecialchars($sk['description']); ?></p>
                        <div class="w-full bg-slate-700 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: <?php echo intval($sk['percentage']); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Languages Card -->
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-6 relative hover:border-amber-500 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-language"></i>
                            </div>
                            <h4 class="text-lg font-bold text-white font-serif">Language Proficiency</h4>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div>
                                <div class="flex justify-between text-slate-300 mb-1"><span>Somali</span><span class="text-amber-400 font-semibold">Native</span></div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5"><div class="bg-amber-500 h-1.5 rounded-full" style="width:100%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-slate-300 mb-1"><span>English</span><span class="text-amber-400 font-semibold">Fluent / Academic</span></div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5"><div class="bg-amber-500 h-1.5 rounded-full" style="width:92%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-slate-300 mb-1"><span>Arabic</span><span class="text-amber-400 font-semibold">Fluent</span></div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5"><div class="bg-amber-500 h-1.5 rounded-full" style="width:90%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Software Workshops (Zoom Classes) Section -->
    <section id="workshops" class="py-20 bg-slate-950 text-white border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-400 tracking-widest uppercase mb-2">Live Training & Zoom Classes</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-white">Data Analysis Software Workshops</h3>
                <p class="mt-4 text-slate-400 text-base">Enroll in specialized online training workshops led by Prof. Hassan.</p>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($software_courses as $course): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-900/50 text-blue-300 mb-2 inline-block">
                                <?php echo htmlspecialchars($course['skill_level']); ?>
                            </span>
                            <h4 class="text-base font-bold font-serif text-white mb-2"><?php echo htmlspecialchars($course['course_title']); ?></h4>
                            <p class="text-xs text-slate-400 leading-relaxed mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <div class="pt-4 border-t border-slate-800 flex justify-between items-center">
                            <span class="font-bold text-amber-400">$<?php echo number_format($course['price'], 2); ?></span>
                            <button onclick="openEnrollModal(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars(addslashes($course['course_title'])); ?>')" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded flex items-center">
                                <i class="fa-brands fa-whatsapp mr-1 text-sm"></i> Enroll via WhatsApp
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Leadership & Administrative Experience -->
    <section id="leadership" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold text-amber-600 tracking-widest uppercase mb-2">Administrative Background</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold font-serif text-slate-900">Leadership & Governance</h3>
                <div class="w-16 h-1 bg-amber-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($leadership as $lead): ?>
                    <div class="bg-white rounded-2xl p-8 border border-slate-200 relative overflow-hidden shadow-sm">
                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full mb-4"><?php echo htmlspecialchars($lead['duration']); ?></span>
                        <h4 class="text-2xl font-bold font-serif text-slate-900 mb-2"><?php echo htmlspecialchars($lead['role_title']); ?></h4>
                        <p class="text-base font-semibold text-amber-600 mb-4"><?php echo htmlspecialchars($lead['organization']); ?></p>
                        <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($lead['details']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact & Consultation Section -->
    <section id="contact" class="py-20 bg-slate-900 text-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-5 space-y-8">
                    <div>
                        <h2 class="text-xs font-bold text-amber-400 tracking-widest uppercase mb-2">Academic Enquiries</h2>
                        <h3 class="text-3xl font-extrabold font-serif text-white">Get in Touch</h3>
                        <p class="mt-4 text-slate-400 text-sm leading-relaxed">
                            Available for research thesis supervision, statistical methodology consulting, university lectures, and institutional monitoring & evaluation frameworks[cite: 4].
                        </p>
                    </div>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-start space-x-4 p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="text-amber-400 text-xl mt-0.5"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <h5 class="font-bold text-white">Location</h5>
                                <p class="text-slate-300"><?php echo htmlspecialchars($profile['location']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4 p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="text-amber-400 text-xl mt-0.5"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <h5 class="font-bold text-white">Email Address</h5>
                                <a href="mailto:<?php echo htmlspecialchars($profile['email']); ?>" class="text-amber-400 hover:underline"><?php echo htmlspecialchars($profile['email']); ?></a>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4 p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="text-[#25D366] text-xl mt-0.5"><i class="fa-brands fa-whatsapp"></i></div>
                            <div>
                                <h5 class="font-bold text-white">WhatsApp Direct</h5>
                                <a href="<?php echo WHATSAPP_LINK; ?>" target="_blank" class="text-[#25D366] hover:underline font-semibold">Chat with Prof. Hassan</a>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4 p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="text-amber-400 text-xl mt-0.5"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <h5 class="font-bold text-white">Direct Phone</h5>
                                <a href="tel:<?php echo htmlspecialchars($profile['phone']); ?>" class="text-slate-300 hover:text-amber-400"><?php echo htmlspecialchars($profile['phone']); ?></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-2xl">
                    <h4 class="text-xl font-bold font-serif text-white mb-6">Send an Inquiry</h4>
                    <form method="POST" action="" onsubmit="handleInquiryWhatsApp(event)" class="space-y-5">
                        <input type="hidden" name="action" value="send_inquiry">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Full Name *</label>
                                <input type="text" id="inquiry_name" name="sender_name" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500" placeholder="Name">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Email Address *</label>
                                <input type="email" id="inquiry_email" name="sender_email" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500" placeholder="email@institution.edu">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Institution / University</label>
                                <input type="text" id="inquiry_institution" name="institution" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500" placeholder="University">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Inquiry Type</label>
                                <select id="inquiry_subject" name="subject_type" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500">
                                    <option value="Thesis Supervision">Thesis Data & Supervision</option>
                                    <option value="Lecture Request">Guest Lecture / Teaching</option>
                                    <option value="Consulting">M&E / Statistical Consulting</option>
                                    <option value="Other">General Academic Inquiry</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Message Detail *</label>
                            <textarea id="inquiry_message" name="message" rows="4" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500" placeholder="Detail your research needs..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-[#25D366] hover:bg-[#20ba5a] text-white font-semibold py-3.5 px-6 rounded-lg text-sm tracking-wide transition-colors flex items-center justify-center space-x-2 shadow-lg">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>Send via WhatsApp</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Enrollment Modal -->
    <div id="enrollModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 relative">
            <button onclick="closeEnrollModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl">&times;</button>
            <h3 class="text-xl font-bold font-serif text-white mb-1">Course Enrollment</h3>
            <p id="enrollCourseName" class="text-xs text-amber-400 font-semibold mb-6"></p>

            <form method="POST" action="" onsubmit="handleEnrollmentWhatsApp(event)" class="space-y-4">
                <input type="hidden" name="action" value="enroll_course">
                <input type="hidden" id="enrollCourseId" name="course_id" value="">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Full Name</label>
                    <input type="text" id="enroll_student_name" name="student_name" required class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-sm text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Email</label>
                        <input type="email" id="enroll_student_email" name="student_email" required class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Phone</label>
                        <input type="text" id="enroll_student_phone" name="student_phone" required class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Academic Background</label>
                    <input type="text" id="enroll_academic_bg" name="academic_background" placeholder="e.g. Master's Student" class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Preferred Shift</label>
                    <select id="enroll_schedule" name="preferred_schedule" class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-sm text-white">
                        <option value="Weekend Morning Shift">Weekend Morning Shift</option>
                        <option value="Evening Online Shift">Evening Online Shift</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#25D366] hover:bg-[#20ba5a] text-white font-bold text-xs uppercase tracking-wider rounded flex items-center justify-center space-x-2">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Submit & Chat on WhatsApp</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-navy-900 text-slate-400 py-12 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div>
                <p class="font-serif text-slate-200 font-bold text-sm"><?php echo htmlspecialchars($profile['full_name']); ?></p>
                <p class="text-slate-500 mt-0.5">Applied Statistician & Research Methodology Specialist • Mogadishu, Somalia[cite: 4]</p>
            </div>
            <div class="text-center sm:text-right text-slate-500">
                <p>&copy; <span id="year">2026</span> All Rights Reserved.</p>
                <p class="mt-0.5"><a href="<?php echo BASE_URL; ?>login.php" class="text-amber-400 hover:underline">Admin Login Portal</a></p>
            </div>
        </div>
    </footer>

    <!-- Interactive JavaScript Logic -->
    <script>
        const whatsappBaseLink = "<?php echo WHATSAPP_LINK; ?>";
        const profileImgUrl = "<?php echo $profile_img_url; ?>";

        document.getElementById('year').textContent = new Date().getFullYear();

        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');

        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
        });

        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
            });
        });

        function openEnrollModal(id, title) {
            document.getElementById('enrollCourseId').value = id;
            document.getElementById('enrollCourseName').innerText = title;
            document.getElementById('enrollModal').classList.remove('hidden');
        }
        function closeEnrollModal() {
            document.getElementById('enrollModal').classList.add('hidden');
        }

        // WhatsApp Redirection Handlers
        function handleEnrollmentWhatsApp(e) {
            const courseTitle = document.getElementById('enrollCourseName').innerText;
            const name = document.getElementById('enroll_student_name').value;
            const email = document.getElementById('enroll_student_email').value;
            const phone = document.getElementById('enroll_student_phone').value;
            const bg = document.getElementById('enroll_academic_bg').value;
            const shift = document.getElementById('enroll_schedule').value;

            const message = `Hello Prof. Hassan, I would like to enroll in the following course:\n\n` +
                            `*Course:* ${courseTitle}\n` +
                            `*Name:* ${name}\n` +
                            `*Email:* ${email}\n` +
                            `*Phone:* ${phone}\n` +
                            `*Academic Background:* ${bg}\n` +
                            `*Preferred Shift:* ${shift}`;

            window.open(`${whatsappBaseLink}?text=${encodeURIComponent(message)}`, '_blank');
        }

        function handleInquiryWhatsApp(e) {
            const name = document.getElementById('inquiry_name').value;
            const email = document.getElementById('inquiry_email').value;
            const inst = document.getElementById('inquiry_institution').value;
            const subject = document.getElementById('inquiry_subject').value;
            const msg = document.getElementById('inquiry_message').value;

            const message = `Hello Prof. Hassan, I have an academic inquiry:\n\n` +
                            `*Inquiry Type:* ${subject}\n` +
                            `*Name:* ${name}\n` +
                            `*Email:* ${email}\n` +
                            `*Institution:* ${inst}\n\n` +
                            `*Message:* ${msg}`;

            window.open(`${whatsappBaseLink}?text=${encodeURIComponent(message)}`, '_blank');
        }

        // Sample Size Calculator
        function calculateSampleSize() {
            const N = parseFloat(document.getElementById('pop-size').value) || 0;
            const z = parseFloat(document.getElementById('conf-level').value);
            const e = parseFloat(document.getElementById('margin-error').value);
            const p = 0.5;

            if (N <= 0) {
                document.getElementById('sample-result').textContent = '0';
                return;
            }
            const n0 = (Math.pow(z, 2) * p * (1 - p)) / Math.pow(e, 2);
            const n = n0 / (1 + ((n0 - 1) / N));
            document.getElementById('sample-result').textContent = Math.ceil(n);
        }

        // Statistical Test Selector
        function recommendStatTest() {
            const goal = document.getElementById('test-goal').value;
            const varType = document.getElementById('var-type').value;
            const groups = document.getElementById('num-groups').value;
            const titleElem = document.getElementById('recommended-test-title');
            const descElem = document.getElementById('recommended-test-desc');

            if (goal === 'compare') {
                if (varType === 'continuous') {
                    if (groups === '2') {
                        titleElem.textContent = 'Independent Samples t-Test / Mann-Whitney U';
                        descElem.textContent = 'Use t-Test for parametric data to compare mean scores between 2 groups, or Mann-Whitney U for non-parametric data[cite: 4].';
                    } else {
                        titleElem.textContent = 'One-Way ANOVA / Kruskal-Wallis Test';
                        descElem.textContent = 'Use One-Way ANOVA to compare mean differences across 3 or more independent groups with continuous data[cite: 4].';
                    }
                } else {
                    titleElem.textContent = 'Chi-Square Test of Independence';
                    descElem.textContent = 'Evaluates if a significant relationship or difference exists between two categorical variables[cite: 4].';
                }
            } else if (goal === 'relate') {
                if (varType === 'continuous') {
                    titleElem.textContent = 'Pearson Correlation Coefficient (r)';
                    descElem.textContent = 'Measures the strength and direction of a linear relationship between two continuous variables[cite: 4].';
                } else {
                    titleElem.textContent = 'Spearman Rank Correlation / Chi-Square';
                    descElem.textContent = 'Assesses monotonic relationships between ordinal or non-parametric variables[cite: 4].';
                }
            } else if (goal === 'predict') {
                if (varType === 'continuous') {
                    titleElem.textContent = 'Multiple Linear Regression Analysis';
                    descElem.textContent = 'Models the quantitative relationship between multiple independent variables and a single continuous dependent variable[cite: 4].';
                } else {
                    titleElem.textContent = 'Binary / Multinomial Logistic Regression';
                    descElem.textContent = 'Predicts categorical or binary dependent variable outcomes based on predictor variables[cite: 4].';
                }
            }
        }

        calculateSampleSize();
        recommendStatTest();

        // Extended Dynamic CV Generator Engine
        function generateAndOpenCV() {
            const profile = <?php echo json_encode($profile ?? []); ?>;
            const appointments = <?php echo json_encode($appointments ?? []); ?>;
            const education = <?php echo json_encode($education ?? []); ?>;
            const teachingCourses = <?php echo json_encode($teaching_courses ?? []); ?>;
            const softwareCourses = <?php echo json_encode($software_courses ?? []); ?>;
            const softwareSkills = <?php echo json_encode($software_skills ?? []); ?>;
            const leadership = <?php echo json_encode($leadership ?? []); ?>;
            const researchPapers = <?php echo json_encode($research_papers ?? []); ?>;

            const renderAppointments = () => {
                if (!appointments || appointments.length === 0) return '<p class="text-xs text-slate-500">No institutional appointments recorded.</p>';
                return appointments.map(app => `
                    <div class="mb-4 relative pl-4 border-l-2 border-amber-600">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-slate-900">${app.role_title || ''}</span>
                            <span class="text-[10px] font-semibold uppercase px-2 py-0.5 bg-amber-50 text-amber-800 rounded">${app.category || ''}</span>
                        </div>
                        <div class="text-xs text-amber-700 font-semibold mt-0.5">${app.institution || ''}</div>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">${app.description || ''}</p>
                    </div>
                `).join('');
            };

            const renderEducation = () => {
                if (!education || education.length === 0) return '<p class="text-xs text-slate-500">No education background recorded.</p>';
                return education.map(edu => `
                    <div class="mb-4 relative pl-4 border-l-2 border-slate-800">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-slate-900">${edu.degree_title || ''}</span>
                            <span class="text-[10px] text-slate-500 font-medium">${edu.status || 'Completed'}</span>
                        </div>
                        <div class="text-xs text-amber-700 font-semibold mt-0.5">${edu.institution || ''} (${edu.location || ''})</div>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">${edu.description || ''}</p>
                    </div>
                `).join('');
            };

            const renderLeadership = () => {
                if (!leadership || leadership.length === 0) return '<p class="text-xs text-slate-500">No leadership experience recorded.</p>';
                return leadership.map(lead => `
                    <div class="mb-4 relative pl-4 border-l-2 border-blue-800">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-slate-900">${lead.role_title || ''}</span>
                            <span class="text-[10px] font-semibold text-blue-800 bg-blue-50 px-2 py-0.5 rounded">${lead.duration || ''}</span>
                        </div>
                        <div class="text-xs text-slate-700 font-semibold mt-0.5">${lead.organization || ''}</div>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">${lead.details || ''}</p>
                    </div>
                `).join('');
            };

            const renderResearchPapers = () => {
                if (!researchPapers || researchPapers.length === 0) return '<p class="text-xs text-slate-500">No research papers published yet.</p>';
                return researchPapers.map(paper => `
                    <div class="mb-4 pb-3 border-b border-slate-100">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-slate-900">${paper.title || ''}</span>
                            <span class="text-[10px] font-bold bg-slate-900 text-white px-2 py-0.5 rounded">${paper.publication_year || ''}</span>
                        </div>
                        <div class="text-xs italic text-amber-700 mt-0.5">${paper.journal_name || ''}</div>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">${paper.abstract_text || ''}</p>
                        ${paper.paper_url ? `<a href="${paper.paper_url}" target="_blank" class="text-[10px] text-blue-600 underline font-mono mt-1 inline-block"><i class="fa-solid fa-link mr-1"></i> View Publication / DOI</a>` : ''}
                    </div>
                `).join('');
            };

            const renderTeaching = () => {
                if (!teachingCourses || teachingCourses.length === 0) return '';
                return teachingCourses.map(tc => `
                    <div class="bg-slate-50 p-2.5 rounded border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-800 uppercase">${tc.category_badge || 'Module'}</span>
                        <h4 class="text-xs font-bold text-slate-900">${tc.course_title}</h4>
                        <p class="text-[10px] text-slate-600 mt-0.5">${tc.description}</p>
                    </div>
                `).join('');
            };

            const renderSoftwareSkillsBars = () => {
                if (!softwareSkills || softwareSkills.length === 0) return '';
                return softwareSkills.map(sk => `
                    <div class="mb-3">
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span>${sk.tool_name}</span>
                            <span class="text-amber-700">${sk.percentage}% (${sk.proficiency})</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-600 h-2 rounded-full" style="width: ${sk.percentage}%"></div>
                        </div>
                    </div>
                `).join('');
            };

            const cvHtmlContent = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Vitae - ${profile.full_name || 'Prof. Hassan Sheikh Ali Hilowle'}</title>
    <script src="https://cdn.tailwindcss.com"><\/script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; color: #1e293b !important; font-size: 10pt; padding: 0 !important; }
            a { text-decoration: none; color: inherit; }
            .shadow-lg, .shadow-md, .shadow-xl { box-shadow: none !important; }
            .page-break { page-break-before: always; }
        }
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
    </style>
</head>
<body class="p-4 sm:p-8">

    <div class="no-print max-w-5xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-xl flex flex-col sm:flex-row justify-between items-center shadow-lg gap-4">
        <div>
            <h1 class="text-base font-bold text-amber-400">Infographic Academic Curriculum Vitae</h1>
            <p class="text-xs text-slate-300">Optimized with visual data bars, structured grids, and clean typography.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center shadow transition-all">
                <i class="fa-solid fa-print mr-2"></i> Print / Save as PDF
            </button>
            <button onclick="window.close()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2.5 rounded-lg text-xs font-semibold border border-slate-700">
                Close
            </button>
        </div>
    </div>

    <main class="max-w-5xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <header class="bg-slate-900 text-white p-8 border-b-4 border-amber-500 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center space-x-6">
                <div class="w-32 h-36 rounded-xl overflow-hidden border-4 border-amber-500 bg-slate-800 shadow-lg flex-shrink-0">
                    <img src="${profileImgUrl}" alt="Profile Photo" class="w-full h-full object-cover object-top">
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold font-serif text-white">${profile.full_name || 'Professor Hassan Sheikh Ali Hilowle'}</h1>
                    <p class="text-amber-400 font-semibold text-sm sm:text-base mt-1">${profile.title || 'University Lecturer | Applied Statistician'}</p>
                    <p class="text-slate-400 text-xs mt-0.5">${profile.subtitle || 'Research Methodology Specialist'}</p>
                    
                    <div class="flex flex-wrap gap-4 text-xs text-slate-300 mt-3 pt-3 border-t border-slate-800">
                        <span><i class="fa-solid fa-location-dot text-amber-400 mr-1"></i> ${profile.location || 'Mogadishu, Somalia'}</span>
                        <span><i class="fa-solid fa-envelope text-amber-400 mr-1"></i> ${profile.email || 'xasancali0008@gmail.com'}</span>
                        <span><i class="fa-solid fa-phone text-amber-400 mr-1"></i> ${profile.phone || '+252 615 562 416'}</span>
                    </div>
                </div>
            </div>
            <div class="text-center bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                <span class="block text-2xl font-extrabold text-amber-400 font-serif">${profile.years_exp || '15'}+</span>
                <span class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Years Experience</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 p-8">
            <div class="md:col-span-5 space-y-8">
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-amber-600 mb-3">Profile Summary</h2>
                    <p class="text-xs text-slate-700 leading-relaxed text-justify">
                        ${profile.bio_summary || 'Over 15 years of dedicated academic instruction, quantitative research methodology, and statistical leadership in higher education.'}
                    </p>
                </section>

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-amber-600 mb-3">Software & Analytics Proficiency</h2>
                    <div class="space-y-2">
                        ${renderSoftwareSkillsBars()}
                    </div>
                </section>

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-amber-600 mb-3">Languages</h2>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between font-medium"><span>Somali</span><span class="text-amber-700 font-bold">Native</span></div>
                        <div class="flex justify-between font-medium"><span>English</span><span class="text-amber-700 font-bold">Fluent (Academic)</span></div>
                        <div class="flex justify-between font-medium"><span>Arabic</span><span class="text-amber-700 font-bold">Fluent</span></div>
                    </div>
                </section>

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-amber-600 mb-3">Core Modules Taught</h2>
                    <div class="grid grid-cols-1 gap-2">
                        ${renderTeaching()}
                    </div>
                </section>
            </div>

            <div class="md:col-span-7 space-y-8">
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-blue-800 mb-4">Institutional Appointments</h2>
                    ${renderAppointments()}
                </section>

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-blue-800 mb-4">Education & Credentials</h2>
                    ${renderEducation()}
                </section>

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-blue-800 mb-4">Administrative Leadership</h2>
                    ${renderLeadership()}
                </section>

                <section class="page-break">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 bg-slate-100 p-2 rounded border-l-4 border-blue-800 mb-4">Scholarly Publications & Research</h2>
                    ${renderResearchPapers()}
                </section>
            </div>
        </div>

        <footer class="bg-slate-900 text-slate-400 p-4 text-center text-[10px] border-t border-slate-800">
            ${profile.full_name || 'Prof. Hassan Sheikh Ali Hilowle'} • Official Infographic Academic Curriculum Vitae
        </footer>
    </main>

</body>
</html>`;

            const cvWindow = window.open('', '_blank');
            if (cvWindow) {
                cvWindow.document.write(cvHtmlContent);
                cvWindow.document.close();
            } else {
                alert('Please allow popups for this site to generate and view the Infographic CV.');
            }
        }
    </script>
</body>
</html>