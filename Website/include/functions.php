<?php
if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    header( 'HTTP/1.0 404 Not Found', TRUE, 404 );
    die();
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('include\conn_fn.php');
define('OTAC_EXP_MINUTES', 3);

function updateSessionLifetime(){
    $timeoutSeconds = 1200;
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeoutSeconds)) {
        session_unset();     // unset $_SESSION variable for the run-time 
        session_destroy();   // destroy session data in storage
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > $timeoutSeconds) {

        session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
        $_SESSION['CREATED'] = time();  // update creation time
    }
}

function redirect($url, $statusCode = 200)
{
   header('Location: ' . $url);
   exit();
}

function getRedLabelStyle($isHidden) {
    if(is_null($isHidden) or $isHidden == true){
        echo "\"color: red; display: none;\"";
        return;
    }
    echo "\"color: red;\"";
    return;
}

function getUserAndPwdStyle($needs_mfa) {
    if ($needs_mfa == 1) {
    echo " style=\"display: none;\"";
    } else {
    echo " required=\"\"";
    }
    }

function getVerCodeStyle($needs_mfa) {
    if ($needs_mfa == 0) {
        echo " style=\"display: none;\"";
    }
    else {
        echo " required=\"\"";
    }
    }

function displayEmailSentMessage($needs_mfa, $email) {
    $style = "";
    if ($needs_mfa == 0) {
        $style = " style=\"display: none;\"";
    }
    echo "<label " . $style . ">Code was sent to " . $email . "<br/>Code is valid for " . constant('OTAC_EXP_MINUTES') . " minute(s)</label>";
}

function displayLogoutLink($needs_mfa) {
    $style = "";
    if ($needs_mfa == 0) {
        $style = " style=\"display: none;\"";
    }
    echo "<a href=\"logout.php\" " . $style . " >Logout</a>";
}

function displayWrongCodeMessage($wrong_code) {
    $style = "";
    if ($wrong_code == 0) {
        $style = " style=\"display: none;\"";
    }
    else {
        $style = "style=\"color: red;\"";
    }
    echo "<label " . $style . ">Invalid or expired code</label>";
}

function getPreparedStatement($con, string $sql){
    $stmt = mysqli_stmt_init($con);
    if(!mysqli_stmt_prepare($stmt, $sql)){
        echo "Failed to prepare statement\n";
        return null;
    }
    return $stmt;
}

function getDataFromDB (string $sql, string $types = "", ...$arguments){
    $con = getDBConnection();
    $stmt = getPreparedStatement($con, $sql);

    if (!empty($arguments)) {
        mysqli_stmt_bind_param($stmt, $types, ...$arguments);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        echo mysqli_error($con);
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $result;
}

function getDBConn()
{
    return getDBConnection();
}

function getDataFromDBWithConn ($con, string $sql, string $types = "", ...$arguments){
    $stmt = getPreparedStatement($con, $sql);

    if (!empty($arguments)) {
        mysqli_stmt_bind_param($stmt, $types, ...$arguments);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        echo mysqli_error($con);
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function getUserInfo(string $username, string $password){
    $dbData = getDataFromDB("CALL get_user_info(?, ?)", "si", $username, constant('OTAC_EXP_MINUTES'));

    $userInfo = array(
        'user_id' => null,
        'email' => null,
        'is_mfa_enabled' => null,
        'otac' => null,
        'otac_expires_at' => null,
        'is_valid' => false
    );

    if ($row = mysqli_fetch_array($dbData)) {
        if (password_verify($password, $row['pwd_hash'])) {
            $userInfo['user_id'] = $row['user_id'];
            $userInfo['user_name'] = $row['user_name'];
            $userInfo['email'] = $row['email'];
            $userInfo['is_mfa_enabled'] = $row['is_mfa_enabled'];
            $userInfo['otac'] = $row['otac'];
            $userInfo['otac_expires_at'] = $row['otac_expires_at'];
            $userInfo['is_valid'] = true;
        }
    }
    return $userInfo;
}

function getSearchResults($ssn_search, $loan_search, $fullname_search)
{
    $ssn_filter = $ssn_search != "" ? "AND ssn LIKE '%$ssn_search%'" : "";
    $loan_filter = $loan_search != "" ? "AND loan_number LIKE '%$loan_search%'" : "";
    $fullname_filter = $fullname_search != "" ? "AND full_name LIKE '%$fullname_search%'" : "";
    $sql = "SELECT * FROM v_search_results WHERE 1=1 $ssn_filter $loan_filter $fullname_filter LIMIT 1000";

    return getDataFromDB($sql);
}

function getPaymentExportResults($project_filter_array, $date_from, $date_to)
{
    $project_filter_string = implode(",", $project_filter_array);
    $sql = "SELECT * FROM v_payment_info WHERE company_id IN ($project_filter_string) AND payment_date BETWEEN ? AND ?";
    
    return getDataFromDB($sql, "ss", $date_from, $date_to);
}

function getPortfolioExportResults($project_filter_array, $debt_from, $debt_to)
{
    $project_filter_string = implode(",", $project_filter_array);
    $sql = "SELECT * FROM v_loan_general_info WHERE company_id IN ($project_filter_string) AND total_debt BETWEEN ? AND ?";
    
    return getDataFromDB($sql, "dd", $debt_from, $debt_to);
}

function getLoanOverview($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_loan_general_info WHERE loan_id = ?", "s", $loan_id);
}

function getPaymentDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_payment_info WHERE loan_id = ? ORDER BY payment_date DESC", "s", $loan_id);
}

function getAddressDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_address_info WHERE loan_id = ? ORDER BY is_active DESC, is_primary DESC", "s", $loan_id);
}

function getPhoneDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_phone_info WHERE loan_id = ? ORDER BY is_active DESC, is_primary DESC", "s", $loan_id);
}

function getOtherLoansList($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_borrowers_loans_links WHERE loan_id = ?", "s", $loan_id);
}

function getLoanDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_loan_info WHERE loan_id = ?", "s", $loan_id);
}

function getBorrowerDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_borrower_info WHERE loan_id = ?", "s", $loan_id);
}

function getLoanContragentInfoDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT t.* FROM (SELECT 1 AS dummy) AS d LEFT JOIN (SELECT * FROM v_loan_contragent_info WHERE loan_id = ?) AS t ON 1=1", "s", $loan_id);
}

function getActivityDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_activity_info WHERE loan_id = ?", "s", $loan_id);
}

function getTrialDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_trial_info WHERE loan_id = ?", "s", $loan_id);
}

function getLoanExpensesDetails($con, $loan_id)
{
    return getDataFromDBWithConn($con, "SELECT * FROM v_loan_expenses_info WHERE loan_id = ?", "s", $loan_id);
}

function getProjectList()
{
    return getDataFromDB("SELECT company_id, company_name FROM dim_company ORDER BY company_name");
}

function uploadSourceFiles($company_name, $user_id)
{
    $success = true;
    $import_results = "";

    $basedir = getFilesUploadDir();
    $uploaddir = "$basedir\\$company_name";

    // clear folder first
    $files = glob("$uploaddir\\*"); // get all file names
    foreach($files as $file){ // iterate files
    if(is_file($file)) {
        unlink($file); // delete file
    }
    }
    
    $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );

    // upload files
    foreach ($_FILES["userfiles"]["error"] as $key => $error) {
        $filename = basename($_FILES["userfiles"]["name"][$key]);

        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["userfiles"]["tmp_name"][$key];
            $name = "$uploaddir\\$filename";

            if(move_uploaded_file($tmp_name, $name)){
                $import_results = $import_results . "SUCCESS uploading $filename to $uploaddir" . "<br>";
            } else {
                $import_results = $import_results . "ERROR uploading $filename to $uploaddir" . "<br>";
                $success = false;
            }
        } else {
            $import_results = $import_results . "ERROR $filename: " . $phpFileUploadErrors[$_FILES["userfiles"]["error"][$key]] . "<br>";
            $success = false;
        }
    }

    // create flag file if all is ok
    if($success) {
        file_put_contents("$uploaddir\\flag.txt", $user_id);
        $import_results = $import_results . "<br>" . "FLAG file created";
    }

    return $import_results;
}

function sendVerificationMail($email, $otac, $otac_expires_at)
{
    $from = "eAttorney CRM<aluploadservice@outlook.com>";
    $to = $email;
    $subject = "[" . gethostname() . "] Login verification";
    $message = "Verification code: " . $otac . PHP_EOL . "Valid through: " . $otac_expires_at;
    $headers = array('From' => $from);

    mail( $to, $subject, $message, $headers);
}
?>