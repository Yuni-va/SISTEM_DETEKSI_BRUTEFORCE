<?php

session_start();

define("AUTH_LOGIN_PATH","../../login.php");
require "../middleware/Auth.php";

require "../../config/database.php";
require "../models/User.php";
require "../models/ActivityLog.php";
require "../models/SystemLog.php";

$userModel = new User($conn);
$activityModel = new ActivityLog($conn);
$systemModel = new SystemLog($conn);

if(isset($_POST['update_profile'])){

    require_csrf();

    $id = (int) $_SESSION['id'];
    $fullname = input_string($_POST,'fullname',100);
    $username = input_string($_POST,'username',100);
    $email = input_string($_POST,'email',150);
    $password = (string) ($_POST['password'] ?? "");

    if($fullname === "" || $username === "" || $email === ""){
        $_SESSION['error']="Nama, username, dan email wajib diisi.";
        header("Location: ../../profile.php");
        exit;
    }

    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $_SESSION['error']="Format email tidak valid.";
        header("Location: ../../profile.php");
        exit;
    }

    if($password !== "" && strlen($password) < 8){
        $_SESSION['error']="Password minimal 8 karakter.";
        header("Location: ../../profile.php");
        exit;
    }

    $currentUser = $userModel->find($id);

    if(!$currentUser){
        $_SESSION['error']="Data profil tidak ditemukan.";
        header("Location: ../../profile.php");
        exit;
    }

    if($userModel->usernameExistsExcept($username,$id)){
        $_SESSION['error']="Username sudah digunakan oleh user lain.";
        header("Location: ../../profile.php");
        exit;
    }

    if($userModel->emailExistsExcept($email,$id)){
        $_SESSION['error']="Email sudah digunakan oleh user lain.";
        header("Location: ../../profile.php");
        exit;
    }

    $photo = $currentUser['profile_photo'] ?? "default.png";

    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE){

        if($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK){
            $_SESSION['error']="Upload foto profil gagal.";
            header("Location: ../../profile.php");
            exit;
        }

        if($_FILES['profile_photo']['size'] > 2 * 1024 * 1024){
            $_SESSION['error']="Ukuran foto profil maksimal 2MB.";
            header("Location: ../../profile.php");
            exit;
        }

        $allowedExtensions = ["jpg","jpeg","png","webp","gif"];
        $extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

        if(!in_array($extension,$allowedExtensions)){
            $_SESSION['error']="Format foto harus JPG, PNG, WEBP, atau GIF.";
            header("Location: ../../profile.php");
            exit;
        }

        if(getimagesize($_FILES['profile_photo']['tmp_name']) === false){
            $_SESSION['error']="File yang diupload bukan gambar valid.";
            header("Location: ../../profile.php");
            exit;
        }

        $uploadDir = "../../assets/uploads/profile/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0755,true);
        }

        $newPhoto = "profile_".$id."_".time().".".$extension;
        $target = $uploadDir.$newPhoto;

        if(!move_uploaded_file($_FILES['profile_photo']['tmp_name'],$target)){
            $_SESSION['error']="Foto profil gagal disimpan.";
            header("Location: ../../profile.php");
            exit;
        }

        $oldPhotoPath = $uploadDir.$photo;

        if($photo !== "default.png" && is_file($oldPhotoPath)){
            unlink($oldPhotoPath);
        }

        $photo = $newPhoto;
        $userModel->updateProfilePhoto($id,$photo);
    }

    if($userModel->updateProfile($id,$fullname,$username,$email)){

        if($password !== ""){
            $userModel->updatePassword($id,$password);
        }

        $_SESSION['fullname']=$fullname;
        $_SESSION['username']=$username;
        $_SESSION['profile_photo']=$photo;

        $activityModel->save($id,"Mengubah profil administrator");
        $systemModel->save("Administrator ".$username." mengubah profil.","INFO");

        $_SESSION['success']="Profil berhasil diperbarui.";

    }else{

        $_SESSION['error']="Profil gagal diperbarui.";

    }

    header("Location: ../../profile.php");
    exit;
}

header("Location: ../../profile.php");
exit;
