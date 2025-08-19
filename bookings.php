<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php require('inc/links.php'); ?>
  <title> <?php echo $settings_r['site_title'] ?> bookings</title>


  </style>

</head>

<body class="bg-light">


  <?php require('inc/header.php');
  if (!(isset($_SESSION['login'])  && $_SESSION['login'] == true)) {
    redirect('index.php');
  }
  ?>





  <div class="container">
    <div class="row">
      <div class="col-12 my-5  px-4">
        <h2 class="fw-font">booking</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration">HOME</a>
          <span class="text-secondary">></span>
          <a href="#" class="text-secondary text-decoration">BOOKING</a>
        </div>
      </div>


      <?php
      $query = "SELECT bo.*,bd.* FROM `booking_order` bo
                INNER JOIN `booking_details` bd ON bo.booking_id=bd.booking_id
                WHERE ((bo.booking_status='booked')
                 OR(bo.booking_status='cancelled')
                 OR (bo.booking_status='payment failed'))
                 AND(bo.user_id=?)
                 ORDER BY bo.booking_id DESC";
      $result = select($query, [$_SESSION['uid']], 'i');

      while ($data = mysqli_fetch_assoc($result)) {
        $date = date("d-m-Y", strtotime($data['datentime']));
        $checkin = date("d-m-Y", strtotime($data['check_in']));
        $checkout = date("d-m-Y", strtotime($data['check_out']));

        $status_bg = "";
        $btn = "";

        if ($data['booking_status'] == 'booked') {
          $status_bg = "bg-success";
          if ($data['arrival'] == 1) {
            $btn = "<a  href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none' >download pdf</a>";
            if ($data['rate_review'] == 0) {
              $btn .= "<button type='button' onclick='review_room($data[booking_id],$data[room_id])' data-bs-toggle='modal' data-bs-target='#reviewmodal' class=' btn btn-dark btn-sm shadow-none ms-2' >Rate & Review</button>";
            }
          } else {
            $btn = "<button type='button' onclick='cancle_booking($data[booking_id])' class=' btn btn-danger btn-sm shadow-none' >Cancle</button>";
          }
        } else if ($data['booking_status'] == 'cancelled') {
          $status_bg = "bg-danger";

          if ($data['refund'] == 0) {
            $btn = "<span class='badge bg-primary'>Refund in process</span>";
          } else {
            $btn = "<a  href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none' >download pdf</a>";
          }
        } else {
          $status_bg = "bg-warning";
          $btn = "<a  href='generate_pdf.php?gen_pdf&id=$data[booking_id]' class='btn btn-dark btn-sm shadow-none'>download pdf</a>";
        }
        if ($data['room_no'] != NULL) {
          echo <<<booking
          <div class='col-md-4 px-4 mb-4'>
            <div class='bg-white p-3 rounded shadow-sm'>
              <h5 class='fw-bold'>$data[room_name]</h5>
              <p>₹$data[price]</p>
              <p>
                <b>Check in:</b>$checkin<br>
                <b>Check out:</b>$checkout
              </p>
              <p class=''>
              <b>Amount:</b>₹$data[trans_amt]<br>
              <b>Order Id:</b>$data[order_id]<br>
              <b>Date:</b>$date<br>
              <b>Room No:</b>$data[room_no]
            </p>
            <p class=''>
                <span class='badge $status_bg'>$data[booking_status]</span>
           </p>
           $btn
            </div>
          </div>

        booking;
        }
        else{
          echo <<<booking
          <div class='col-md-4 px-4 mb-4'>
            <div class='bg-white p-3 rounded shadow-sm'>
              <h5 class='fw-bold'>$data[room_name]</h5>
              <p>₹$data[price]</p>
              <p>
                <b>Check in:</b>$checkin<br>
                <b>Check out:</b>$checkout
              </p>
              <p class=''>
              <b>Amount:</b>₹$data[trans_amt]<br>
              <b>Order Id:</b>$data[order_id]<br>
              <b>Date:</b>$date<br>
             
            </p>
            <p class=''>
                <span class='badge $status_bg'>$data[booking_status]</span>
           </p>
           $btn
            </div>
          </div>

        booking;
        }
      }



      ?>
    </div>
  </div>

  <div class="modal fade" id="reviewmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <form id="review-form">
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center">
              <i class="bi bi-chat-square-heart-fill fs-3 me-2"></i> Rate & Review
            </h5>
            <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Rating</label>
              <select class="form-select shadow-none" name="rating">
                <option value="5">Excellent</option>
                <option value="4">good</option>
                <option value="3">Ok</option>
                <option value="2">Poor</option>
                <option value="1">Bad</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label">Review</label>
              <textarea type="password" name="review" rows="3" required class="form-control shadow-none"></textarea>
            </div>
            <input type="hidden" name="booking_id">
            <input type="hidden" name="room_id">

            <div class="text-end">
              <button type="submit" class="btn custom-bg text-white shadow-none ">Submit</button>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>




  <?php
  if (isset($_GET['cancle_status'])) {
    alert('success', 'booking canclled!!');
  } else if (isset($_GET['review_status'])) {
    alert('success', 'Thank you for rating & review!!');
  }
  ?>

  <?php require('inc/footer.php'); ?>

  <script>
    function cancle_booking(id) {
      if (confirm('are you sure to cancle bookings?')) {


        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/cancle_booking.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


        xhr.onload = function() {
          if (this.responseText == 1) {
            window.location.href = "bookings.php?cancel_status=true";
          } else {
            alert('error', 'cancllation failed!!');
          }

        }

        xhr.send('cancle_booking&id=' + id);
      }
    }


    let review_form = document.getElementById('review-form');

    function review_room(bid, rid) {
      review_form.elements['booking_id'].value = bid;
      review_form.elements['room_id'].value = rid;

    }

    review_form.addEventListener('submit', function(e) {
      e.preventDefault();

      let data = new FormData();
      data.append('review_form', '');
      data.append('rating', review_form.elements['rating'].value);
      data.append('review', review_form.elements['review'].value);
      data.append('booking_id', review_form.elements['booking_id'].value);
      data.append('room_id', review_form.elements['room_id'].value);

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "ajax/review_room.php", true);


      xhr.onload = function() {

        if (this.responseText == 1) {
          window.location.href = 'bookings.php?review_status=true';

        } else {
          var myModal = document.getElementById('reviewmodal');
          var modal = bootstrap.Modal.getInstance(myModal);
          modal.hide();
          alert('error', 'Rating & Review Failed!!');

        }
      }

      xhr.send(data);

    });
  </script>

</body>

</html>