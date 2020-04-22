<?php require_once('Connections/magento.php'); 
      require_once('Connections/habari.php'); 

//initialize the session
if (!isset($_SESSION)) {
  session_start();
}
log_activity($conn,'Viewed the 3K store.');


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
                <th>product_name</th>
                    <th>Qty</th>
                    <th>sku</th>
                    <th>price</th>
                    <th>special_price</th>
                </tr>
            <?php 
            $query = "SELECT cpe.entity_id as product_id,cpe.sku as sku, cpei.value as product_name,csi.qty as qty,cped.value as price,cped2.value as special_price, cpei.value as value 
            FROM catalog_product_entity as cpe 
            INNER JOIN catalog_product_entity_decimal as cped 
            on cpe.entity_id = cped.entity_id 
            and cped.attribute_id = 77 
            INNER JOIN catalog_product_entity_varchar as cpei 
            on cpe.entity_id = cpei.entity_id and cpei.attribute_id = 73 
            INNER JOIN catalog_product_entity_decimal as cped2 
            on cpe.entity_id = cped2.entity_id and cped2.attribute_id = 78 
            INNER JOIN cataloginventory_stock_item as csi 
            ON csi.product_id = cpe.entity_id and csi.website_id = 0 
            INNER JOIN marketplace_product as mp 
            ON mp.mageproduct_id = cpe.entity_id 
            INNER JOIN marketplace_userdata as mu 
            ON mu.seller_id = mp.seller_id
            where cped2.value <= 3000";
            $result = mysqli_query($magento,$query);
            $row = mysqli_fetch_array($result);
            $user_arr = array();
            $xxx = 0;
           
           
            do {
                $xxx++;
                $col1 = $row['product_name'];
                $col2 = $row['qty'];
                $col3 = $row['value'];
                $col4 = $row['price']; 
                $col5 = $row['special_price'];

       
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

