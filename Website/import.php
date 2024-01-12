<?php
session_start();
include_once('include\functions.php');
updateSessionLifetime();
if(!isset($_SESSION["userloggedin"])){
	redirect("login.php");
	exit();
}

if(array_key_exists('send-files', $_POST)) {
    $_SESSION['import_results'] = uploadSourceFiles($_POST["projects"], $_SESSION["user_id"]);
    redirect('import.php');
}

$project_list = getProjectList();
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
        integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="/css/bootstrap-table.min-index-page.css"> -->
    <link rel="stylesheet" href="https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css">

    <title>eAttorney CRM - Import</title>
</head>

<body>
    <?php
  include_once('include\navbar.php');
?>

    <div class="container-fluid mt-2">
        <br>
        <br>
        <form enctype="multipart/form-data" action="" method="POST">
            <div class="form-row mt-2">

                <div class="col-md"> <label class="col-form-label-sm mb-0 pb-0">Project</label></div>  
                <div class="col-md"></div>
                <div class="col-md"></div>
                <div class="col-md"></div>

            </div>
            <div class="form-row mt-2">

                <div class="col-md">
                <!-- <input type="hidden" name="MAX_FILE_SIZE" value="3000000" /> -->
                <select id="projects" name="projects" required>
                    <?php
                        if (!empty($project_list)) {
                            while($row = mysqli_fetch_array($project_list))
                            {
                                echo '<option value="' . $row['company_name'] . '">' . $row['company_name'] . '</option>';
                            }
                            }  
                        ?>
                </select>
            </div>
            <div class="col-md"></div>
            <div class="col-md"></div>
            <div class="col-md"></div>
            </div>
            <br>
            <div class="form-row mt-2">

                <div class="col-md"><input name="userfiles[]" type="file" multiple/></div>  
                <div class="col-md"></div>
                <div class="col-md"></div>
                <div class="col-md"></div>

            </div>

            <div class="form-row mt-2">
                <div class="col-md"><input type="submit" name="send-files" value="Send file(s)" /></div>
                <div class="col-md"></div>
                <div class="col-md"></div>
                <div class="col-md"></div>

            </div>
        </form>
        <br>
        <br>

        <?php

        if(isset($_SESSION['import_results'])) {
            echo $_SESSION['import_results'];
            unset($_SESSION['import_results']);
        }
        ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
</body>

</html>