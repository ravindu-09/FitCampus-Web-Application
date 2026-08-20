<?php
// backend/auth/register_process.php
session_start();
require_once '../../includes/db_connection.php';

$step = $_GET['step'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../views/auth/register_step1.php");
    exit();
}

// STEP 1 HANDLER

if ($step == '1') {
    $full_name = trim($_POST['full_name'] ?? '');
    $reg_no    = trim($_POST['reg_no'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $faculty   = trim($_POST['faculty'] ?? '');

    if (empty($full_name) || empty($reg_no) || empty($email) || empty($faculty)) {
        $_SESSION['error'] = "Please fill in all academic details.";
        header("Location: ../../views/auth/register_step1.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email OR reg_no = :reg_no LIMIT 1");
    $stmt->execute([':email' => $email, ':reg_no' => $reg_no]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "An account with this Email or Registration Number already exists.";
        header("Location: ../../views/auth/register_step1.php");
        exit();
    }

    $_SESSION['reg_step1'] = [
        'full_name' => $full_name,
        'reg_no'    => $reg_no,
        'email'     => $email,
        'faculty'   => $faculty
    ];

    header("Location: ../../views/auth/register_step2.php");
    exit();
}

// STEP 2 HANDLER

if ($step == '2') {
    $dob               = trim($_POST['dob'] ?? '');
    $gender            = trim($_POST['gender'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';

    if (empty($dob) || empty($gender) || empty($emergency_contact) || empty($password)) {
        $_SESSION['error'] = "Please fill in all profile and security fields.";
        header("Location: ../../views/auth/register_step2.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../../views/auth/register_step2.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../../views/auth/register_step2.php");
        exit();
    }

    $_SESSION['reg_step2'] = [
        'dob'               => $dob,
        'gender'            => $gender,
        'emergency_contact' => $emergency_contact,
        'password'          => $password
    ];

    header("Location: ../../views/auth/register_step3.php");
    exit();
}

// STEP 3 HANDLER

if ($step == '3') {
    if (!isset($_SESSION['reg_step1']) || !isset($_SESSION['reg_step2'])) {
        header("Location: ../../views/auth/register_step1.php");
        exit();
    }

    $nic = trim($_POST['nic'] ?? '');
    if (empty($nic)) {
        $_SESSION['error'] = "Please provide your valid NIC Number.";
        header("Location: ../../views/auth/register_step3.php");
        exit();
    }

    $upload_dir = '../../assets/images/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    function upload_doc($file_key, $upload_dir, $prefix) {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            return null;
        }
        $filename = $prefix . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_dir . $filename);
        return $filename;
    }

    $id_front      = upload_doc('id_front', $upload_dir, 'id_f');
    $id_back       = upload_doc('id_back', $upload_dir, 'id_b');
    $profile_image = upload_doc('profile_image', $upload_dir, 'avatar');

    if (!$id_front || !$id_back || !$profile_image) {
        $_SESSION['error'] = "Please upload valid images (JPG/PNG) for all required document verification fields.";
        header("Location: ../../views/auth/register_step3.php");
        exit();
    }

    try {
        $hashed_pwd = password_hash($_SESSION['reg_step2']['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO users 
                (reg_no, email, password_hash, full_name, faculty, nic, dob, gender, emergency_contact, profile_image, id_front_image, id_back_image, role, status) 
                VALUES 
                (:reg_no, :email, :pwd, :name, :faculty, :nic, :dob, :gender, :emergency, :avatar, :id_f, :id_b, 'member', 'pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reg_no'    => $_SESSION['reg_step1']['reg_no'],
            ':email'     => $_SESSION['reg_step1']['email'],
            ':pwd'       => $hashed_pwd,
            ':name'      => $_SESSION['reg_step1']['full_name'],
            ':faculty'   => $_SESSION['reg_step1']['faculty'],
            ':nic'       => $nic,
            ':dob'       => $_SESSION['reg_step2']['dob'],
            ':gender'    => $_SESSION['reg_step2']['gender'],
            ':emergency' => $_SESSION['reg_step2']['emergency_contact'],
            ':avatar'    => $profile_image,
            ':id_f'      => $id_front,
            ':id_b'      => $id_back
        ]);

        unset($_SESSION['reg_step1']);
        unset($_SESSION['reg_step2']);

        $_SESSION['registration_success'] = "Registration submitted successfully! Your account is currently under review by the Administrator. You will be notified via email once approved.";
        header("Location: ../../views/auth/login.php");
        exit();

    } catch (\PDOException $e) {
        error_log("Registration DB Error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during account registration. Please try again.";
        header("Location: ../../views/auth/register_step3.php");
        exit();
    }
}