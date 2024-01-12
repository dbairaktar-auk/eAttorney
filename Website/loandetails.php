<?php
session_start();
include_once('include\functions.php');
updateSessionLifetime();
if(!isset($_SESSION["userloggedin"])){
	redirect("login.php");
	exit();
}
$loan_id = $_SERVER['QUERY_STRING'];
$con = getDBConn();
$loan_overview = mysqli_fetch_array(getLoanOverview($con, $loan_id));
$loan_details = mysqli_fetch_array(getLoanDetails($con, $loan_id));
$borrower_details = mysqli_fetch_array(getBorrowerDetails($con, $loan_id));
$contragent_info_details = mysqli_fetch_array(getLoanContragentInfoDetails($con, $loan_id));
$payment_details = getPaymentDetails($con, $loan_id);
$address_details = getAddressDetails($con, $loan_id);
$phone_details = getPhoneDetails($con, $loan_id);
$activity_details = getActivityDetails($con, $loan_id);
$trial_details = getTrialDetails($con, $loan_id);
$loan_expenses_details = getLoanExpensesDetails($con, $loan_id);
$other_loans_list = getOtherLoansList($con, $loan_id);
mysqli_close($con);
// echo date('c', $_SESSION['LAST_ACTIVITY']) . " | " . date('c', $_SESSION['CREATED']);
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/loandetails.css">

    <title>eAttorney CRM - Loan Details</title>
</head>

<body>
    <?php
    include_once('include\navbar.php');
    ?>

    <div class="container-fluid mt-2">
        <h2 class="heading-section" style="color:#454c53;">
            <?php echo "Loan " . $loan_overview['loan_number'] . " of " . date_format(date_create($loan_overview['loan_date']), 'd.m.Y')?>
        </h2>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab"
                    aria-controls="general" aria-selected="true">Basic info</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="loan-tab" data-toggle="tab" href="#loan" role="tab" aria-controls="loan"
                    aria-selected="false">Loan details</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="borrower-tab" data-toggle="tab" href="#borrower" role="tab" aria-controls="loan"
                    aria-selected="false">Borrower details</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="loan-contragent-info-tab" data-toggle="tab" href="#loan-contragent-info" role="tab"
                    aria-controls="loan-contragent-info" aria-selected="false">Contragent info</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab"
                    aria-controls="payments" aria-selected="false">Payments</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="addresses-tab" data-toggle="tab" href="#addresses" role="tab"
                    aria-controls="addresses" aria-selected="false">Addresses</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="phones-tab" data-toggle="tab" href="#phones" role="tab"
                    aria-controls="phones" aria-selected="false">Phones</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="activities-tab" data-toggle="tab" href="#activities" role="tab"
                    aria-controls="activities" aria-selected="false">Activities</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="trials-tab" data-toggle="tab" href="#trials" role="tab"
                    aria-controls="trials" aria-selected="false">Trials</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="loan-expenses-tab" data-toggle="tab" href="#loan-expenses" role="tab"
                    aria-controls="loan-expenses" aria-selected="false">Loan expenses</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="general" role="tabpanel" aria-labelledby="general-tab">

                <div class="container-fluid mt-2">
                    <form>
                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan code</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_id_ext']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Project</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['company_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_status_name']?>" readonly>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Full name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['full_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">SSN</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['ssn']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan issue date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_amount']?>" readonly>
                            </div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['total_debt']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Principal</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['principal_debt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Interest</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['interest_debt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Commission</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['commission_debt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Penalties</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['penalty_debt']?>" readonly>
                            </div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Payments (num./amt.)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['pmt_summary']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Expences (num./amt.)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['loan_exp_summary']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Activities (num.)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['activity_cnt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Trials (num.)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['trial_cnt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Predicted next payment date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['predicted_next_pmt_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Predicted next payment amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_overview['predicted_next_pmt_amount']?>" readonly>
                            </div>
                        </div>
                    </form>

                    <br></br>
                    <h5>Other customer's loans</h5>
                    <table class="table table-sm table-responsive-sm other-loans-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Loan number</th>
                            <th>Loan date</th>
                            <th>Project</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        if (!empty($other_loans_list)) {
                            while($row = mysqli_fetch_array($other_loans_list))
                            {
                            echo '<tr>';
                            echo '<td><a href="loandetails.php?' . $row['other_loan_id'] .'">' . $row['other_loan_number'] . '</a></td>';
                            echo '<td>' . $row['other_loan_date'] . '</td>';
                            echo '<td>' . $row['other_company_name'] . '</td>';
                            echo '</tr>';
                            }
                            }  
                    ?>
                    </tbody>
                    </table>
                </div>

            </div>
            <div class="tab-pane" id="loan" role="tabpanel" aria-labelledby="loan-tab">
                <div class="container-fluid mt-2">
                    <form>
                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['loan_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan code</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['loan_id_ext']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['loan_status_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Letter status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['letter_status_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Status date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['letter_date']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan issue date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['loan_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['loan_amount']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Priority</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['priority']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Box number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['box_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Number of loans</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['contracts_count']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Project</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['company_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Contract exists</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['has_contract']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Last payment date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['last_payment_date']?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Letter payment date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['letter_payoff_date']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Debt wo penalties</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt_wo_pnlt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Debt wo penalties (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt_wo_pnlt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Principal debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['principal_debt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Principal debt (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['principal_debt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Interest debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['interest_debt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Interest debt (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['interest_debt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Commission debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['commission_debt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Commission debt (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['commission_debt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Penalties debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['penalty_debt']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Penalties debt (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['penalty_debt_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Inscription cost</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['inscription_cost']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Inscription cost (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['inscription_cost_w']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt with inscription</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt_w_inscr']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt with inscription (say)</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['total_debt_w_inscr_w']?>" readonly>
                            </div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Initial creditor</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($loan_details['initial_creditor'], ENT_QUOTES)?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Initial concession</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['initial_concession']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Next concession Aval</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['next_consession_aval']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">FC to AMS concession</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['fc_to_ams_concession']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Previous creditor</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($loan_details['previous_creditor'], ENT_QUOTES)?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Current creditor</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($loan_details['current_creditor'], ENT_QUOTES)?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Current concession number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['current_concession_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Current concession date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $loan_details['current_concession_date']?>" readonly>
                            </div>
                        </div>

                    </form>

                    <br></br>

                </div>

            </div>
            <div class="tab-pane" id="borrower" role="tabpanel" aria-labelledby="borrower-tab">
                <div class="container-fluid mt-2">
                    <form>
                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Last name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['last_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">First name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['first_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Middle name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['middle_name']?>" readonly>
                            </div>
							<div class="col-md"></div>
							<div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">SSN</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['ssn']?>" readonly>
                            </div>
							<div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Birth date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['birth_date']?>" readonly>
                            </div>
							<div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Birth place</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['birth_place']?>" readonly>
                            </div>
							<div class="col-md"></div>
							<div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Registration address</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['registration_address']?>" readonly>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Work place</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['work_place']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Passport number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['passport_number']?>" readonly>
                            </div>
							<div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Passport issuer</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $borrower_details['passport_issuer']?>" readonly>
                            </div>
							<div class="col-md"></div>
                        </div>
                    </form>

                    <br></br>

                </div>

            </div>
            <div class="tab-pane" id="loan-contragent-info" role="tabpanel" aria-labelledby="loan-contragent-info-tab">

                <div class="container-fluid mt-2">
                    <form>
                        <div class="form-row mt-2">
                            <div class="col-md-3">
                                <label class="col-form-label-sm mb-0 pb-0">Full name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['borrower_full_name']?>" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="col-form-label-sm mb-0 pb-0">SSN</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['borrower_ssn']?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Birth date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['birth_date']?>" readonly>
                            </div>
							<div class="col-md"></div>
							<div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <label class="col-form-label-sm mb-0 pb-0">Registration address</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['registration_address']?>" readonly>
                            </div>
							<div class="col-md"></div>
							<div class="col-md"></div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Total debt</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['total_debt']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Prepayment amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['prepayment_amount']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Prepayment return amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['prepayment_return_amount']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Total payments amount</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['total_payments_amount']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Inscription number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['inscription_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Inscription date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['inscription_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Notary name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['notary_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Registry number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['registry_number']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan transfer date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['loan_transfer_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Prepayment order date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['prepayment_order_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Prepayment confirmation date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['prepayment_confirmation_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Region</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['region']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Private executor name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['private_executor_name']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan status name</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['loan_status_name']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Documents transfer date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['documents_transfer_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Days for execution opening</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['days_for_execution_opening']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Days for sal. deduct. transfer</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['days_for_salary_deduction_transfer']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Execution open date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['execution_opening_date']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Execution number</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['execution_number']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Access Id</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['access_id']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Transfer to executor status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['transferred_to_executor_status']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Execution open status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['execution_opening_status']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Sal. deduct. transfer status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['sal_deduction_transferred_status']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Income found status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['income_found_status']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Income not found status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['income_not_found_status']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Info not avail. status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['information_not_available_status']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                            <div class="col-md"></div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Sal. deduction document date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['sal_deduction_document_date']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">General income info</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['general_income_info']?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="col-form-label-sm mb-0 pb-0">Sal. deduction fail reason</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['sal_deduction_fail_reason']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-1">
                                <label class="col-form-label-sm mb-0 pb-0">Check needed</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['check_needed']?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="col-form-label-sm mb-0 pb-0">Income check 2nd request date</label>
                                <input type="date" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['income_check_second_request_date']?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="col-form-label-sm mb-0 pb-0">Income check 2nd request result</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['income_check_second_request_result']?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="col-form-label-sm mb-0 pb-0">Additional checks status</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['additional_checks_status']?>" readonly>
                            </div>
                            <div class="col-md-2"></div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-1">
                                <label class="col-form-label-sm mb-0 pb-0">Additional checks num.</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['additional_checks_number']?>" readonly>
                            </div>
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Execution has payment</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['execution_has_payments']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Prepayment return control</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['prepayment_return_control']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Discount closure</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['discount_closure']?>" readonly>
                            </div>
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Has payments</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['has_payments']?>" readonly>
                            </div>
                            <div class="col-md"></div>
                        </div>

                        <br></br>

                        <div class="form-row mt-2">
                            <div class="col-md">
                                <label class="col-form-label-sm mb-0 pb-0">Loan comment</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="<?php echo $contragent_info_details['loan_comment']?>" readonly>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                    </form>

                    <br></br>

                </div>

            </div>

            <!-- Payments -->
            <div class="tab-pane" id="payments" role="tabpanel" aria-labelledby="payments-tab">
            <div class="container-fluid mt-2">
            <?php
                echo "<table class=\"table table-sm table-responsive-sm\"
                id=\"payment-table\">
                <thead class=\"thead-light\">
                  <tr>
                    <th>Payment date</th>
                    <th>Amount</th>
                    <th>Payment description</th>
                    <th>Contragent name</th>
                    <th>Payment source</th>
                    <th>Execution n.</th>
                    <th>Prescr. n.</th>
                    <th>Type</th>
                    <th>Executor</th>
                  </tr>
                </thead>
                <tbody>";

                if (!empty($payment_details)) {
                while($row = mysqli_fetch_array($payment_details))
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
            </div>
            
            <!-- Addresses -->
            <div class="tab-pane" id="addresses" role="tabpanel" aria-labelledby="addresses-tab">
                <div class="container-fluid mt-2">
                    <?php
                    echo "<table class=\"table table-sm table-responsive-sm\"
                    id=\"address-table\">
                    <thead class=\"thead-light\">
                      <tr>
                        <th>Address type</th>
                        <th>ZIP</th>
                        <th>Region</th>
                        <th>District</th>
                        <th>City</th>
                        <th>Street</th>
                        <th>House/app</th>
                        <th>Priority</th>
                        <th>Primary</th>
                        <th>Active</th>
                      </tr>
                    </thead>
                    <tbody>";
    
                    if (!empty($address_details)) {
                    while($row = mysqli_fetch_array($address_details))
                    {
                    echo '<tr>';
                    echo '<td>' . $row['address_type_name'] . '</td>';
                    echo '<td>' . $row['zip_code'] . '</td>';
                    echo '<td>' . $row['region'] . '</td>';
                    echo '<td>' . $row['district'] . '</td>';
                    echo '<td>' . $row['city'] . '</td>';
                    echo '<td>' . $row['street'] . '</td>';
                    echo '<td>' . $row['house_appartment'] . '</td>';
                    echo '<td>' . $row['priority'] . '</td>';
                    echo '<td>' . $row['is_primary_txt'] . '</td>';
                    echo '<td>' . $row['is_active_txt'] . '</td>';
                    echo '</tr>';
                    }
                    echo '</tbody></table>';
                    }
                    ?>
                </div>
                <br></br>
            </div>
            
            <!-- Phones -->
            <div class="tab-pane" id="phones" role="tabpanel" aria-labelledby="phones-tab">
                <div class="container-fluid mt-2">
                    <?php
                    echo "<table class=\"table table-sm table-responsive-sm\"
                    id=\"phone-table\">
                    <thead class=\"thead-light\">
                      <tr>
                        <th>Phone no.</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Last RPC date</th>
                        <th>Last PTP date</th>
                        <th>Comment</th>
                        <th>Priority</th>
                        <th>Primary</th>
                        <th>Active</th>
                      </tr>
                    </thead>
                    <tbody>";
    
                    if (!empty($phone_details)) {
                    while($row = mysqli_fetch_array($phone_details))
                    {
                    echo '<tr>';
                    echo '<td>' . $row['phone_number'] . '</td>';
                    echo '<td>' . $row['phone_type_name'] . '</td>';
                    echo '<td>' . $row['phone_status_name'] . '</td>';
                    echo '<td>' . $row['last_rpc_date'] . '</td>';
                    echo '<td>' . $row['last_ptp_date'] . '</td>';
                    echo '<td>' . $row['comment'] . '</td>';
                    echo '<td>' . $row['priority'] . '</td>';
                    echo '<td>' . $row['is_primary_txt'] . '</td>';
                    echo '<td>' . $row['is_active_txt'] . '</td>';
                    echo '</tr>';
                    }
                    echo '</tbody></table>';
                    }
                    ?>
                </div>
                <br></br>
            </div>

            <!-- Activities -->
            <div class="tab-pane" id="activities" role="tabpanel" aria-labelledby="activities-tab">
                <div class="container-fluid mt-2">
                    <?php
                    echo "<table class=\"table table-sm table-responsive-sm\"
                    id=\"activity-table\">
                    <thead class=\"thead-light\">
                      <tr>
                        <th>Activity date</th>
                        <th>Description</th>
                        <th>Answer date</th>
                        <th>Bank</th>
                        <th>Notes</th>
                      </tr>
                    </thead>
                    <tbody>";

                    if (!empty($activity_details)) {
                        while($row = mysqli_fetch_array($activity_details))
                        {
                        echo '<tr>';
                        echo '<td>' . $row['activity_date'] . '</td>';
                        echo '<td>' . $row['activity_details'] . '</td>';
                        echo '<td>' . $row['answer_date'] . '</td>';
                        echo '<td>' . $row['bank'] . '</td>';
                        echo '<td>' . $row['notes'] . '</td>';
                        echo '</tr>';
                        }
                        echo '</tbody></table>';
                        }
                    ?>
                </div>
                <br></br>
            </div>

            <!-- Trials -->
            <div class="tab-pane" id="trials" role="tabpanel" aria-labelledby="trials-tab">
                <div class="container-fluid mt-2">
                    <?php
                    echo "<table class=\"table table-sm table-responsive-sm\"
                    id=\"trial-table\">
                    <thead class=\"thead-light\">
                      <tr>
                        <th>Loan number</th>
                        <th>Total debt</th>
                        <th>Initial creditor</th>
                        <th>Contact phone</th>
                        <th>Trial #</th>
                        <th>Execution #</th>
                        <th>Court</th>
                        <th>Claim date</th>
                        <th>Trial stage</th>
                        <th>Decision date</th>
                        <th>Comments</th>
                      </tr>
                    </thead>
                    <tbody>";

                    if (!empty($trial_details)) {
                        while($row = mysqli_fetch_array($trial_details))
                        {
                        echo '<tr>';
                        echo '<td>' . $row['loan_number_ext'] . '</td>';
                        echo '<td>' . $row['total_debt'] . '</td>';
                        echo '<td>' . $row['initial_creditor'] . '</td>';
                        echo '<td>' . $row['contact_phone_number'] . '</td>';
                        echo '<td>' . $row['trial_number'] . '</td>';
                        echo '<td>' . $row['execution_number'] . '</td>';
                        echo '<td>' . $row['court_name'] . '</td>';
                        echo '<td>' . $row['claim_date'] . '</td>';
                        echo '<td>' . $row['trial_stage'] . '</td>';
                        echo '<td>' . $row['court_decision_promulgation_date'] . '</td>';
                        echo '<td>' . $row['comments'] . '</td>';
                        echo '</tr>';
                        }
                        echo '</tbody></table>';
                        }
                    ?>
                </div>
                <br></br>
            </div>
            <div class="tab-pane" id="loan-expenses" role="tabpanel" aria-labelledby="loan-expenses-tab">
                <div class="container-fluid mt-2">
                    <?php
                    echo "<table class=\"table table-sm table-responsive-sm\"
                    id=\"loan-expenses-table\">
                    <thead class=\"thead-light\">
                      <tr>
                        <th>Payment date</th>
                        <th>Amount</th>
                        <th>Credit IBAN</th>
                        <th>Debit IBAN</th>
                        <th>Bank MFO</th>
                        <th>Contragent name</th>
                        <th>Contragent code</th>
                        <th>Description</th>
                        <th>Document</th>
                        <th>Type</th>
                      </tr>
                    </thead>
                    <tbody>";

                    if (!empty($loan_expenses_details)) {
                        while($row = mysqli_fetch_array($loan_expenses_details))
                        {
                        echo '<tr>';
                        echo '<td>' . $row['payment_date'] . '</td>';
                        echo '<td>' . $row['payment_amount'] . '</td>';
                        echo '<td>' . $row['credit_iban'] . '</td>';
                        echo '<td>' . $row['debit_iban'] . '</td>';
                        echo '<td>' . $row['bank_code'] . '</td>';
                        echo '<td>' . $row['contragent_name'] . '</td>';
                        echo '<td>' . $row['contragent_code'] . '</td>';
                        echo '<td>' . $row['payment_details'] . '</td>';
                        echo '<td>' . $row['document_no'] . '</td>';
                        echo '<td>' . $row['payment_type'] . '</td>';
                        echo '</tr>';
                        }
                        echo '</tbody></table>';
                        }
                    ?>
                </div>
                <br></br>
            </div>
            
        </div>
    </div>

    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
</body>

</html>