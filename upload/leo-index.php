<?php
/**
    Hydrid CAD/MDT - Computer Aided Dispatch / Mobile Data Terminal for use in GTA V Role-playing Communities.
    Copyright (C) 2018 - Hydrid Development Team

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
**/
require 'includes/connect.php';
include 'includes/config.php';
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ' . $url_login . '');
    exit();
}
include 'includes/isLoggedIn.php';

$stmts    = $pdo->prepare("SELECT * FROM settings");
$stmts->execute();
$leo_gsettings = $stmts->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['createTicketbtn'])) {
    //Pull the variables from the form
    $ticketing_officer_form = !empty($_POST['ticketing_officer']) ? trim($_POST['ticketing_officer']) : null;
    $suspect_form = !empty($_POST['suspect']) ? trim($_POST['suspect']) : null;
    $reasons_form = !empty($_POST['reasons']) ? trim($_POST['reasons']) : null;
    $location_form = !empty($_POST['location']) ? trim($_POST['location']) : null;
    $postal_form = !empty($_POST['postal']) ? trim($_POST['postal']) : null;
    $amount_form = !empty($_POST['amount']) ? trim($_POST['amount']) : null;
    //Sanitize the variables, prevents xss, etc.
    $ticketing_officer        = strip_tags($ticketing_officer_form);
    $suspect        = strip_tags($suspect_form);
    $reasons        = strip_tags($reasons_form);
    $location        = strip_tags($location_form);
    $postal        = strip_tags($postal_form);
    $amount        = strip_tags($amount_form);


        //if everything passes, than continue
        $sql          = "INSERT INTO tickets (ticketing_officer, suspect, reasons, location, postal, amount) VALUES (:ticketing_officer, :suspect, :reasons, :location, :postal, :amount)";
        $stmt         = $pdo->prepare($sql);
        $stmt->bindValue(':ticketing_officer', $ticketing_officer);
        $stmt->bindValue(':suspect', $suspect);
        $stmt->bindValue(':reasons', $reasons);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':postal', $postal);
        $stmt->bindValue(':amount', $amount);
        logme('(LEO) Created New Ticket', $user_username . ' / ' . $_SESSION['identifier']);
        $result = $stmt->execute();
        if ($result) {
            $message='<div class="alert alert-success" id="dismiss">Ticket Created</div>';
        }
}

if (isset($_POST['createArrestReportbtn'])) {
    //Pull the variables from the form
    $arresting_officer_form = !empty($_POST['arresting_officer']) ? trim($_POST['arresting_officer']) : null;
    $suspect_form = !empty($_POST['suspect']) ? trim($_POST['suspect']) : null;
    $summary_form = !empty($_POST['summary']) ? trim($_POST['summary']) : null;
    //Sanitize the variables, prevents xss, etc.
    $arresting_officer        = strip_tags($arresting_officer_form);
    $suspect        = strip_tags($suspect_form);
    $summary        = strip_tags($summary_form);


        //if everything passes, than continue
        $sql          = "INSERT INTO arrest_reports (arresting_officer, suspect, summary) VALUES (:arresting_officer, :suspect, :summary)";
        $stmt         = $pdo->prepare($sql);
        $stmt->bindValue(':arresting_officer', $arresting_officer);
        $stmt->bindValue(':suspect', $suspect);
        $stmt->bindValue(':summary', $summary);
        logme('(LEO) Created New Arrest Report', $user_username . ' / ' . $_SESSION['identifier']);
        $result = $stmt->execute();
        if ($result) {
            $message='<div class="alert alert-success" id="dismiss">Arrest Report Created</div>';
        }
}

if (isset($_POST['addBoloBtn'])) {
    //Pull the variables from the form
    $bolo_created_by_form = !empty($_POST['bolo_created_by']) ? trim($_POST['bolo_created_by']) : null;
    $vehicle_plate_form = !empty($_POST['vehicle_plate']) ? trim($_POST['vehicle_plate']) : null;
    $vehicle_model_form = !empty($_POST['vehicle_model']) ? trim($_POST['vehicle_model']) : null;
    $vehicle_color_form = !empty($_POST['vehicle_color']) ? trim($_POST['vehicle_color']) : null;
    $bolo_reason_form = !empty($_POST['bolo_reason']) ? trim($_POST['bolo_reason']) : null;
    //Sanitize the variables, prevents xss, etc.
    $bolo_created_by        = strip_tags($bolo_created_by_form);
    $vehicle_plate        = strip_tags($vehicle_plate_form);
    $vehicle_model        = strip_tags($vehicle_model_form);
    $vehicle_color        = strip_tags($vehicle_color_form);
    $bolo_reason        = strip_tags($bolo_reason_form);

    //than
    $sql          = "INSERT INTO bolos (vehicle_plate, vehicle_color, vehicle_model, bolo_reason, bolo_created_by, bolo_created_on) VALUES (:vehicle_plate, :vehicle_color, :vehicle_model, :bolo_reason, :bolo_created_by, :bolo_created_on)";
    $stmt         = $pdo->prepare($sql);
    $stmt->bindValue(':vehicle_plate', $vehicle_plate);
    $stmt->bindValue(':vehicle_color', $vehicle_color);
    $stmt->bindValue(':vehicle_model', $vehicle_model);
    $stmt->bindValue(':bolo_reason', $bolo_reason);
    $stmt->bindValue(':bolo_created_by', $bolo_created_by);
    $stmt->bindValue(':bolo_created_on', $date . ' ' . $time);
    logme('(LEO) Added BOLO', $user_username . ' / ' . $_SESSION['identifier']);
    $result = $stmt->execute();
    if ($result) {
        //redirect
        $message='<div class="alert alert-success" id="dismiss">Bolo Added</div>';
    }
}

if (isset($_POST['1041btn'])) {
  $sqlcd  = "SELECT COUNT(identifier) AS num FROM on_duty WHERE identifier = :identifier";
  $stmtcd = $pdo->prepare($sqlcd);
  $stmtcd->bindValue(':identifier', $_SESSION['identifier']);
  $stmtcd->execute();
  $rowcd = $stmtcd->fetch(PDO::FETCH_ASSOC);
  if ($rowcd['num'] > 0) {
    $i = $_SESSION['identifier'];
    $sql     = "DELETE FROM `on_duty` WHERE identifier = :i";
    $stmt    = $pdo->prepare($sql);
    $stmt->bindValue(':i', $i);
    $delete_old_ids = $stmt->execute();
  }

  $sql          = "INSERT INTO on_duty (identifier, type) VALUES (:identifier, :type)";
  $duty_type = "LEO";
  $stmt         = $pdo->prepare($sql);
  $stmt->bindValue(':identifier', $_SESSION['identifier']);
  $stmt->bindValue(':type', $duty_type);
  $result = $stmt->execute();
  $_SESSION['on_duty'] = "Yes";
  logme('(LEO) Went On Duty', $user_username . ' / ' . $_SESSION['identifier']);
  header('Location: ' . $url_leo_index . '');
  exit();
}
if (isset($_POST['1042btn'])) {
  $i = $_SESSION['identifier'];
  $sql     = "DELETE FROM `on_duty` WHERE identifier = :i";
  $stmt    = $pdo->prepare($sql);
  $stmt->bindValue(':i', $i);
  $endShift = $stmt->execute();
  $_SESSION['on_duty'] = "No";

  header('Location: ' . $url_leo_index . '');
  exit();
}

?>
<!DOCTYPE html>
<html>
<?php
$page_name = "LEO Home";
include('includes/header.php')
?>
<head>
  <script>
  $(document).ready(function() {
   $("#openNameSearch").on("click",function(){
     loadNames();
   });
   $("#openWeaponSearch").on("click",function(){
     loadWpns();
   });
   $("#openVehicleSearch").on("click",function(){
     loadVehs();
   });
  });
  </script>
</head>
<body>
   <div class="container-leo">
      <div class="main-leo">
        <div class="leo-header"><div class="float-right" id="getTime"></div>
          <div class="float-left">
            <?php if (subdivisionModule_isInstalled): ?>
              <div style="margin-top:50px;">
                <h6>Sub Division</h6>
                <?php
                echo "
                <select style='width:200px;' name='changeSubDivision' class='select' onChange='changeSubDivision(this)'>
                <option selected='true' disabled='disabled'>" . $_SESSION['sub_division'] . "</option>";
          			$getSDS = "SELECT * FROM sub_divisions";
          			$result = $pdo->prepare($getSDS);
          			$result->execute();
          			while ($row = $result->fetch(PDO::FETCH_ASSOC))
          				{
          				echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
          				}
          			echo "</select>";
                 ?>
              </div>
            <?php endif; ?>
          </div>
         <div class="center"><a href="functions/leo/api.php?a=endShift"><img src="assets/imgs/police.png" class="main-logo" draggable="false"/></a></div>
         <div class="main-header-leo">
            <div class="float-left">Supervisor: <?php if ($_SESSION['leo_supervisor'] === "Yes") {
              echo 'Yes';
            } else {
              echo 'No';
            } ?></div>
            <div class="float-right"><div id="checkAOP"></div></div>
            <div class="center">Welcome, <?php echo $_SESSION['identifier'] ?></div>
         </div>
       </div>
         <div id="checkStatus"></div>
           <div class="row">
             <div class="col-sm-2">
               <a data-toggle="modal" href="#searchNameDB" class="btn btn-success btn-block" id="openNameSearch">Name Lookup</a><br-leo>
               <a data-toggle="modal" href="#searchDMV" class="btn btn-success btn-block" id="openVehicleSearch">Vehicle Lookup</a><br-leo>
               <a data-toggle="modal" href="#searchWeaponDB" class="btn btn-success btn-block" id="openWeaponSearch">Weapon Lookup</a><br-leo>
               <a data-toggle="modal" href="#newTicket" class="btn btn-warning btn-block">New Ticket</a><br-leo>
               <a data-toggle="modal" href="#arrestReportModal" class="btn btn-warning btn-block">New Arrest Report</a><br-leo>
               <a data-toggle="modal" href="#notepad" class="btn btn-secondary btn-block">Notepad</a><br-leo>
               <a data-toggle="modal" href="#addBoloModal" class="btn btn-danger btn-block">Add Bolo</a><br-leo>
               <a data-toggle="modal" href="#activeUnitsmodal" class="btn btn-primary btn-block">Active Units</a><br-leo>
               <?php if (mapModule_isInstalled): ?>
                 <a data-toggle="modal" href="#liveMap" class="btn btn-info btn-block">Map</a><br-leo>
               <?php endif; ?>
             </div>
             <div class="col-sm-8">
               <?php if (!custom10codesModule_isInstalled): ?>
                 <a id="10-6" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-6</a>
                 <a id="10-7" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-7</a>
                 <a id="10-8" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-8</a>
                 <a id="10-15" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-15</a>
                 <a id="10-23" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-23</a>
                 <a id="10-97" class="btn btn-info btn-md <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">10-97</a><br>
               <?php else: ?>
                 <?php
                 $getBolos = "SELECT * FROM custom10codes";
             	 	$result = $pdo->prepare($getBolos);
             		$result->execute();
             		while ($row = $result->fetch(PDO::FETCH_ASSOC))
             			{
                    echo '<a id="'. $row['btn_value'] .'" class="btn btn-info btn-md" style="width:140px; margin-bottom: 4px;" onclick="setStatus(this)">'. $row['btn_value'] .'</a>';
                  }
                 ?>
               <?php endif; ?><br>
                 <form method="post" action="leo-index.php">
               <button class="btn btn-info btn-md" name="1041btn" style="width:140px; margin-bottom: 4px;" type="submit" type="button" <?php if ($_SESSION['on_duty'] === "Yes") {echo 'disabled';} ?>>10-41</button>
               <button class="btn btn-info btn-md" name="1042btn" style="width:140px; margin-bottom: 4px;" type="submit" type="button" <?php if ($_SESSION['on_duty'] === "No") {echo 'disabled';} ?>>10-42</button>
               </form>
               <?php print($message); ?>
               <div class="col-sm-12">
                <div id="get911calls"></div>
               </div>
                <div class="col-sm-12">
                 <div id="getBolos"></div>
                </div>
             </div>
             <?php if ($_SESSION['leo_supervisor'] === "Yes"): ?>
               <div class="col-sm-2">
                 <a href="<?php echo $url_leo_supervisor_view_all_identities ?>" class="btn btn-success btn-block">All Identities</a><br-leo>
                 <a href="<?php echo $url_leo_supervisor_view_pending_identities ?>" class="btn btn-warning btn-block">Pending Identities</a><br-leo>
                 <a data-toggle="modal" href="#aop" class="btn btn-danger btn-block">Change AOP</a><br-leo>
               </div>
             <?php endif; ?>
           </div>
         <?php echo $ftter; ?>
      </div>
   </div>

   <!-- modals -->
   <!-- aop modal -->
   <div class="modal fade" id="aop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">AOP Management</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <select class="form-control" id="aopSet" onchange="aopSet(this.value)">
                <option selected="true" disabled="disabled"><?php echo $leo_gsettings['aop']; ?></option>
                <option value="State Wide">State Wide</option>
                <option value="Blaine County">Blaine County</option>
                <option value="Sandy Shores">Sandy Shores</option>
                <option value="Grapeseed">Grapeseed</option>
                <option value="Paleto Bay">Paleto Bay</option>
                <option value="Sandy Shores/Grapeseed">Sandy Shores/Grapeseed</option>
                <option value="Sandy Shores/Grapeseed">Grapeseed/Paleto Bay</option>
                <option value="Los Santos">Los Santos</option>
                <option value="Lower Los Santos">Lower Los Santos</option>
                <option value="Mid Los Santos">Mid Los Santos</option>
                <option value="Upper Los Santos">Upper Los Santos</option>
              </select>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
   <!-- end modal -->
   <!-- active units modal -->
   <div class="modal fade" id="activeUnitsmodal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Active Units</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <div id="getActiveUnits"></div>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->

   <!-- search weapon modal -->
   <div class="modal fade" id="searchWeaponDB" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Weapon Database</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form>
                <select class="js-example-basic-single" name="weaponSearch" id="weaponSearch" onchange="showWpn(this.value)"></select>
              </form>

              <div id="showWpn"></div>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- search vehicle modal -->
   <div class="modal fade" id="searchDMV" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Vehicle Database</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form>
                <select class="js-example-basic-single" name="vehicleSearch" id="vehicleSearch" onchange="showVeh(this.value)"></select>
              </form><br>
              <div id="showVehInfo"></div>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- search name modal -->
   <div class="modal fade" id="searchNameDB" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Name Database</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form>
                <select class="js-example-basic-single" name="nameSearch" id="nameSearch" onchange="showName(this.value)"></select>
              </form><br>
              <div id="showPersonInfo"></div>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- add bolo modal -->
   <div class="modal fade" id="addBoloModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Add Bolo</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form method="post" action="leo-index.php">
                <div class="form-group">
                   <input type="text" name="bolo_created_by" class="form-control" maxlength="126" readonly="true" value="<?php echo $_SESSION['identifier'] ?>" data-lpignore="true" />
                </div>
                <div class="form-group">
                  <select class="js-example-basic-single" name="vehicle_plate" id="vehicle_plate">
                  </select>
                </div>
                <div class="form-group">
                  <input type="text" name="vehicle_model" class="form-control" maxlength="126" placeholder="Vehicle Model" data-lpignore="true" required />
                </div>
                <div class="form-group">
                  <input type="text" name="vehicle_color" class="form-control" maxlength="126" placeholder="Vehicle Color" data-lpignore="true" required />
                </div>
                <div class="form-group">
                  <input type="text" name="bolo_reason" class="form-control" maxlength="255" placeholder="Bolo Reason" data-lpignore="true" required />
                </div>
           </div>
           <div class="modal-footer">
           <div class="form-group">
              <input class="btn btn-primary" name="addBoloBtn" id="addBoloBtn" type="submit" value="Add Bolo">
           </div>
           </form>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- new ticket modal -->
   <div class="modal fade" id="newTicket" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">New Ticket</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form method="post" action="leo-index.php">
                <div class="form-group">
                   <input type="text" name="ticketing_officer" class="form-control" maxlength="126" placeholder="Ticketing Officer" readonly="true" value="<?php echo $_SESSION['identifier'] ?>" data-lpignore="true" required />
                </div>
                <div class="form-group">
                  <select class="js-example-basic-single" name="suspect" id="suspect">
                  </select>
                </div>
                <div class="row">
                  <div class="col">
                    <div class="form-group">
                      <input type="text" name="location" class="form-control" placeholder="Ticket Location" data-lpignore="true" required />
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-group">
                      <input type="text" name="postal" class="form-control" pattern="\d*" placeholder="(Nearest Postal)" data-lpignore="true" required />
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <input type="text" name="amount" class="form-control" pattern="\d*" placeholder="Fine Amount" data-lpignore="true" required />
                </div>
                <div class="form-group">
                  <input type="text" name="reasons" class="form-control" maxlength="255" placeholder="Ticket Reasons" data-lpignore="true" required />
                </div>
           </div>
           <div class="modal-footer">
           <div class="form-group">
              <input class="btn btn-primary" name="createTicketbtn" id="createTicketbtn" type="submit" value="Create Ticket">
           </div>
           </form>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- notepad modal -->
   <div class="modal fade" id="notepad" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Notepad (Resets After Shift)</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form method="post" action="leo-index.php">
                <div class="form-group">
                  <?php if ($settings_theme_db === "lumen"): ?>
                    <textarea name="textarea" oninput="updateNotepad(this.value)" rows="12" cols="89"><?php echo $_SESSION['notepad']; ?></textarea>
                  <?php elseif ($settings_theme_db === "pulse" OR $settings_theme_db === "pulsev2"): ?>
                    <textarea name="textarea" oninput="updateNotepad(this.value)" rows="12" cols="89"><?php echo $_SESSION['notepad']; ?></textarea>
                  <?php elseif ($settings_theme_db === "simplex"): ?>
                    <textarea name="textarea" oninput="updateNotepad(this.value)" rows="12" cols="89"><?php echo $_SESSION['notepad']; ?></textarea>
                  <?php else: ?>
                    <textarea name="textarea" oninput="updateNotepad(this.value)" rows="12" cols="74"><?php echo $_SESSION['notepad']; ?></textarea>
                  <?php endif; ?>
                </div>
           </div>
           </form>
         </div>
      </div>
   </div>
   <!-- // -->
   <!-- new arrest report modal -->
   <div class="modal fade" id="arrestReportModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">New Arrest Report</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <form method="post" action="leo-index.php">
                <div class="form-group">
                   <input type="text" name="arresting_officer" class="form-control" maxlength="126" placeholder="Arresting Officer" readonly="true" value="<?php echo $_SESSION['identifier'] ?>" data-lpignore="true" required />
                </div>
                <div class="form-group">
                  <select class="js-example-basic-single" name="suspect" id="suspect_arr">
                  </select>
                </div>
                <div class="form-group">
                  <input type="text" name="summary" class="form-control" maxlength="255" placeholder="Charges" data-lpignore="true" required />
                </div>
           </div>
           <div class="modal-footer">
           <div class="form-group">
              <input class="btn btn-primary" name="createArrestReportbtn" id="createArrestReportbtn" type="submit" value="Create Arrest Report">
           </div>
           </form>
            </div>
         </div>
      </div>
   </div>
   <!-- // -->

   <!-- live map modal -->
   <div class="modal fade" id="liveMap" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalLabel">Live Map</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
              <iframe frameborder="0" width="100%" height="720" src="<?php echo $mapModule_link; ?>"></iframe>
            </div>
      </div>
   </div>
  </div>
   <!-- // -->
   <!-- end modals -->
   <!-- sounds -->
   <!-- <audio id="panicButton" src="assets/sounds/panic-button.mp3" preload="auto"></audio> -->
   <!-- end sounds -->
   <!-- js -->
   <script src="assets/js/pages/leo.js"></script>
   <!-- end js -->
</body>
</html>
