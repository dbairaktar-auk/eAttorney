<?php
session_start();
include_once('include\functions.php');
updateSessionLifetime();
if(!isset($_SESSION["userloggedin"])){
	redirect("login.php");
	exit();
}
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
  <link rel="stylesheet" href="/css/bootstrap-table.min-index-page.css">
  <link rel="stylesheet" href="/css/index.css">

  <title>eAttorney CRM - Search</title>
</head>

<body>
<?php
  include_once('include\navbar.php');
  ?>

  <div class="container mt-2">
  <form method="GET" style="box-shadow: 0px 10px 34px -15px rgb(0 0 0 / 24%); padding:1rem;">
    <div class="form-row mt-2">
      <input type="text" class="form-control form-control-sm" placeholder="Customer SSN" aria-describedby="basic-addon2" name="ssn-search">
    </div>
    <div class="form-row mt-2">
      <input type="text" class="form-control form-control-sm" placeholder="Full name" aria-describedby="basic-addon2" name="fullname-search">
    </div>
    <div class="form-row mt-2">
      <input type="text" class="form-control form-control-sm" placeholder="Loan number" aria-describedby="basic-addon2" name="loan-search">
    </div>
    <div class="form-row mt-2">
    <button type="submit" class="btn btn-primary btn-lg mx-auto" style="background-color: mediumseagreen; border-color: mediumseagreen;" name="search-button">Search</button>
  </div>
  </form>
</div>
  <hr>

  <div class="container">

    <?php
    $result = array();
    $ssn_search = null;
    $loan_search = null;
    $fullname_search = null;
    if(array_key_exists('search-button', $_GET)) {
      $ssn_search = $_GET["ssn-search"];
      $loan_search = $_GET["loan-search"];
      $fullname_search = $_GET["fullname-search"];
      $result = getSearchResults($ssn_search, $loan_search, $fullname_search);
    }
    

    echo "<table
    id=\"table\"
    data-toggle=\"table\"
    data-pagination=\"true\"
    </table>
    <thead>
      <tr>
        <th>Loan number</th>
        <th>SSN</th>
        <th>Full name</th>
        <th>Project</th>
      </tr>
    </thead>
    <tbody>";

    if (!empty($result)) {
    while($row = mysqli_fetch_array($result))
    {
    echo '<tr>';
    echo '<td><a href="loandetails.php?' . $row['loan_id'] .'">' . $row['loan_number'] . '</a></td>';
    echo '<td>' . $row['ssn'] . '</td>';
    echo '<td>' . $row['full_name'] . '</td>';
    echo '<td>' . $row['company_name'] . '</td>';
    echo '</tr>';
    }
    echo '</tbody></table>';
    }
    ?>

  </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
</body>

</html>