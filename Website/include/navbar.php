<?php
if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
    header( 'HTTP/1.0 404 Not Found', TRUE, 404 );
    die();
}
?>
<style>
.dropdown-item:active {
    background-color: mediumseagreen;
}

.navb-dr-item:hover {
    background-color: mediumseagreen;
    color: white;
}

.nav-link-cust {
    color: rgba(255,255,255,.85) !important;
}

.nav-link-cust:hover {
    font-weight: 600;
}

.navbar-brand {
    font-weight: 600;
}

</style>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:mediumseagreen; color:#fff;">
    <a class="navbar-brand" href="index.php">eAttorney CRM</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-link nav-link-cust" href="index.php">Search</a>
            <a class="nav-link nav-link-cust" href="reports.php">Reports</a>
            <a class="nav-link nav-link-cust" href="import.php">Import</a>
            <li class="nav-item dropdown">
                <a class="nav-link nav-link-cust dropdown-toggle" href="" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Export
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item navb-dr-item" href="export-portfolio.php">Portfolio</a>
                  <a class="dropdown-item navb-dr-item" href="export-payments.php">Payments</a>
              </li>
            <a class="nav-link nav-link-cust" href="logout.php">Logout (<?php echo $_SESSION["username"]?>)</a>
        </div>
    </div>
</nav>