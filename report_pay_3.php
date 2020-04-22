<?php require_once('Connections/prod.php'); 
      require_once('Connections/habari.php'); 


//initialize the session
if (!isset($_SESSION)) {
  session_start();
}
log_activity($conn,'Viewed the card logs.');

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

?>

<!doctype html>
<html>
    <head>
        <title>Habari | Reports</title>
        <link href="style.css" rel="stylesheet" type="text/css">

    </head>
    <body>
        <div class="container">
            
            <form method='post' action='download.php'>
            <input type='submit' style="width:300px; height : 260px; font-size:10em;" value='Export' name='Export'>
                
            <table border='1' style='border-collapse:collapse;'>
                <tr>
                <th>S/N</th>
                <th>Username</th>
                    <th>Customer Number</th>
                    <th>Funding Method</th>
                    <th>Issuer</th>
                    <th>Masked Number</th>
                </tr>
            <?php 
            $query = "SELECT u.id, (ua.`userId`), u.`firstName`, u.`lastName`,u.`userName` as username,u.`customerNumber` as acc_number,u.`bvnNumber`, ua.`brand`,ua.`fundingMethod` as fund_method,ua.`issuer` as issuer,ua.`scheme`,ua.`cardHolderName`,ua.`cardStatus`,ua.maskedNumber as PAN from users u join `userCards` ua on u.id = ua.`userId` where ua.`cardStatus` = 'success' group by u.id order by ua.`userId`;";
            $result = mysqli_query($prodd,$query);
            $row = mysqli_fetch_array($result);
            $user_arr = array();
            $xxx = 0;
           
           
            do {
                $xxx++;
                $col1 = $row['username'];
                $col2 = $row['acc_number'];
                $col3 = $row['fund_method'];
                $col4 = $row['issuer'];
                $col5 = $row['PAN'];

       
                $user_arr[] = array($col1,$col2,$col3,$col4,$col5);
            ?>
                <tr>
                <td><?php echo $xxx; ?></td>
                    <td><?php echo $col1; ?></td>
                    <td><?php echo $col2; ?></td>
                    <td><?php echo $col3; ?></td>
                    <td><?php echo $col4; ?></td>
                    <td><?php echo $col5; ?></td>
                </tr>
            <?php
            }while($row = mysqli_fetch_array($result))
            ?>
            </table>
            <?php 
            $serialize_user_arr = serialize($user_arr);
            ?>
            <textarea name='export_data' style='display: none;'><?php echo $serialize_user_arr; ?></textarea>
            </form>
        </div>
    </body>
</html>

