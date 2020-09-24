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

if (isset($_GET['i']) && is_numeric($_GET['i']) && filter_var($_GET['i'], FILTER_VALIDATE_INT)) {
    $i   = $_GET['i'];
    $sql  = "SELECT * FROM identities WHERE identity_id = :i";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':i', $i);
    $stmt->execute();
    $identity = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($identity === false) {
       header('Location: ' . $url_index . '');
       exit();
    } else {
      //set the needed session variables
       $sidentity_id    = $identity['identity_id'];
       $_SESSION['identity_id'] = $sidentity_id;

       $sidentity_name    = $identity['identifier'];
       $_SESSION['identifier'] = $sidentity_name;

       if ($identity['leo_supervisor'] === "Yes") {
         $_SESSION['leo_supervisor'] = "Yes";
       } else {
         $_SESSION['leo_supervisor'] = "No";
       }

       $_SESSION['active_dispatch'] = "No";

       $_SESSION['sub_division'] = "None";

       $_SESSION['notepad'] = "";

       $sidentity_user    = $identity['user'];
       $_SESSION['on_duty'] = "No";
       header('Location: ' . $url_leo_index . '');
       exit();

    } if ($sidentity_user !== $user_id) {
      header('Location: ../../' . $url_index . '');
      exit();
    }
 }
