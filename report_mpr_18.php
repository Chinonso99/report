<?php require_once('Connections/prod.php'); 

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

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
                    <th>isFor</th>
                    <th>amount</th>
                    <th>count</th>
                    <th></th>
                    <th></th>
                </tr>
            <?php 
            $query = "SELECT isFor, FORMAT(SUM(fromAmount),2) as amount, count(*) as total
            from transactionHistoryNew
            where  date(createdat) between '2020-04-04' and '2020-04-10'
            
            group by isFor";
            $result = mysqli_query($prodd,$query);
            $row = mysqli_fetch_array($result);
            $user_arr = array();
            $xxx = 0;
           
           
            do {
                $xxx++;
                $col1 = $row['isFor'];
                $col2 = $row['amount'];
                $col3 = $row['total'];
                $col4 = '-';
                $col5 = '-';

       
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

