<?php
require_once '../../config/db.php';

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;

if ($student_id <= 0) {
    header('Location: manage_students.php');
    exit;
}

// Fetch student details
$stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: manage_students.php');
    exit;
}

$student = $result->fetch_assoc();
$first_name = $student['first_name'];
$last_name  = $student['last_name'];

$whatsapp_number = "27763803862";


$message = "Hi, My name is $first_name $last_name, I just registered for JD Tutoring.";
$whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($message);
?>

<div class="success-wrapper">
    <div class="success-card">
        <div class="icon-circle">
            <i class="bi bi-check-circle-fill"></i>
        </div>

        <h1 class="title">Welcome</h1>
        <h2 class="name">
            <?= htmlspecialchars($first_name . ' ' . $last_name); ?> 
        </h2>

        <p class="description">
            Your registration was successful!  
            Please send us a WhatsApp message so we can finalize your enrollment.
        </p>

        <a href="<?= $whatsapp_url; ?>" target="_blank" class="whatsapp-btn">
            <i class="bi bi-whatsapp"></i>
            Send Message on WhatsApp
        </a>
    </div>
</div>

<style>
/* Page background */
body {
    background: linear-gradient(135deg, #eef2ff, #dbeafe);
    min-height: 100vh;
}

/* Wrapper */
.success-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 1rem;
}

/* Glass card */
.success-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(18px);
    border-radius: 28px;
    padding: 3rem 2.5rem;
    max-width: 520px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    animation: fadeUp 0.8s ease forwards;
}

/* Success icon */
.icon-circle {
    width: 110px;
    height: 110px;
    margin: 0 auto 1.5rem;
    background: #dcfce7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bounceIn 0.8s ease forwards;
}

.icon-circle i {
    font-size: 3.5rem;
    color: #16a34a;
}

/* Text */
.title {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    margin-bottom: 0.3rem;
    opacity: 0;
    animation: fadeInText 0.8s ease forwards;
    animation-delay: 0.5s;
}

.name {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 1rem;
    opacity: 0;
    animation: fadeInText 0.8s ease forwards;
    animation-delay: 0.7s;
}

.description {
    color: #6b7280;
    margin-bottom: 2rem;
    line-height: 1.6;
    opacity: 0;
    animation: fadeInText 0.8s ease forwards;
    animation-delay: 0.9s;
}

/* WhatsApp button */
.whatsapp-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    background: #25D366;
    color: #fff;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 1rem;
    border-radius: 16px;
    box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
    transition: all 0.25s ease;
    opacity: 0;
    animation: fadeInText 0.8s ease forwards;
    animation-delay: 1.1s;
}

.whatsapp-btn:hover {
    background: #128C7E;
    transform: scale(1.03);
    color: #fff;
}

/* Animations */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); }
}

@keyframes fadeInText {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php require_once '../../templates/footer.php'; ?>
