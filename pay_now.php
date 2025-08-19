<?php
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

require('inc/paytm/config_paytm.php');
require('inc/paytm/encdec_paytm.php');

date_default_timezone_set("Asia/Kolkata");

session_start();
if (!(isset($_SESSION['login'])  && $_SESSION['login'] == true)) {
    redirect('index.php');
}

if (isset($_POST['pay_now'])) {
    header("Pragma: no-cache");
    header("Cache-Control: no-cache");
    header("Expires: 0");


    $checkSum = "";

    $ORDER_ID = 'ORD_' . $_SESSION['uid'] . random_int(11111, 99999);
    $CUST_ID = $_SESSION['uid'];
    $INDUSTRY_TYPE_ID = INDUSTRY_TYPE_ID;
    $CHANNEL_ID = CHANNEL_ID;
    $TXN_AMOUNT = $_SESSION['room']['payment'];

    // Create an array having all required parameters for creating checksum.
    $paramList = array();
    $paramList["MID"] = PAYTM_MERCHANT_MID;
    $paramList["ORDER_ID"] = $ORDER_ID;
    $paramList["CUST_ID"] = $CUST_ID;
    $paramList["INDUSTRY_TYPE_ID"] = $INDUSTRY_TYPE_ID;
    $paramList["CHANNEL_ID"] = $CHANNEL_ID;
    $paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
    $paramList["WEBSITE"] = PAYTM_MERCHANT_WEBSITE;

    $paramList["CALLBACK_URL"] = CALLBACK_URL;

    //Here checksum string will return by getChecksumFromArray() function.
    $checkSum = getChecksumFromArray($paramList, PAYTM_MERCHANT_KEY);

    //insert payment data into databse

    $frm_data = filteration($_POST);
    $query1 = "INSERT INTO `booking_order`(`user_id`, `room_id`, `check_in`, `check_out`,`booking_status`,`trans_amt`, `trans_status`, `order_id`)    VALUES (?,?,?,?,?,?,?,?)";
    insert($query1, [$CUST_ID, $_SESSION['room']['id'], $frm_data['checkin'], $frm_data['checkout'],"booked", $TXN_AMOUNT, "TXN_SUCCESS", $ORDER_ID], 'issssiss');

    $booking_id = mysqli_insert_id($con);

    $query2 = "INSERT INTO `booking_details`(`booking_id`, `room_name`, `price`, `total_pay`, `user_name`, `phonenum`, `address`) 
    VALUES (?,?,?,?,?,?,?)";

    insert($query2, [
        $booking_id, $_SESSION['room']['name'], $_SESSION['room']['price'], $TXN_AMOUNT, $frm_data['name'], $frm_data['phonenum'],
        $frm_data['address']
    ], 'issssss');
}

?>
<html>

<head>
    <title>Processing</title>
</head>

<body>
    <h1>Please do not refresh this page...</h1>
    <form method="POST" action="<?php echo PAYTM_TXN_URL ?>" name="f1">
        <?php
      //  foreach ($paramList as $name => $value) {
        //    echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        //}
        ?>
        <input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum ?>">
    </form>
    <script type="text/javascript">
        document.f1.submit();
    </script>
</body>

</html>