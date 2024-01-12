<?php
session_start();
include_once('include\functions.php');
updateSessionLifetime();
if(!isset($_SESSION["userloggedin"])){
	redirect("login.php");
	exit();
}
$project_list = getProjectList();
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="/css/bootstrap-table.min-index-page.css"> -->
  <link rel="stylesheet" href="https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.css">

  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css">

  <link rel="stylesheet" href="/css/export-pages.css">

  <title>eAttorney CRM - Export - Payments</title>
</head>

<body>
  <?php
  include_once('include\navbar.php');
  ?>
  <div class="container-fluid mt-2">
    <br></br>
    <form method="GET">
      <div class="form-row mt-2">
      <div class="col-md"></div>
      <div class="col-md"></div>
        <div class="col-md">
          <label class="col-form-label-sm mb-0 pb-0">Project</label>
          <select name="project-id[]" multiple required>
            <?php
            if (!empty($project_list)) {
                while($row = mysqli_fetch_array($project_list))
                {
                echo '<option value="' . $row['company_id'] . '">' . $row['company_name'] . '</option>';
                }
                }  
            ?>
            </select>
        </div>
        <div class="col-md">
          <label class="col-form-label-sm mb-0 pb-0">Date from:</label>
          <input type="date" name="date-from" required value="2021-01-01">
        </div>
        <div class="col-md">
          <label class="col-form-label-sm mb-0 pb-0">Date to:</label>
          <input type="date" name="date-to" required value="2021-01-15">
        </div>
        <div class="col-md"></div>
        <div class="col-md"></div>
      </div>

      <div class="form-row mt-4">
        <div class="col-md"></div>
        <div class="col-md"></div>
        <div class="col-md"></div>
        <div class="col-md">
          <button type="submit" name="export-button" class="btn btn-primary">Download</button>
        </div>
        <div class="col-md"></div>
        <div class="col-md"></div>
        <div class="col-md"></div>
      </div>
      
    </form>

    <br></br>

  </div>

  <!-- Payments -->
    <div class="container-fluid mt-2">
    <?php

      $payment_export_result = array();
      $project_filter_array = null;
      $date_from = null;
      $date_to = null;
      if(array_key_exists('export-button', $_GET)) {
        $project_filter_array = $_GET["project-id"];
        $date_from = $_GET["date-from"];
        $date_to = $_GET["date-to"];
        $payment_export_result = getPaymentExportResults($project_filter_array, $date_from, $date_to);
      }

      echo "<table class=\"table table-sm table-responsive-sm display nowrap\"
      id=\"table\"
      data-toggle=\"table\"
      data-pagination=\"true\"
      </table>
      <thead>
          <tr>
            <th>Payment date</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Contragent name</th>
            <th>Source</th>
            <th>Execution num</th>
            <th>Inscription</th>
            <th>Payment type</th>
            <th>Executor found</th>
          </tr>
      </thead>
      <tbody>";

      if (!empty($payment_export_result)) {
        while($row = mysqli_fetch_array($payment_export_result))
        {
        echo '<tr>';
        echo '<td>' . $row['payment_date'] . '</td>';
        echo '<td>' . $row['payment_amount'] . '</td>';
        echo '<td>' . $row['payment_details'] . '</td>';
        echo '<td>' . $row['contragent_name'] . '</td>';
        echo '<td>' . $row['payment_source'] . '</td>';
        echo '<td>' . $row['execution_number'] . '</td>';
        echo '<td>' . $row['inscription_number'] . '</td>';
        echo '<td>' . $row['payment_type_name'] . '</td>';
        echo '<td>' . $row['private_executor_found'] . '</td>';
        echo '</tr>';
        }
        echo '</tbody></table>';
        }

    ?>

    </div>
    <br></br>


  <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <!-- <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script> -->
  <script src="https://unpkg.com/multiple-select@1.5.2/dist/multiple-select.min.js"></script>

  <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>

  <script>
  $(function() {
    $('select').multipleSelect()
  })
  </script>

<script>
$(document).ready(function() {
    $('#table').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            // 'copy', 'csv', 'excel', 'pdf', 'print'
            'excel', 'copy'
        ],
        ordering: false
    } );
} );
  </script>


</body>

</html>