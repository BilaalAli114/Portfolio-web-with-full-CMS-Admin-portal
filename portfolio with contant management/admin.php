<?php
// admin.php - Protected Administration Control Panel with Full CRUD Management & Image Uploads
session_start();

define('BASE_URL', 'https://hassansheekhali.kesug.com/');

// Handle Logout Action
if (isset($_GET['logout']) || (isset($_POST['action']) && $_POST['action'] === 'logout')) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Access Control Protection
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Database Credentials
$db_host = '';
$db_name = '';
$db_user = '';
$db_pass = '';
$pdo = null;
$notice = "";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

// ==========================================
// HANDLE ALL POST CRUD OPERATIONS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['admin_action'] ?? '';

    // 1. Profile Settings (Update with Image Uploads)
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $short_name = trim($_POST['short_name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $years_exp = intval($_POST['years_exp'] ?? 0);
        $students_count = trim($_POST['students_count'] ?? '');
        $bio_summary = trim($_POST['bio_summary'] ?? '');

        // Fetch existing profile images to preserve them if no new file is uploaded
        $existing = $pdo->query("SELECT profile_img, hero_bg FROM profile_settings LIMIT 1")->fetch();
        $profile_img = $existing['profile_img'] ?? 'Gemini_Generated_Image_76hkjm76hkjm76hk.jpg';
        $hero_bg = $existing['hero_bg'] ?? 'WhatsApp Image 2026-10-21 at 4.23.04 PM.jpeg';

        // Target upload directory
        $target_dir = __DIR__ . '/images/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Handle Profile Image Upload
        if (isset($_FILES['profile_img_file']) && $_FILES['profile_img_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_img_file']['tmp_name'];
            $file_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($_FILES['profile_img_file']['name']));
            $unique_name = time() . '_' . $file_name;
            if (move_uploaded_file($file_tmp, $target_dir . $unique_name)) {
                $profile_img = $unique_name;
            }
        }

        // Handle Hero Background Image Upload
        if (isset($_FILES['hero_bg_file']) && $_FILES['hero_bg_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['hero_bg_file']['tmp_name'];
            $file_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($_FILES['hero_bg_file']['name']));
            $unique_name = time() . '_bg_' . $file_name;
            if (move_uploaded_file($file_tmp, $target_dir . $unique_name)) {
                $hero_bg = $unique_name;
            }
        }

        $check = $pdo->query("SELECT COUNT(*) FROM profile_settings")->fetchColumn();
        if ($check > 0) {
            $stmt = $pdo->prepare("UPDATE profile_settings SET full_name = ?, short_name = ?, title = ?, email = ?, phone = ?, location = ?, years_exp = ?, students_count = ?, bio_summary = ?, profile_img = ?, hero_bg = ? WHERE id = 1");
            $stmt->execute([$full_name, $short_name, $title, $email, $phone, $location, $years_exp, $students_count, $bio_summary, $profile_img, $hero_bg]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO profile_settings (id, full_name, short_name, title, email, phone, location, years_exp, students_count, bio_summary, profile_img, hero_bg) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $short_name, $title, $email, $phone, $location, $years_exp, $students_count, $bio_summary, $profile_img, $hero_bg]);
        }
        $notice = "Profile settings and images successfully updated!";
    }

    // 2. Institutional Appointments (Create / Update / Delete)
    if ($action === 'save_appointment') {
        $id = intval($_POST['id'] ?? 0);
        $institution = trim($_POST['institution']);
        $role_title = trim($_POST['role_title']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $display_order = intval($_POST['display_order'] ?? 0);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE appointments SET institution = ?, role_title = ?, category = ?, description = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$institution, $role_title, $category, $description, $display_order, $id]);
            $notice = "Appointment updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (institution, role_title, category, description, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$institution, $role_title, $category, $description, $display_order]);
            $notice = "New appointment added successfully!";
        }
    }
    if ($action === 'delete_appointment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Appointment deleted successfully!";
    }

    // 3. Education (Create / Update / Delete)
    if ($action === 'save_education') {
        $id = intval($_POST['id'] ?? 0);
        $degree_title = trim($_POST['degree_title']);
        $institution = trim($_POST['institution']);
        $location = trim($_POST['location']);
        $status = trim($_POST['status']);
        $badge_label = trim($_POST['badge_label']);
        $description = trim($_POST['description']);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE education SET degree_title = ?, institution = ?, location = ?, status = ?, badge_label = ?, description = ? WHERE id = ?");
            $stmt->execute([$degree_title, $institution, $location, $status, $badge_label, $description, $id]);
            $notice = "Education qualification updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO education (degree_title, institution, location, status, badge_label, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$degree_title, $institution, $location, $status, $badge_label, $description]);
            $notice = "New education qualification added successfully!";
        }
    }
    if ($action === 'delete_education') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM education WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Education record deleted successfully!";
    }

    // 4. Teaching Courses Catalog (Create / Update / Delete)
    if ($action === 'save_teaching_course') {
        $id = intval($_POST['id'] ?? 0);
        $course_title = trim($_POST['course_title']);
        $category_badge = trim($_POST['category_badge']);
        $icon_class = trim($_POST['icon_class']);
        $description = trim($_POST['description']);
        $display_order = intval($_POST['display_order'] ?? 0);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE teaching_courses SET course_title = ?, category_badge = ?, icon_class = ?, description = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$course_title, $category_badge, $icon_class, $description, $display_order, $id]);
            $notice = "Teaching course updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO teaching_courses (course_title, category_badge, icon_class, description, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$course_title, $category_badge, $icon_class, $description, $display_order]);
            $notice = "New teaching course added successfully!";
        }
    }
    if ($action === 'delete_teaching_course') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM teaching_courses WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Teaching course deleted successfully!";
    }

    // 5. Software Workshops (Create / Update / Delete)
    if ($action === 'save_software_course') {
        $id = intval($_POST['id'] ?? 0);
        $software_name = trim($_POST['software_name']);
        $course_title = trim($_POST['course_title']);
        $duration = trim($_POST['duration']);
        $skill_level = trim($_POST['skill_level']);
        $price = floatval($_POST['price']);
        $icon_class = trim($_POST['icon_class']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE software_courses SET software_name = ?, course_title = ?, duration = ?, skill_level = ?, price = ?, icon_class = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$software_name, $course_title, $duration, $skill_level, $price, $icon_class, $description, $status, $id]);
            $notice = "Software workshop updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO software_courses (software_name, course_title, duration, skill_level, price, icon_class, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$software_name, $course_title, $duration, $skill_level, $price, $icon_class, $description, $status]);
            $notice = "New software workshop added successfully!";
        }
    }
    if ($action === 'delete_software_course') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM software_courses WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Software workshop deleted successfully!";
    }

    // 6. Software Skills (Create / Update / Delete)
    if ($action === 'save_skill') {
        $id = intval($_POST['id'] ?? 0);
        $tool_name = trim($_POST['tool_name']);
        $proficiency = trim($_POST['proficiency']);
        $percentage = intval($_POST['percentage']);
        $icon_class = trim($_POST['icon_class']);
        $description = trim($_POST['description']);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE software_skills SET tool_name = ?, proficiency = ?, percentage = ?, icon_class = ?, description = ? WHERE id = ?");
            $stmt->execute([$tool_name, $proficiency, $percentage, $icon_class, $description, $id]);
            $notice = "Software skill updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO software_skills (tool_name, proficiency, percentage, icon_class, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tool_name, $proficiency, $percentage, $icon_class, $description]);
            $notice = "New software skill added successfully!";
        }
    }
    if ($action === 'delete_skill') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM software_skills WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Software skill deleted successfully!";
    }

    // 7. Leadership Experience (Create / Update / Delete)
    if ($action === 'save_leadership') {
        $id = intval($_POST['id'] ?? 0);
        $role_title = trim($_POST['role_title']);
        $organization = trim($_POST['organization']);
        $duration = trim($_POST['duration']);
        $details = trim($_POST['details']);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE leadership SET role_title = ?, organization = ?, duration = ?, details = ? WHERE id = ?");
            $stmt->execute([$role_title, $organization, $duration, $details, $id]);
            $notice = "Leadership record updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO leadership (role_title, organization, duration, details) VALUES (?, ?, ?, ?)");
            $stmt->execute([$role_title, $organization, $duration, $details]);
            $notice = "New leadership record added successfully!";
        }
    }
    if ($action === 'delete_leadership') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM leadership WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Leadership record deleted successfully!";
    }

    // 8. Research Papers (Create / Update / Delete)
    if ($action === 'save_research_paper') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $journal_name = trim($_POST['journal_name']);
        $publication_year = intval($_POST['publication_year']);
        $abstract_text = trim($_POST['abstract_text']);
        $paper_url = trim($_POST['paper_url']);

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE research_papers SET title = ?, journal_name = ?, publication_year = ?, abstract_text = ?, paper_url = ? WHERE id = ?");
            $stmt->execute([$title, $journal_name, $publication_year, $abstract_text, $paper_url, $id]);
            $notice = "Research paper updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO research_papers (title, journal_name, publication_year, abstract_text, paper_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $journal_name, $publication_year, $abstract_text, $paper_url]);
            $notice = "New research paper added successfully!";
        }
    }
    if ($action === 'delete_research_paper') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM research_papers WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Research paper deleted successfully!";
    }

    // 9. Course Enrollments & Zoom Link (Update / Delete)
    if ($action === 'update_enrollment') {
        $enroll_id = intval($_POST['enrollment_id']);
        $status = $_POST['approval_status'];
        $zoom_url = trim($_POST['zoom_join_url']);

        $stmt = $pdo->prepare("UPDATE course_enrollments SET approval_status = ?, zoom_join_url = ? WHERE id = ?");
        $stmt->execute([$status, $zoom_url, $enroll_id]);
        $notice = "Student enrollment status and Zoom URL updated successfully!";
    }
    if ($action === 'delete_enrollment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM course_enrollments WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Course enrollment record deleted!";
    }

    // 10. Consultation Bookings (Update / Delete)
    if ($action === 'update_booking') {
        $booking_id = intval($_POST['booking_id']);
        $status = $_POST['booking_status'];
        $stmt = $pdo->prepare("UPDATE appointment_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);
        $notice = "Consultation booking status updated!";
    }
    if ($action === 'delete_booking') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM appointment_bookings WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Consultation booking deleted!";
    }

    // 11. Contact Inquiries (Delete)
    if ($action === 'delete_inquiry') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$id]);
        $notice = "Contact inquiry deleted successfully!";
    }

    // 12. Admin User Management (Create / Update / Delete)
    if ($action === 'save_user') {
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($username)) {
            if ($id > 0) {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->execute([$username, $id]);
                }
                $notice = "Admin user account updated successfully!";
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    $stmt->execute([$username, $hashed_password]);
                    $notice = "New admin user account created successfully!";
                } else {
                    $notice = "Password is required for a new admin user account.";
                }
            }
        }
    }
    if ($action === 'delete_user') {
        $id = intval($_POST['id']);
        if ($id === intval($_SESSION['admin_user_id'] ?? 0)) {
            $notice = "Error: You cannot delete your own currently logged-in admin account!";
        } else {
            $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            if ($count <= 1) {
                $notice = "Error: Cannot delete the last remaining admin account!";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $notice = "Admin user account deleted successfully!";
            }
        }
    }
}

// ==========================================
// FETCH ALL DATA FOR DASHBOARD RENDERING
// ==========================================
$profile = [];
$appointments = [];
$education = [];
$teaching_courses = [];
$software_courses = [];
$software_skills = [];
$leadership = [];
$research_papers = [];
$enrollments = [];
$bookings = [];
$inquiries = [];
$users = [];

if ($pdo) {
    try {
        $profile = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch() ?: [];
        $appointments = $pdo->query("SELECT * FROM appointments ORDER BY display_order ASC, id DESC")->fetchAll();
        $education = $pdo->query("SELECT * FROM education ORDER BY id DESC")->fetchAll();
        $teaching_courses = $pdo->query("SELECT * FROM teaching_courses ORDER BY display_order ASC, id DESC")->fetchAll();
        $software_courses = $pdo->query("SELECT * FROM software_courses ORDER BY id DESC")->fetchAll();
        $software_skills = $pdo->query("SELECT * FROM software_skills ORDER BY percentage DESC, id DESC")->fetchAll();
        $leadership = $pdo->query("SELECT * FROM leadership ORDER BY id DESC")->fetchAll();
        $research_papers = $pdo->query("SELECT * FROM research_papers ORDER BY publication_year DESC, id DESC")->fetchAll();
        $enrollments = $pdo->query("SELECT e.*, c.course_title FROM course_enrollments e LEFT JOIN software_courses c ON e.course_id = c.id ORDER BY e.id DESC")->fetchAll();
        $bookings = $pdo->query("SELECT * FROM appointment_bookings ORDER BY id DESC")->fetchAll();
        $inquiries = $pdo->query("SELECT * FROM contact_inquiries ORDER BY id DESC")->fetchAll();
        $users = $pdo->query("SELECT id, username FROM users ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        // Handle fetch errors gracefully
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Prof. Hassan Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('bg-amber-500', 'text-slate-950', 'font-bold');
                el.classList.add('bg-slate-900', 'text-slate-400');
            });
            document.getElementById(tabId).classList.remove('hidden');
            event.currentTarget.classList.remove('bg-slate-900', 'text-slate-400');
            event.currentTarget.classList.add('bg-amber-500', 'text-slate-950', 'font-bold');
        }

        function editAppt(data) {
            document.getElementById('appt_id').value = data.id;
            document.getElementById('appt_inst').value = data.institution;
            document.getElementById('appt_role').value = data.role_title;
            document.getElementById('appt_cat').value = data.category;
            document.getElementById('appt_desc').value = data.description;
            document.getElementById('appt_order').value = data.display_order;
            window.scrollTo({top: 200, behavior: 'smooth'});
        }

        function editEdu(data) {
            document.getElementById('edu_id').value = data.id;
            document.getElementById('edu_title').value = data.degree_title;
            document.getElementById('edu_inst').value = data.institution;
            document.getElementById('edu_loc').value = data.location;
            document.getElementById('edu_badge').value = data.badge_label;
            document.getElementById('edu_status').value = data.status;
            document.getElementById('edu_desc').value = data.description;
            window.scrollTo({top: 200, behavior: 'smooth'});
        }

        function editTeach(data) {
            document.getElementById('teach_id').value = data.id;
            document.getElementById('teach_title').value = data.course_title;
            document.getElementById('teach_badge').value = data.category_badge;
            document.getElementById('teach_icon').value = data.icon_class;
            document.getElementById('teach_order').value = data.display_order;
            document.getElementById('teach_desc').value = data.description;
            window.scrollTo({top: 200, behavior: 'smooth'});
        }

        function editSoft(data) {
            document.getElementById('soft_id').value = data.id;
            document.getElementById('soft_name').value = data.software_name;
            document.getElementById('soft_title').value = data.course_title;
            document.getElementById('soft_dur').value = data.duration;
            document.getElementById('soft_level').value = data.skill_level;
            document.getElementById('soft_price').value = data.price;
            document.getElementById('soft_icon').value = data.icon_class;
            document.getElementById('soft_status').value = data.status;
            document.getElementById('soft_desc').value = data.description;
            window.scrollTo({top: 200, behavior: 'smooth'});
        }

        function editSkill(data) {
            document.getElementById('sk_id').value = data.id;
            document.getElementById('sk_name').value = data.tool_name;
            document.getElementById('sk_prof').value = data.proficiency;
            document.getElementById('sk_perc').value = data.percentage;
            document.getElementById('sk_icon').value = data.icon_class;
            document.getElementById('sk_desc').value = data.description;
            window.scrollTo({top: 200, behavior: 'smooth'});
        }

        function editUser(data) {
            document.getElementById('user_id').value = data.id;
            document.getElementById('user_name').value = data.username;
            document.getElementById('user_pass').value = '';
            document.getElementById('user_pass').placeholder = 'Leave blank to keep current password';
            window.scrollTo({top: 200, behavior: 'smooth'});
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen">

    <!-- Navigation Header -->
    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center font-serif font-black text-slate-950">H</div>
            <div>
                <span class="block font-bold text-amber-400 font-serif">Prof. Hassan Hilowle</span>
                <span class="block text-[10px] text-slate-400 uppercase">Master Admin CRUD Control Panel</span>
            </div>
        </div>
        <div class="flex items-center space-x-4 text-xs">
            <span class="text-slate-400 hidden md:inline"><i class="fa-solid fa-user-circle text-amber-400 mr-1"></i> Logged in as: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong></span>
            <a href="<?php echo BASE_URL; ?>" target="_blank" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors"><i class="fa-solid fa-globe mr-1.5"></i> View Website</a>
            <a href="<?php echo BASE_URL; ?>admin.php?logout=1" class="px-3 py-2 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-rose-500 hover:text-white transition-colors"><i class="fa-solid fa-right-from-bracket mr-1.5"></i> Logout</a>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 py-8 space-y-8">
        
        <?php if (!empty($notice)): ?>
            <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 text-xs p-4 rounded-xl flex items-center shadow-lg">
                <i class="fa-solid fa-circle-check text-lg mr-3"></i> 
                <div><strong>Notification:</strong> <?php echo htmlspecialchars($notice); ?></div>
            </div>
        <?php endif; ?>

        <!-- Dashboard Navigation Tabs -->
        <div class="flex flex-wrap gap-2 pb-2 border-b border-slate-800">
            <button onclick="switchTab('enrollments-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-amber-500 text-slate-950 font-bold transition-all shadow-md">
                <i class="fa-solid fa-video mr-1.5"></i> Enrollments & Zoom
            </button>
            <button onclick="switchTab('profile-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-user-pen mr-1.5"></i> Profile & Images
            </button>
            <button onclick="switchTab('appointments-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-university mr-1.5"></i> Appointments
            </button>
            <button onclick="switchTab('education-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-graduation-cap mr-1.5"></i> Education
            </button>
            <button onclick="switchTab('teaching-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-book mr-1.5"></i> Teaching Modules
            </button>
            <button onclick="switchTab('software-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-laptop-code mr-1.5"></i> Software Workshops
            </button>
            <button onclick="switchTab('skills-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-microchip mr-1.5"></i> Skills
            </button>
            <button onclick="switchTab('leadership-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-briefcase mr-1.5"></i> Leadership
            </button>
            <button onclick="switchTab('papers-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-file-lines mr-1.5"></i> Research Papers
            </button>
            <button onclick="switchTab('bookings-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-calendar-check mr-1.5"></i> Consultations
            </button>
            <button onclick="switchTab('inquiries-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-envelope mr-1.5"></i> Inquiries
            </button>
            <button onclick="switchTab('users-tab')" class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider bg-slate-900 text-slate-400 font-semibold transition-all hover:text-white">
                <i class="fa-solid fa-users-gear mr-1.5"></i> Admin Users
            </button>
        </div>

        <!-- ================= 1. ENROLLMENTS & ZOOM ================= -->
        <div id="enrollments-tab" class="tab-content space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Student Course Enrollments & Zoom Management</h2>
            <?php if (empty($enrollments)): ?>
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center text-slate-500 text-xs">No student enrollments found.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($enrollments as $e): ?>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-xl">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3">
                                    <span class="font-bold text-white text-sm"><?php echo htmlspecialchars($e['student_name']); ?></span>
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full <?php echo ($e['approval_status'] === 'Approved') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'; ?>">
                                        <?php echo htmlspecialchars($e['approval_status']); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-amber-400 font-semibold"><i class="fa-solid fa-book mr-1"></i><?php echo htmlspecialchars($e['course_title'] ?? 'Software Course'); ?></p>
                                <p class="text-xs text-slate-400"><i class="fa-solid fa-envelope mr-1"></i><?php echo htmlspecialchars($e['student_email']); ?> | <i class="fa-solid fa-phone mr-1 ml-2"></i><?php echo htmlspecialchars($e['student_phone']); ?></p>
                                <p class="text-[11px] text-slate-500">Background: <?php echo htmlspecialchars($e['academic_background'] ?? 'N/A'); ?> | Shift: <?php echo htmlspecialchars($e['preferred_schedule']); ?></p>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                                <form method="POST" action="" class="space-y-2 bg-slate-950 p-4 rounded-xl border border-slate-800 flex-1">
                                    <input type="hidden" name="admin_action" value="update_enrollment">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $e['id']; ?>">
                                    <input type="text" name="zoom_join_url" value="<?php echo htmlspecialchars($e['zoom_join_url'] ?? ''); ?>" placeholder="https://zoom.us/j/..." class="w-full md:w-64 bg-slate-900 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:border-amber-500 focus:outline-none">
                                    <div class="flex items-center space-x-2">
                                        <select name="approval_status" class="bg-slate-900 border border-slate-800 rounded-lg px-3 py-1 text-xs text-white flex-1">
                                            <option value="Pending" <?php echo ($e['approval_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo ($e['approval_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Rejected" <?php echo ($e['approval_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 px-3 py-1 rounded-lg font-bold text-xs">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="" onsubmit="return confirm('Delete this enrollment?');">
                                    <input type="hidden" name="admin_action" value="delete_enrollment">
                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                    <button type="submit" class="p-3 bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl border border-rose-500/30 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================= 2. PROFILE SETTINGS & IMAGE UPLOADS ================= -->
        <div id="profile-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Edit Profile Settings & Upload Images</h2>
            <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <form method="POST" action="" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <input type="hidden" name="admin_action" value="update_profile">
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Full Name</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Short Display Name</label>
                        <input type="text" name="short_name" required value="<?php echo htmlspecialchars($profile['short_name'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Professional Title</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" required value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Location</label>
                        <input type="text" name="location" required value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Years of Experience</label>
                        <input type="number" name="years_exp" required value="<?php echo intval($profile['years_exp'] ?? 15); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Students Count Metric</label>
                        <input type="text" name="students_count" required value="<?php echo htmlspecialchars($profile['students_count'] ?? '1,000+'); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white">
                    </div>
                    
                    <!-- File Upload Input: Profile Photo -->
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Upload Profile Photo</label>
                        <input type="file" name="profile_img_file" accept="image/*" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400">
                        <?php if (!empty($profile['profile_img'])): ?>
                            <span class="text-[10px] text-slate-400 mt-1 block">Current: <?php echo htmlspecialchars($profile['profile_img']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- File Upload Input: Hero Background -->
                    <div>
                        <label class="block font-bold text-slate-300 uppercase mb-1">Upload Hero Background Image</label>
                        <input type="file" name="hero_bg_file" accept="image/*" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400">
                        <?php if (!empty($profile['hero_bg'])): ?>
                            <span class="text-[10px] text-slate-400 mt-1 block">Current: <?php echo htmlspecialchars($profile['hero_bg']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-300 uppercase mb-1">Professional Bio Summary</label>
                        <textarea name="bio_summary" rows="4" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white"><?php echo htmlspecialchars($profile['bio_summary'] ?? ''); ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase tracking-wider rounded-lg shadow-lg">Save Profile Changes & Upload Images</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= 3. INSTITUTIONAL APPOINTMENTS ================= -->
        <div id="appointments-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Institutional Appointments</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add / Edit Appointment</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_appointment">
                    <input type="hidden" name="id" id="appt_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Institution Name</label>
                        <input type="text" name="institution" id="appt_inst" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Role Title</label>
                        <input type="text" name="role_title" id="appt_role" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Category / Department</label>
                        <input type="text" name="category" id="appt_cat" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Display Order</label>
                        <input type="number" name="display_order" id="appt_order" value="0" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Description</label>
                        <textarea name="description" id="appt_desc" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Appointment</button>
                        <button type="button" onclick="document.getElementById('appt_id').value=''; document.querySelector('#appointments-tab form').reset();" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($appointments as $a): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 mb-2 inline-block"><?php echo htmlspecialchars($a['category']); ?></span>
                            <h4 class="text-base font-bold font-serif text-white"><?php echo htmlspecialchars($a['role_title']); ?></h4>
                            <p class="text-xs text-amber-300 font-semibold mb-2"><?php echo htmlspecialchars($a['institution']); ?></p>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($a['description']); ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end space-x-2">
                            <button onclick="editAppt(<?php echo htmlspecialchars(json_encode($a)); ?>)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg">Edit</button>
                            <form method="POST" action="" onsubmit="return confirm('Delete appointment?');">
                                <input type="hidden" name="admin_action" value="delete_appointment">
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 4. EDUCATION ================= -->
        <div id="education-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Academic Qualifications</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add / Edit Education</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_education">
                    <input type="hidden" name="id" id="edu_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Degree Title</label>
                        <input type="text" name="degree_title" id="edu_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Institution Name</label>
                        <input type="text" name="institution" id="edu_inst" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Location</label>
                        <input type="text" name="location" id="edu_loc" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Badge Label</label>
                        <input type="text" name="badge_label" id="edu_badge" required placeholder="e.g. Master's Degree" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Status</label>
                        <input type="text" name="status" id="edu_status" value="Completed" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Description</label>
                        <textarea name="description" id="edu_desc" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Qualification</button>
                        <button type="button" onclick="document.getElementById('edu_id').value=''; document.querySelector('#education-tab form').reset();" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($education as $ed): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 mb-2 inline-block"><?php echo htmlspecialchars($ed['badge_label']); ?></span>
                            <h4 class="text-base font-bold font-serif text-white"><?php echo htmlspecialchars($ed['degree_title']); ?></h4>
                            <p class="text-xs text-amber-400 font-semibold mb-2"><?php echo htmlspecialchars($ed['institution']); ?> (<?php echo htmlspecialchars($ed['location']); ?>)</p>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($ed['description']); ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end space-x-2">
                            <button onclick="editEdu(<?php echo htmlspecialchars(json_encode($ed)); ?>)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg">Edit</button>
                            <form method="POST" action="" onsubmit="return confirm('Delete education record?');">
                                <input type="hidden" name="admin_action" value="delete_education">
                                <input type="hidden" name="id" value="<?php echo $ed['id']; ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 5. TEACHING COURSES ================= -->
        <div id="teaching-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Teaching & Course Modules</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add / Edit Teaching Module</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_teaching_course">
                    <input type="hidden" name="id" id="teach_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Course Title</label>
                        <input type="text" name="course_title" id="teach_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Category Badge</label>
                        <input type="text" name="category_badge" id="teach_badge" required placeholder="e.g. UG / PG Level" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">FontAwesome Icon Class</label>
                        <input type="text" name="icon_class" id="teach_icon" value="fa-solid fa-calculator" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Display Order</label>
                        <input type="number" name="display_order" id="teach_order" value="0" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Description</label>
                        <textarea name="description" id="teach_desc" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Teaching Module</button>
                        <button type="button" onclick="document.getElementById('teach_id').value=''; document.querySelector('#teaching-tab form').reset();" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($teaching_courses as $tc): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="text-amber-400 text-xl mb-3"><i class="<?php echo htmlspecialchars($tc['icon_class']); ?>"></i></div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 mb-2 inline-block"><?php echo htmlspecialchars($tc['category_badge']); ?></span>
                            <h4 class="text-base font-bold font-serif text-white mb-2"><?php echo htmlspecialchars($tc['course_title']); ?></h4>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($tc['description']); ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end space-x-2">
                            <button onclick="editTeach(<?php echo htmlspecialchars(json_encode($tc)); ?>)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg">Edit</button>
                            <form method="POST" action="" onsubmit="return confirm('Delete module?');">
                                <input type="hidden" name="admin_action" value="delete_teaching_course">
                                <input type="hidden" name="id" value="<?php echo $tc['id']; ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 6. SOFTWARE WORKSHOPS ================= -->
        <div id="software-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Software Workshops & Zoom Classes</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add / Edit Software Workshop</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_software_course">
                    <input type="hidden" name="id" id="soft_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Software Name</label>
                        <input type="text" name="software_name" id="soft_name" required placeholder="e.g. IBM SPSS Statistics" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Course Title</label>
                        <input type="text" name="course_title" id="soft_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Duration</label>
                        <input type="text" name="duration" id="soft_dur" required placeholder="e.g. 3 Weeks (12 Hours)" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Skill Level</label>
                        <input type="text" name="skill_level" id="soft_level" required placeholder="e.g. Postgraduate Level" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="soft_price" required value="120.00" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">FontAwesome Icon</label>
                        <input type="text" name="icon_class" id="soft_icon" value="fa-solid fa-laptop-code" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Status</label>
                        <select name="status" id="soft_status" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                            <option value="Active">Active</option>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Description</label>
                        <textarea name="description" id="soft_desc" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Workshop</button>
                        <button type="button" onclick="document.getElementById('soft_id').value=''; document.querySelector('#software-tab form').reset();" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($software_courses as $sc): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded bg-blue-500/20 text-blue-300"><?php echo htmlspecialchars($sc['skill_level']); ?></span>
                                <span class="text-amber-400 font-bold text-sm">$<?php echo number_format($sc['price'], 2); ?></span>
                            </div>
                            <h4 class="text-base font-bold font-serif text-white mb-1"><?php echo htmlspecialchars($sc['course_title']); ?></h4>
                            <p class="text-xs text-slate-400 mb-3"><?php echo htmlspecialchars($sc['description']); ?></p>
                        </div>
                        <div class="pt-3 border-t border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-500"><i class="fa-regular fa-clock mr-1 text-amber-500"></i><?php echo htmlspecialchars($sc['duration']); ?></span>
                            <div class="flex space-x-2">
                                <button onclick="editSoft(<?php echo htmlspecialchars(json_encode($sc)); ?>)" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 font-semibold rounded-lg">Edit</button>
                                <form method="POST" action="" onsubmit="return confirm('Delete workshop?');">
                                    <input type="hidden" name="admin_action" value="delete_software_course">
                                    <input type="hidden" name="id" value="<?php echo $sc['id']; ?>">
                                    <button type="submit" class="px-3 py-1 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white font-semibold rounded-lg">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 7. SOFTWARE SKILLS ================= -->
        <div id="skills-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Software Skills & Tools</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add / Edit Software Skill</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_skill">
                    <input type="hidden" name="id" id="sk_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Tool Name</label>
                        <input type="text" name="tool_name" id="sk_name" required placeholder="e.g. STATA" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Proficiency Level</label>
                        <input type="text" name="proficiency" id="sk_prof" required placeholder="e.g. Advanced" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Percentage (%)</label>
                        <input type="number" name="percentage" id="sk_perc" required min="1" max="100" value="90" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">FontAwesome Icon</label>
                        <input type="text" name="icon_class" id="sk_icon" value="fa-solid fa-code" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Description</label>
                        <textarea name="description" id="sk_desc" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Skill</button>
                        <button type="button" onclick="document.getElementById('sk_id').value=''; document.querySelector('#skills-tab form').reset();" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($software_skills as $sk): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-base font-bold font-serif text-white"><?php echo htmlspecialchars($sk['tool_name']); ?></h4>
                                <span class="text-xs font-semibold px-2 py-0.5 bg-amber-500/20 text-amber-400 rounded-full"><?php echo htmlspecialchars($sk['proficiency']); ?></span>
                            </div>
                            <p class="text-xs text-slate-400 mb-4"><?php echo htmlspecialchars($sk['description']); ?></p>
                        </div>
                        <div class="pt-3 border-t border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-slate-500">Power: <strong class="text-white"><?php echo intval($sk['percentage']); ?>%</strong></span>
                            <div class="flex space-x-2">
                                <button onclick="editSkill(<?php echo htmlspecialchars(json_encode($sk)); ?>)" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 font-semibold rounded-lg">Edit</button>
                                <form method="POST" action="" onsubmit="return confirm('Delete skill?');">
                                    <input type="hidden" name="admin_action" value="delete_skill">
                                    <input type="hidden" name="id" value="<?php echo $sk['id']; ?>">
                                    <button type="submit" class="px-3 py-1 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white font-semibold rounded-lg">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 8. LEADERSHIP ================= -->
        <div id="leadership-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Leadership Experience</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add Leadership Role</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_leadership">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Role Title</label>
                        <input type="text" name="role_title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Organization</label>
                        <input type="text" name="organization" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Duration</label>
                        <input type="text" name="duration" required placeholder="e.g. 2013 – 2022" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Details</label>
                        <textarea name="details" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Leadership Role</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($leadership as $ld): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 mb-2 inline-block"><?php echo htmlspecialchars($ld['duration']); ?></span>
                            <h4 class="text-base font-bold font-serif text-white"><?php echo htmlspecialchars($ld['role_title']); ?></h4>
                            <p class="text-xs text-amber-300 font-semibold mb-2"><?php echo htmlspecialchars($ld['organization']); ?></p>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($ld['details']); ?></p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-800 flex justify-end">
                            <form method="POST" action="" onsubmit="return confirm('Delete leadership record?');">
                                <input type="hidden" name="admin_action" value="delete_leadership">
                                <input type="hidden" name="id" value="<?php echo $ld['id']; ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 9. RESEARCH PAPERS ================= -->
        <div id="papers-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Research Papers</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-plus-circle mr-1"></i> Add Research Paper</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_research_paper">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Paper Title</label>
                        <input type="text" name="title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Journal / Publication Name</label>
                        <input type="text" name="journal_name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Publication Year</label>
                        <input type="number" name="publication_year" required value="2026" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Paper URL / DOI</label>
                        <input type="url" name="paper_url" placeholder="https://..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-bold uppercase text-slate-300 mb-1">Abstract</label>
                        <textarea name="abstract_text" rows="3" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Research Paper</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <?php foreach ($research_papers as $rp): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex justify-between items-start gap-4">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-amber-400 mb-2 inline-block"><?php echo htmlspecialchars($rp['publication_year']); ?></span>
                            <h4 class="text-base font-bold font-serif text-white"><?php echo htmlspecialchars($rp['title']); ?></h4>
                            <p class="text-xs text-amber-400 italic mb-2"><?php echo htmlspecialchars($rp['journal_name']); ?></p>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($rp['abstract_text']); ?></p>
                        </div>
                        <form method="POST" action="" onsubmit="return confirm('Delete paper?');">
                            <input type="hidden" name="admin_action" value="delete_research_paper">
                            <input type="hidden" name="id" value="<?php echo $rp['id']; ?>">
                            <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= 10. CONSULTATION BOOKINGS ================= -->
        <div id="bookings-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage 1-on-1 Consultation Bookings</h2>
            <?php if (empty($bookings)): ?>
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center text-slate-500 text-xs">No consultation bookings found.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($bookings as $b): ?>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white text-sm"><?php echo htmlspecialchars($b['student_name']); ?></span>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-amber-500/20 text-amber-400"><?php echo htmlspecialchars($b['status']); ?></span>
                                </div>
                                <p class="text-xs text-amber-400 font-semibold"><?php echo htmlspecialchars($b['consultation_type']); ?></p>
                                <p class="text-xs text-slate-400"><i class="fa-solid fa-envelope mr-1"></i><?php echo htmlspecialchars($b['student_email']); ?> | <i class="fa-solid fa-phone mr-1 ml-2"></i><?php echo htmlspecialchars($b['student_phone']); ?></p>
                                <p class="text-xs text-slate-300">Date: <strong class="text-white"><?php echo htmlspecialchars($b['preferred_date']); ?></strong> at <?php echo htmlspecialchars($b['preferred_time']); ?></p>
                                <p class="text-[11px] text-slate-500">Notes: <?php echo htmlspecialchars($b['notes'] ?? 'None'); ?></p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-800 flex justify-between items-center">
                                <form method="POST" action="" class="flex items-center space-x-2">
                                    <input type="hidden" name="admin_action" value="update_booking">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <select name="booking_status" class="bg-slate-950 border border-slate-800 rounded px-2 py-1 text-xs text-white">
                                        <option value="Pending" <?php echo ($b['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Confirmed" <?php echo ($b['status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Completed" <?php echo ($b['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1 bg-amber-500 text-slate-950 text-xs font-bold rounded">Update</button>
                                </form>
                                <form method="POST" action="" onsubmit="return confirm('Delete booking?');">
                                    <input type="hidden" name="admin_action" value="delete_booking">
                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                    <button type="submit" class="px-2.5 py-1 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs rounded">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================= 11. CONTACT INQUIRIES ================= -->
        <div id="inquiries-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Contact Inquiries</h2>
            <?php if (empty($inquiries)): ?>
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center text-slate-500 text-xs">No contact inquiries found.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($inquiries as $iq): ?>
                        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex justify-between items-start gap-4">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-500/20 text-blue-300"><?php echo htmlspecialchars($iq['subject_type']); ?></span>
                                <h4 class="text-base font-bold text-white"><?php echo htmlspecialchars($iq['sender_name']); ?> <span class="text-xs text-slate-400 font-normal">(<?php echo htmlspecialchars($iq['sender_email']); ?>)</span></h4>
                                <p class="text-xs text-amber-400">Institution: <?php echo htmlspecialchars($iq['institution'] ?? 'N/A'); ?></p>
                                <p class="text-xs text-slate-300 mt-2 bg-slate-950 p-3 rounded-lg border border-slate-800"><?php echo htmlspecialchars($iq['message']); ?></p>
                            </div>
                            <form method="POST" action="" onsubmit="return confirm('Delete inquiry?');">
                                <input type="hidden" name="admin_action" value="delete_inquiry">
                                <input type="hidden" name="id" value="<?php echo $iq['id']; ?>">
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ================= 12. USER MANAGEMENT ================= -->
        <div id="users-tab" class="tab-content hidden space-y-6">
            <h2 class="text-xl font-bold font-serif text-white">Manage Administrator Accounts</h2>
            <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-4"><i class="fa-solid fa-user-plus mr-1"></i> Add / Edit Admin User</h3>
                <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <input type="hidden" name="admin_action" value="save_user">
                    <input type="hidden" name="id" id="user_id" value="">
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Username</label>
                        <input type="text" name="username" id="user_name" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-slate-300 mb-1">Password</label>
                        <input type="password" name="password" id="user_pass" placeholder="Required for new, optional for edit" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div class="md:col-span-2 flex gap-4">
                        <button type="submit" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold uppercase rounded-lg">Save Admin User</button>
                        <button type="button" onclick="document.getElementById('user_id').value=''; document.querySelector('#users-tab form').reset(); document.getElementById('user_pass').placeholder='Required for new, optional for edit';" class="px-4 py-3 bg-slate-800 text-slate-300 rounded-lg">Clear Form</button>
                    </div>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($users as $usr): ?>
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex justify-between items-center">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold mb-2">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <h4 class="text-base font-bold text-white"><?php echo htmlspecialchars($usr['username']); ?></h4>
                            <span class="text-[10px] text-slate-500">ID: #<?php echo intval($usr['id']); ?></span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($usr)); ?>)" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg">Edit</button>
                            <?php if (intval($usr['id']) !== intval($_SESSION['admin_user_id'] ?? 0)): ?>
                                <form method="POST" action="" onsubmit="return confirm('Delete this admin user?');">
                                    <input type="hidden" name="admin_action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $usr['id']; ?>">
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/20 text-rose-300 hover:bg-rose-500 hover:text-white text-xs font-semibold rounded-lg">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
</body>
</html>