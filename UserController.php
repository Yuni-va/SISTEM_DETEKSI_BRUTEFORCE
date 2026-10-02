<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

define("AUTH_LOGIN_PATH","../../login.php");
require __DIR__."/../middleware/Admin.php";
require __DIR__."/../../config/database.php";
require __DIR__."/../models/User.php";
require __DIR__."/../models/ActivityLog.php";
require __DIR__."/../models/SystemLog.php";
require __DIR__."/../models/Setting.php";

$user = new User($conn);
$activity = new ActivityLog($conn);
$system = new SystemLog($conn);
$setting = new Setting($conn);

/*
|--------------------------------------------------------------------------
| Tambah User
|--------------------------------------------------------------------------
*/

if(isset($_POST['add'])){

    require_csrf();

    $fullname = input_string($_POST,'fullname',100);
    $username = input_string($_POST,'username',100);
    $email    = input_string($_POST,'email',150);
    $password = (string) ($_POST['password'] ?? "");
    $role     = valid_choice($_POST['role'] ?? "User",["Administrator","User"],"User");
    $status   = valid_choice($_POST['status'] ?? "active",["active","inactive"],"active");

    if($fullname === "" || $username === "" || $email === "" || $password === ""){
        $_SESSION['error']="Semua field wajib diisi.";
        header("Location: ../../users.php");
        exit;
    }

    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $_SESSION['error']="Format email tidak valid.";
        header("Location: ../../users.php");
        exit;
    }

    if(strlen($password) < 8){
        $_SESSION['error']="Password minimal 8 karakter.";
        header("Location: ../../users.php");
        exit;
    }

    if($user->usernameExists($username)){
        $_SESSION['error']="Username sudah digunakan.";
        header("Location: ../../users.php");
        exit;
    }

    if($user->emailExists($email)){
        $_SESSION['error']="Email sudah digunakan.";
        header("Location: ../../users.php");
        exit;
    }

    if($user->insert(
        $fullname,
        $username,
        $email,
        $password,
        $role,
        $status
    )){

        $activity->save($_SESSION['id'],"Menambahkan user ".$username);

        $system->save(
            "User ".$_SESSION['username']." menambahkan user ".$username,
            "INFO"
        );

        $_SESSION['success']="User berhasil ditambahkan.";

    }else{

        $_SESSION['error']="Gagal menambahkan user.";

    }

    header("Location: ../../users.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if(isset($_POST['update'])){

    require_csrf();

    $id       = (int) ($_POST['id'] ?? 0);
    $fullname = input_string($_POST,'fullname',100);
    $username = input_string($_POST,'username',100);
    $email    = input_string($_POST,'email',150);
    $role     = valid_choice($_POST['role'] ?? "User",["Administrator","User"],"User");
    $status   = valid_choice($_POST['status'] ?? "active",["active","inactive"],"active");
    $password = (string) ($_POST['password'] ?? "");

    if($id <= 0 || $fullname === "" || $username === "" || $email === ""){
        $_SESSION['error']="Data user tidak lengkap.";
        header("Location: ../../users.php");
        exit;
    }

    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $_SESSION['error']="Format email tidak valid.";
        header("Location: ../../users.php?edit=".$id);
        exit;
    }

    if($password !== "" && strlen($password) < 8){
        $_SESSION['error']="Password minimal 8 karakter.";
        header("Location: ../../users.php?edit=".$id);
        exit;
    }

    if($user->usernameExistsExcept($username,$id)){
        $_SESSION['error']="Username sudah digunakan oleh user lain.";
        header("Location: ../../users.php?edit=".$id);
        exit;
    }

    if($user->emailExistsExcept($email,$id)){
        $_SESSION['error']="Email sudah digunakan oleh user lain.";
        header("Location: ../../users.php?edit=".$id);
        exit;
    }

    if($user->update(
        $id,
        $fullname,
        $username,
        $email,
        $role,
        $status
    )){

        if(!empty($password)){
            $user->updatePassword($id,$password);
        }

        $activity->save($_SESSION['id'],"Mengubah data user ".$username);

        $system->save(
            "User ".$_SESSION['username']." mengubah user ".$username,
            "INFO"
        );

        $_SESSION['success']="Data user berhasil diupdate.";

    }else{

        $_SESSION['error']="Gagal mengupdate user.";

    }

    header("Location: ../../users.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Hapus User
|--------------------------------------------------------------------------
*/

if(isset($_POST['delete'])){

    require_csrf();

    $id = (int) ($_POST['id'] ?? 0);

    $old = $user->find($id);

    if($old && $user->delete($id)){

        $activity->save($_SESSION['id'],"Menghapus user ".$old['username']);

        $system->save(
            "User ".$_SESSION['username']." menghapus user ".$old['username'],
            "WARNING"
        );

        $_SESSION['success']="User berhasil dihapus.";

    }else{

        $_SESSION['error']="User gagal dihapus.";

    }

    header("Location: ../../users.php");
    exit;

}

header("Location: ../../users.php");
exit;
