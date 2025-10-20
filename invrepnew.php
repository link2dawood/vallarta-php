<?php
require_once('settings/db.php');
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:LogReg/login.php');
};


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
        <title>serch for 420</title>
        <link rel="stylesheet" href="assets2/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"><link rel="stylesheet" href="/assets/css/styles.min.css">
    </head>
    <body>
        <!-- Start: Table With Search -->
        <div class="col-md-12 search-table-col">
            <label class="form-label" style="font-size: 25px;">420 vallarta inventory table</label>
            <div class="form-group pull-right col-lg-4">
                <input type="text" class="search form-control" placeholder="Search by typing here..">
                <button class="btn btn-primary" type="button">Searsh</button>
            </div>
            <span class="counter pull-right">

            </span>
            <div class="table-responsive table table-hover table-bordered results">
                <table class="table table-hover table-bordered">
                    <thead class="bill-header cs" style="color: var(--bs-gray-100);background: black;">
                        <tr><th id="trs-hd-1" class="col-lg-1">Product</th>
                            <th id="trs-hd-2" class="col-lg-2">Price</th>
                            <th id="trs-hd-3" class="col-lg-3">Inventory Aded</th>
                            <th id="trs-hd-4" class="col-lg-2">Remaining in stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="warning no-result">
                            <td colspan="12">
                                <i class="fa fa-warning">

                                </i>&nbsp; No Result !!!</td>
                            </tr>
                            <tr>
                                <td style="width: 242.5469px;">01</td>
                                <td>India</td><td>Souvik Kundu</td>
                                <td>Bootstrap Stuido</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- End: Table With Search -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js">

            </script>
            <script src="assets2/js/script.min.js">

            </script>
            </body>
            </html>