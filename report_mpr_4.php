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
                    <th>username</th>
                    <th>prev_count</th>
                    <th>prev_amount</th>
                    <th>curr_count</th>
                    <th>curr_amount</th>
                </tr>
            <?php 
            $query = "SELECT u.userName as username, u.firstName as firstname, u.lastName as lastname, date(u.lastSeen) as 'LastSeen', date(max(tn.createdAt)) as 'LastTxn', 
            format(count( case when tn.status = 'success' and date(tn.createdAt) BETWEEN '2020-03-27' and '2020-04-03' then tn.fromAmount END),0) as prev_count, 
            format(sum( case when tn.status = 'success' and date(tn.createdAt) BETWEEN '2020-03-27' and '2020-04-03' then tn.fromAmount END),0) as prev_amount,  
            format(count( case when tn.status = 'success' and date(tn.createdAt) BETWEEN '2020-04-04' and '2020-04-10' then tn.fromAmount END),0) as curr_count,  
            format(sum( case when tn.status = 'success' and date(tn.createdAt) BETWEEN '2020-04-04' and '2020-04-10' then tn.fromAmount END),0) as curr_amount 
            from users u
            left join transactionHistoryNew tn
            on u.id = tn.fromUser
            WHERE u.userName in ('emeka.uche','munaokeynwosu19','obish1','rachelade','yolzzie','dayogeorge',
            'noni_ify','donsaba1','ganiiii','nunu','kemie','abdulsalamelelu','yinka4388','aaresha',
            'i_am_gabriel', 'kemionas', 'onyekus', 'Toladinni', 'kaysolz', 'claradelakun', 'Adesuwa', 'topchris') 
            and isfor != 'Topup'
            group by u.userName
            order by Count DESC;
            ";
            $result = mysqli_query($prodd,$query);
            $row = mysqli_fetch_array($result);
            $user_arr = array();
            $xxx = 0;
           
           
            do {
                $xxx++;
                $col1 = $row['username'];
                $col2 = $row['prev_count'];
                $col3 = $row['prev_amount'];
                $col4 = $row['curr_count'];
                $col5 = $row['curr_amount'];

       
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

