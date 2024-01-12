<?php
include_once('include\functions.php');
session_start();
updateSessionLifetime();
$isLoginValidated = null;
if (!isset($_SESSION["needs_mfa"])) {
  $_SESSION["needs_mfa"] = 0;
  $_SESSION["wrong_code"] = 0;
}

if (!isset($_SESSION["email"])) {
  $_SESSION["email"] = "";
}
// echo $_SESSION["needs_mfa"] . "<br>";
// echo $_SESSION["email"] . "<br>";

if(array_key_exists('login-button', $_POST) && $_SESSION["needs_mfa"] == 0) {
  $username = $_POST["username"];
  $password = $_POST["password"];

  $userInfo = getUserInfo($username, $password);
  $isLoginValidated = $userInfo['is_valid'];

  if ($isLoginValidated) {
    $_SESSION["siteroot"] = __DIR__;
    $_SESSION["userloggedin"] = true;
    $_SESSION["username"] = $userInfo['user_name'];
    $_SESSION["user_id"] = $userInfo['user_id'];
    $_SESSION["needs_mfa"] = $userInfo['is_mfa_enabled'];
    $_SESSION["otac"] = $userInfo['otac'];
    $_SESSION["otac_expires_at"] = $userInfo['otac_expires_at'];
    $_SESSION["email"] = $userInfo['email'];

    if ($_SESSION["needs_mfa"]) {
      sendVerificationMail($userInfo['email'], $userInfo['otac'], $userInfo['otac_expires_at']);
      redirect('login.php');
    }
    redirect('index.php');
  }
}

if(array_key_exists('login-button', $_POST) && $_SESSION["needs_mfa"] == 1) {
    if ($_POST["verification-code"] == $_SESSION["otac"] &&
    date_create('now', timezone_open('Europe/Kiev')) <= date_create($_SESSION["otac_expires_at"])) {
      redirect('index.php');
    }
    else{
      $_SESSION["wrong_code"] = 1;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <title>eAttorney CRM Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/login-page-style.css">

</head>

<body>
  <section class="ftco-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 text-center mb-5">
          <h2 class="heading-section">Welcome to eAttorney CRM</h2>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
          <div class="login-wrap p-4 p-md-5">
            <div class="icon d-flex align-items-center justify-content-center">
              <span class="fa fa-user-o"></span>
            </div>
            <h3 class="text-center mb-4">Sign In</h3>
            <form method="POST" class="login-form">
              <div class="form-group">
                <input type="text" class="form-control rounded-left" placeholder="Username" name="username"
                <?php getUserAndPwdStyle($_SESSION["needs_mfa"])?>>
              </div>
              <div class="form-group d-flex">
                <input type="password" class="form-control rounded-left" placeholder="Password" name="password"
                <?php getUserAndPwdStyle($_SESSION["needs_mfa"])?>>
              </div>
              <div class="form-group d-flex">
                <input type="text" class="form-control rounded-left" placeholder="Verification Code" name="verification-code"
                <?php getVerCodeStyle($_SESSION["needs_mfa"])?>>
              </div>
              <div class="form-group">
                <button type="submit" name="login-button"
                  class="form-control btn btn-success rounded submit px-3">Login</button>
              </div>
              <div class="text-center">
                <label style=<?php getRedLabelStyle($isLoginValidated)?>>Invalid username or password!</label>
              </div>
              <div class="text-center">
                <?php displayEmailSentMessage($_SESSION["needs_mfa"], $_SESSION["email"])?>
              </div>
              <div class="text-center">
                <?php displayWrongCodeMessage($_SESSION["wrong_code"])?>
              </div>
              <div class="text-center">
                <?php displayLogoutLink($_SESSION["needs_mfa"])?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>


</body>

</html>