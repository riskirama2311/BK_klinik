<?php
session_start();
include_once("../../config/conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama = htmlspecialchars($_POST['nama']);
  $alamat = htmlspecialchars($_POST['alamat']);
  $no_ktp = htmlspecialchars($_POST['no_ktp']);
  $no_hp = htmlspecialchars($_POST['no_hp']);

  // Cek apakah pasien sudah terdaftar berdasarkan nomor KTP menggunakan prepared statements
  $check_pasien = $conn->prepare("SELECT id, nama, no_rm FROM pasien WHERE no_ktp = ?");
  $check_pasien->bind_param("s", $no_ktp);
  $check_pasien->execute();
  $result_check_pasien = $check_pasien->get_result();

  if ($result_check_pasien->num_rows > 0) {
    $row = $result_check_pasien->fetch_assoc();
    if ($row['nama'] != $nama) {
      echo "<script>alert('Nama pasien tidak sesuai dengan nomor KTP yang terdaftar.');</script>";
      echo "<meta http-equiv='refresh' content='0; url=register.php'>";
      die();
    }
    $_SESSION['signup'] = true;
    $_SESSION['id'] = $row['id'];
    $_SESSION['username'] = $nama;
    $_SESSION['no_rm'] = $row['no_rm'];
    $_SESSION['akses'] = 'pasien';

    echo "<meta http-equiv='refresh' content='0; url=../pasien'>";
    die();
  }

  // Mendapatkan nomor pasien terakhir
  $get_rm = $conn->prepare("SELECT MAX(SUBSTRING(no_rm, 8)) AS last_queue_number FROM pasien");
  $get_rm->execute();
  $result_rm = $get_rm->get_result();

  if ($result_rm->num_rows > 0) {
    $row_rm = $result_rm->fetch_assoc();
    $lastQueueNumber = $row_rm['last_queue_number'] ? $row_rm['last_queue_number'] : 0;
  } else {
    $lastQueueNumber = 0;
  }
  $tahun_bulan = date("Ym");
  $newQueueNumber = $lastQueueNumber + 1;
  $no_rm = $tahun_bulan . "-" . str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);

  $insert = $conn->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) VALUES (?, ?, ?, ?, ?)");
  $insert->bind_param("sssss", $nama, $alamat, $no_ktp, $no_hp, $no_rm);

  if ($insert->execute()) {
    $_SESSION['signup'] = true;
    $_SESSION['id'] = $insert->insert_id;
    $_SESSION['username'] = $nama;
    $_SESSION['no_rm'] = $no_rm;
    $_SESSION['akses'] = 'pasien';

    echo "<meta http-equiv='refresh' content='0; url=../pasien'>";
    die();
  } else {
    echo "Error: " . $insert->error;
  }

  $insert->close();
  $check_pasien->close();
  $get_rm->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Poliklinik | Registration Page</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <style>
    @media (min-width: 1025px) {
      .h-custom-2 {
        height: 100%;
      }
    }
  </style>
</head>
<body>
<section class="vh-100">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 text-black">
        <div class="px-5 ms-xl-4">
          <span class="h1 fw-bold mb-0">BK-Poliklinik</span>
        </div>
        <br>
        <div class="d-flex align-items-center h-custom-2 px-5 ms-xl-4 mt-5 pt-5 pt-xl-0 mt-xl-n5">
          <form style="width: 23rem;" action="" method="post">
            <h3 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Register Pasien</h3>

            <div class="form-outline mb-4">
              <input type="text" id="nama" class="form-control form-control-lg" name="nama" required />
              <label class="form-label" for="nama">Full name</label>
            </div>

            <div class="form-outline mb-4">
              <input type="text" id="alamat" class="form-control form-control-lg" name="alamat" required />
              <label class="form-label" for="alamat">Alamat</label>
            </div>

            <div class="form-outline mb-4">
              <input type="number" id="no_ktp" class="form-control form-control-lg" name="no_ktp" required />
              <label class="form-label" for="no_ktp">No KTP</label>
            </div>

            <div class="form-outline mb-4">
              <input type="number" id="no_hp" class="form-control form-control-lg" name="no_hp" required />
              <label class="form-label" for="no_hp">No HP</label>
            </div>

            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" id="agreeTerms" name="terms" value="agree" required />
              <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">terms</a></label>
            </div>

            <div class="pt-1 mb-4">
              <button class="btn btn-info btn-lg btn-block" type="submit">Register</button>
            </div>

           
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
</body>
</html>
