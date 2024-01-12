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

  <title>eAttorney CRM - Export - Portfolio</title>
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
          <label class="col-form-label-sm mb-0 pb-0">Total debt from:</label>
          <input type="number" name="debt-from" required value="0.00" step="0.01" min="0.00" max="10000000000.00">
        </div>
        <div class="col-md">
          <label class="col-form-label-sm mb-0 pb-0">Total debt to:</label>
          <input type="number" name="debt-to" required value="10000000000.00" step="0.01" min="0.00" max="10000000000.00">
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
      $debt_from = null;
      $debt_to = null;
      if(array_key_exists('export-button', $_GET)) {
        $project_filter_array = $_GET["project-id"];
        $debt_from = $_GET["debt-from"];
        $debt_to = $_GET["debt-to"];
        $payment_export_result = getPortfolioExportResults($project_filter_array, $debt_from, $debt_to);
      }

      echo "<table class=\"table table-sm table-responsive-sm display nowrap\"
      id=\"table\"
      data-toggle=\"table\"
      data-pagination=\"true\"
      </table>
      <thead>
          <tr>
            <th>Loan Id</th>
            <th>Loan number</th>
            <th>Loan status</th>
            <th>Full name</th>
            <th>SSN</th>
            <th>Loan date</th>
            <th>Loan amount</th>
            <th>Total debt</th>
            <th>Principal</th>
            <th>Interest</th>
            <th>Commission</th>
            <th>Penalties</th>
            <th>Payments (#/amount)</th>
            <th>Expenses (#/amount)</th>
            <th>Activities count</th>
            <th>Trials count</th>
          </tr>
      </thead>
      <tbody>";

      if (!empty($payment_export_result)) {
        while($row = mysqli_fetch_array($payment_export_result))
        {
        echo '<tr>';
        echo '<td><a href="loandetails.php?' . $row['loan_id'] .'">' . $row['loan_number'] . '</a></td>';
        echo '<td>' . $row['loan_id_ext'] . '</td>';
        echo '<td>' . $row['loan_status_name'] . '</td>';
        echo '<td>' . $row['full_name'] . '</td>';
        echo '<td>' . $row['ssn'] . '</td>';
        echo '<td>' . $row['loan_date'] . '</td>';
        echo '<td>' . $row['loan_amount'] . '</td>';
        echo '<td>' . $row['total_debt'] . '</td>';
        echo '<td>' . $row['principal_debt'] . '</td>';
        echo '<td>' . $row['interest_debt'] . '</td>';
        echo '<td>' . $row['commission_debt'] . '</td>';
        echo '<td>' . $row['penalty_debt'] . '</td>';
        echo '<td>' . $row['pmt_summary'] . '</td>';
        echo '<td>' . $row['loan_exp_summary'] . '</td>';
        echo '<td>' . $row['activity_cnt'] . '</td>';
        echo '<td>' . $row['trial_cnt'] . '</td>';
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