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

if (!panel_access) {
  session_unset();
  header('Location: ' . $url_login . '?unverified=true');
  exit();
}

if (isset($_POST['createIdentityBtn'])) {
    //Pull the variables from the form
    $identifier_form = !empty($_POST['identifier']) ? trim($_POST['identifier']) : null;
    //Sanitize the variables, prevents xss, etc.
    $identifier        = strip_tags($identifier_form);

    //check if the Identifier already exists
    $sql  = "SELECT COUNT(identifier) AS num FROM identities WHERE identifier = :identifier";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':identifier', $identifier);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['num'] > 0) {
        header('Location: ' . $url_index . '?identifier=taken');
        exit();
    }
    //else if everything passes, than continue
    if ($identity_approval_needed === "no") {
      $sql          = "INSERT INTO identities (identifier, user, user_name) VALUES (:identifier, :user, :user_name)";
      $stmt         = $pdo->prepare($sql);
      $stmt->bindValue(':identifier', $identifier);
      $stmt->bindValue(':user', $user_id);
      $stmt->bindValue(':user_name', $user_username);
      $result = $stmt->execute();
      if ($result) {
          //redirect
          header('Location: ' . $url_index . '?identifier=created');
      }
    } else {
      $sql          = "INSERT INTO identities (identifier, user, status, user_name) VALUES (:identifier, :user, :status, :user_name)";
      $status = "Approval Needed";
      $stmt         = $pdo->prepare($sql);
      $stmt->bindValue(':identifier', $identifier);
      $stmt->bindValue(':user', $user_id);
      $stmt->bindValue(':status', $status);
      $stmt->bindValue(':user_name', $user_username);
      $result = $stmt->execute();
      if ($result) {
          //redirect
          header('Location: ' . $url_index . '?identifier=approval');
      }
    }

}

if (isset($_GET['identifier']) && strip_tags($_GET['identifier']) === 'created') {
   $message = '<div class="alert alert-success" role="alert">Identifier added!</div>';
} elseif (isset($_GET['identifier']) && strip_tags($_GET['identifier']) === 'taken') {
   $message = '<div class="alert alert-danger" role="alert">That identifier is already taken.</div>';
} elseif (isset($_GET['identifier']) && strip_tags($_GET['identifier']) === 'approval') {
   $message = '<div class="alert alert-info" role="alert">Your identifier has been created, but the community owner has chosen to validate all new identities. Please do not pester staff about getting approved.</div>';
} elseif (isset($_GET['np']) && strip_tags($_GET['np']) === 'dispatch') {
  $message = '<div class="alert alert-danger" role="alert" id="dismiss">You are not assigned to Dispatch.</div>';
} elseif (isset($_GET['np']) && strip_tags($_GET['np']) === 'fire') {
  $message = '<div class="alert alert-danger" role="alert" id="dismiss">You are not assigned to Fire/EMS.</div>';
} elseif (isset($_GET['logged']) && strip_tags($_GET['logged']) === 'in') {
  logme('Logged In', $user_username);
}
?>
<!DOCTYPE html>
<html>
<?php
$page_name = "Home";
include('includes/header.php')
?>
<body>
   <div class="container">
      <div class="main">
         <img src="assets/imgs/los_santos.png" class="main-logo" draggable="false"/>
         <div class="main-header">
            Hello, <?php echo $user_username ?> <?php if (staff_access) {
              echo '<a href="staff.php"><i class="fab fa-adn"></i></a>';
            } ?>
         </div>
         <?php
         if ($update_in_progress === "Yes") {
           echo '<strong><font color="red">ALL PANELS ARE CURRENTLY OFFLINE.</font></strong>';
         } ?>
         <?php print($message); ?>
         <?php if (!setupComplete): ?>
           <div class="alert alert-danger" role="alert">YOU MUST CHANGE YOUR COMMUNITY NAME IN ACP BEFORE ACCESSING THE PANEL.</div>
           <a class="btn btn-primary btn-block btn-sb disabled">Civilian</a>

           <a class="btn btn-primary btn-block btn-sb disabled">Law Enforcement</a><br-leo>

           <a class="btn btn-primary btn-block btn-sb disabled">Dispatch</a><br-leo>

           <a class="btn btn-primary btn-block btn-sb disabled">Fire/EMS</a>

           <a class="btn btn-primary btn-block btn-sb disabled">Judge</a>
         <?php else: ?>
           <a href="<?php print $url_civ_index ?>" class="btn btn-primary btn-block btn-sb">Civilian</a>

           <a data-toggle="modal" href="#soim" class="btn btn-primary btn-block btn-sb">Law Enforcement</a><br-leo>

           <a data-toggle="modal" href="#soimdispatch" class="btn btn-primary btn-block btn-sb">Dispatch</a><br-leo>

           <a data-toggle="modal" href="#soimfire" class="btn btn-primary btn-block btn-sb">Fire/EMS</a>

           <a href="#" onClick="alert('Coming Soon');" class="btn btn-primary btn-block btn-sb">Judge</a>
         <?php endif; ?>

         <?php echo $ftter; ?>
      </div>
      <!-- select option i modal -->
      <div class="modal fade" id="soimdispatch" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Identity Options</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                   <a data-toggle="modal" href="#selectIdentifierdispatch" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Select Identifier</a><br-leo>
                   <a data-toggle="modal" href="#createIdentityModal" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Create Identity</a><br-leo>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- select option i modal -->
      <div class="modal fade" id="soim" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Identity Options</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                   <a data-toggle="modal" href="#selectIdentifier" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Select Identifier</a><br-leo>
                   <a data-toggle="modal" href="#createIdentityModal" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Create Identity</a><br-leo>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- create identifer modal -->
      <div class="modal fade" id="createIdentityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Create New Identity</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                  <form method="post" action="index.php">
                     <div class="form-group">
                        <input type="text" name="identifier" class="form-control" placeholder="EX: [C-01] John Doe" data-lpignore="true" required />
                     </div>
               </div>
               <div class="modal-footer">
               <div class="form-group">
               <input class="btn btn-primary" name="createIdentityBtn" type="submit" value="Create">
               </div>
               </form>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- select identifier modal -->
      <div class="modal fade" id="selectIdentifier" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Select Identifier</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                 <select class="form-control" name="character_list" onchange="location = this.value;">
                   <option selected="true" disabled="disabled">Select Identifier</option>
                   <?php
                   $status = 'Active';
                   $getIdentities = "SELECT * FROM identities WHERE user='$user_id' AND status='$status'";
                   $result = $pdo->prepare($getIdentities);
                   $result->execute();
                   while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                     echo '<option value="'. $url_leo_setId .'?i='. $row['identity_id'] .'">'. $row['identifier'] .'</option>';
                   }
                    ?>
                 </select>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- select identifier modal -->
      <div class="modal fade" id="selectIdentifierdispatch" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Select Identifier</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                 <select class="form-control" name="character_list" onchange="location = this.value;">
                   <option selected="true" disabled="disabled">Select Identifier</option>
                   <?php
                   $status = 'Active';
                   $getIdentities = "SELECT * FROM identities WHERE user='$user_id' AND status='$status'";
                   $result = $pdo->prepare($getIdentities);
                   $result->execute();
                   while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                     echo '<option value="'. $url_dispatch_setid .'?i='. $row['identity_id'] .'">'. $row['identifier'] .'</option>';
                   }
                    ?>
                 </select>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- select id fire modal -->
      <div class="modal fade" id="soimfire" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Identity Options</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                   <a data-toggle="modal" href="#selectIdentifierfire" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Select Identifier</a><br-leo>
                   <a data-toggle="modal" href="#createIdentityModal" data-dismiss="modal" class="btn btn-primary btn-block btn-sb">Create Identity</a><br-leo>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
      <!-- select identifier modal -->
      <div class="modal fade" id="selectIdentifierfire" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Select Identifier</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                 <select class="form-control" name="character_list" onchange="location = this.value;">
                   <option selected="true" disabled="disabled">Select Identifier</option>
                   <?php
                   $status = 'Active';
                   $getIdentities = "SELECT * FROM identities WHERE user='$user_id' AND status='$status'";
                   $result = $pdo->prepare($getIdentities);
                   $result->execute();
                   while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                     echo '<option value="fire-setid.php?i='. $row['identity_id'] .'">'. $row['identifier'] .'</option>';
                   }
                    ?>
                 </select>
               </div>
            </div>
         </div>
      </div>
      <!-- end modal -->
   </div>
</body>
</html>
