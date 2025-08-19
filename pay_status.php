<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php require('inc/links.php'); ?>
  <title> <?php echo $settings_r['site_title'] ?>bookings status</title>


  </style>

</head>

<body class="bg-light">


  <?php require('inc/header.php'); ?>

  <?php


  if (!(isset($_SESSION['login'])  && $_SESSION['login'] == true)) {
    redirect('index.php');
  }
  //filter and get room and user data


  $data = filteration($_GET);
  $room_res = select("SELECT * FROM `rooms` WHERE `status`=? AND `remove`=?", [1, 0], 'ii');
  // if (mysqli_num_rows($room_res) == 0) {
  //   redirect('rooms.php');
  // }

  $room_data = mysqli_fetch_assoc($room_res);
  $_SESSION['room'] = [
    "id" => $room_data['id'],
    "name" => $room_data['name'],
    "price" => $room_data['price'],
    "payment" => null,
    "available" => false,
  ];

  $user_res = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$_SESSION['uid']], "i");
  $user_data = mysqli_fetch_assoc($user_res);

  ?>



  <div class="container">
    <div class="row">
      <div class="col-12 my-5 mb-3 px-4">
        <h2 class="fw-font">PAYMENT STATUS</h2>

      </div>
      <?php
      $frm_data = filteration($_GET);
      if (!(isset($_SESSION['login'])  && $_SESSION['login'] == true)) {
        redirect('index.php');
      }
      $booking_q = "SELECT bo.*, bd.* FROM `booking_order` bo
           INNER JOiN `booking_details` bd ON bo.booking_id=bd.booking_id
           WHERE bo.order_id=? AND bo.user_id=? AND bo.booking_status!=? AND bo.trans_status=? ";

      $booking_res = select($booking_q, [$frm_data["ORDERID"], $_SESSION['uid'], 'pending', "TXN_SUCCESS"], 'siss');
      // if (mysqli_num_rows($booking_res)==0)
      // {
      //   redirect('index.php');
      // }
      $booking_fetch = mysqli_fetch_assoc($booking_res);
      if ($booking_fetch['trans_status'] == "TXN_SUCCESS") {
        echo <<<data
              <div class="col-12 px-4">
              <p class="fw-bold alert alert-success">
              <i class="bi bi-check-circle-fill"></i>
              payment done!!booking successful.
              <br><br>
              <a href='bookings.php'>go to booking</a>
              </p>
              </div>
              data;
      } else {
        echo <<<data
              <div class="col-12 px-4">
              <p class="fw-bold alert alert-success">
              <i class="bi bi-exclamation-triangle-fill"></i>
              payment done!!booking successful.
              payment failed!!$booking_fetch[trans_resp_msg]
              <br><br>
              <a href='bookings.php'>go to booking</a>
              </p>
              </div>
              data;
      }
      ?>




    </div>
  </div>

  <?php require('inc/footer.php'); ?>
  <script>
    let booking_form = document.getElementById('booking_form');
    let info_loader = document.getElementById('info_loader');
    let pay_info = document.getElementById('pay_info');

    function check_availability() {
      let checkin_val = booking_form.elements['checkin'].value;
      let checkout_val = booking_form.elements['checkout'].value;

      booking_form.elements['pay_now'].setAttribute('disabled', true);

      if (checkin_val != '' && checkout_val != '') {

        pay_info.classList.add('d-none');
        pay_info.classList.replace('text-dark', 'text-danger');
        info_loader.classList.remove('d-none');

        let data = new FormData();
        data.append('check_availability', '');
        data.append('check_in', checkin_val);
        data.append('check_out', checkout_val);

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/confirm_booking.php", true);


        xhr.onload = function() {

          let data = JSON.parse(this.responseText);

          if (data.status == 'check_in_out_equal') {
            pay_info.innerText = "you cannot check-out on the same day!!";
          } else if (data.status == 'check_out_earlier') {
            pay_info.innerText = "check-out date is earlier than check-in date!!";
          } else if (data.status == 'check_in_earlier') {
            pay_info.innerText = "check-in date is earlier than today date!!";
          } else if (data.status == 'unavailable') {
            pay_info.innerText = "Room not available for thi check-in date!!";
          } else {
            pay_info.innerHTML = "No. of days:" + data.days + "<br>total amount to pay: ₹" + data.payment;
            pay_info.classList.replace('text-danger', 'text-dark');
            booking_form.elements['pay_now'].removeAttribute('disabled');

          }
          pay_info.classList.remove('d-none');
          info_loader.classList.add('d-none');

        }
        xhr.send(data);
      }
    }
  </script>


</body>

</html>