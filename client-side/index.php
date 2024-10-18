<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'koneksi.php';
require 'vendor/autoload.php';  // Path ke PHPMailer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = $_POST['companyName'];
    $contactPerson = $_POST['contactPerson'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Waktu pengiriman
    $sent_at = date('Y-m-d H:i:s');

    // Query untuk menyimpan data ke tabel kemitraan
    $stmt = $conn->prepare("INSERT INTO kemitraan (nama, perusahaan, email, nomor_handphone, alamat, pdf, status, sent_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param('sssssss', $contactPerson, $companyName, $email, $phone, $address, $pdf, $sent_at);

    // Upload PDF
    if (!empty($_FILES['uploadPDF']['tmp_name'])) {
        $pdf = addslashes(file_get_contents($_FILES['uploadPDF']['tmp_name']));
    } else {
        $pdf = null;
    }

    if ($stmt->execute()) {
        $id_kemitraan = $stmt->insert_id; // Ambil ID dari record terakhir yang disimpan

        // Array untuk menyimpan gambar yang valid
$images = [];

// Upload gambar setelah menyimpan kemitraan
if (!empty($_FILES['uploadImages']['name'][0])) {
  foreach ($_FILES['uploadImages']['tmp_name'] as $key => $tmp_name) {
      if ($_FILES['uploadImages']['error'][$key] === UPLOAD_ERR_OK) {
          $imageData = file_get_contents($tmp_name); // Mengambil konten file gambar
          $imageData = mysqli_real_escape_string($conn, $imageData); // Escape data untuk menghindari error SQL

          // Gantilah $id_kemitraan dengan ID kemitraan yang sesuai
          $sql = "INSERT INTO photo (id_kemitraan, photo) VALUES ('$id_kemitraan', '$imageData')";
          if ($conn->query($sql) === FALSE) {
              echo "Error: " . $conn->error;
          }
      } else {
          echo "<script>alert('Gagal mengupload gambar: " . $_FILES['uploadImages']['error'][$key] . "');</script>";
      }
  }
}

// Jika array gambar tidak kosong, simpan ke database
if (!empty($images)) {
    foreach ($images as $imageData) {
        // Save image to photo table
        $stmtImage = $conn->prepare("INSERT INTO photo (id_kemitraan, photo) VALUES (?, ?)");
        $stmtImage->bind_param('ib', $id_kemitraan, $null);
        $stmtImage->send_long_data(1, $imageData); // Mengirim data binary ke MySQL
        
        if (!$stmtImage->execute()) {
            echo "<script>alert('Gagal menyimpan gambar: " . $stmtImage->error . "');</script>";
        }
        $stmtImage->close();
    }
}

        // Ambil email admin dari database
        $adminEmailQuery = "SELECT email FROM users WHERE role = 'Admin'";
        $result = $conn->query($adminEmailQuery);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $adminEmail = $row['email'];

                // Kirim notifikasi email ke admin
                try {
                    $mail = new PHPMailer(true);
                    // Konfigurasi server SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'arrientysmtp@gmail.com';  // Ganti dengan email kamu
                    $mail->Password   = 'tnvo houl uzxh emyx';      // Ganti dengan App Password yang benar
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Penerima
                    $mail->setFrom('arrientysmtp@gmail.com', 'Arrienty Notification'); // Email dan nama pengirim
                    $mail->addAddress($adminEmail); // Email admin dari database

                    // Konten email
                    $mail->isHTML(true); // Menggunakan format HTML
                    $mail->Subject = 'Pengajuan Kemitraan Baru';
                    $mail->Body    = "
                        <h3>Ada pengajuan kemitraan baru:</h3>
                        <p><b>Nama Perusahaan:</b> {$companyName}</p>
                        <p><b>Nama Kontak:</b> {$contactPerson}</p>
                        <p><b>Email:</b> {$email}</p>
                        <p><b>Nomor Telepon:</b> {$phone}</p>
                        <p><b>Alamat:</b> {$address}</p>
                        <p><b>Dikirim pada:</b> {$sent_at}</p>
                    ";
                    $mail->AltBody = "Pengajuan kemitraan baru dari {$companyName}. Nama kontak: {$contactPerson}, Email: {$email}, Nomor telepon: {$phone}, Alamat: {$address}.";

                    // Kirim email
                    $mail->send();
                    echo "<script>alert('Pengajuan kemitraan berhasil dikirim dan notifikasi sudah dikirim ke admin.');</script>";
                } catch (Exception $e) {
                    echo "<script>alert('Pengajuan berhasil, namun gagal mengirim notifikasi email ke admin. Error: {$mail->ErrorInfo}');</script>";
                }
            }
        } else {
            echo "<script>alert('Pengajuan berhasil, namun tidak ada admin yang terdaftar untuk dikirimkan email notifikasi.');</script>";
        }
    } else {
        echo "<script>alert('Gagal mengirim pengajuan: " . $conn->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>



  
<!doctype html>
<html class="h-100" lang="en">

  <head>
      <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
  <meta name="description" content="A well made and handcrafted Bootstrap 5 template">
  <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
  <link rel="icon" type="image/png" sizes="96x96" href="img/favicon.png">
  <meta name="author" content="Holger Koenemann">
  <meta name="generator" content="Eleventy v2.0.0">
  <meta name="HandheldFriendly" content="true">
  <title>Home - MitraRynnxz</title>
  <link rel="stylesheet" href="css/theme.min.css">


   <style>

/* inter-300 - latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 300;
  font-display: swap;
  src: local(''),
       url('fonts/inter-v12-latin-300.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
       url('fonts/inter-v12-latin-300.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
}

/* inter-400 - latin */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: local(''),
       url('fonts/inter-v12-latin-regular.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
       url('fonts/inter-v12-latin-regular.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
}

@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: local(''),
       url('fonts/inter-v12-latin-500.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
       url('fonts/inter-v12-latin-500.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: local(''),
       url('fonts/inter-v12-latin-700.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */
       url('fonts/inter-v12-latin-700.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
}

</style>
  

  </head>

  <body class="bg-black text-white mt-0" data-bs-spy="scroll" data-bs-target="#navScroll">

    <nav id="navScroll" class="navbar navbar-dark bg-black fixed-top px-vw-5" tabindex="0">
        <div class="container">
          <a class="navbar-brand pe-md-4 fs-4 col-12 col-md-auto text-center" href="index.html">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-stack" viewBox="0 0 16 16">
          <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.598.598 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.598.598 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.598.598 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535L7.733.063z"/>
          <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.598.598 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.659z"/>
        </svg>
        <span class="ms-md-1 mt-1 fw-bolder me-md-5">MitraRynnxz</span>
      </a>

      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 list-group list-group-horizontal">
      <li class="nav-item">
        <a class="nav-link fs-5" href="index.html" aria-label="Homepage">
          Home
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link fs-5" href="content.html" aria-label="A sample content page">
          Content
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link fs-5" href="system.html" aria-label="A system message page">
          System
        </a>
      </li>

    </ul>
      <a href="#" aria-label="Gabung Sekarang" class="btn btn-outline-light">
        <small>Gabung Sekarang</small>
      </a>
      
      <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <span class="d-none d-md-block dropdown-toggle ps-2">nama</span>
        </a><!-- End Profile Iamge Icon -->

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6>nama</h6>
            <span>status</span>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>

          <li>
            <a class="dropdown-item d-flex align-items-center" href="php/logout.php">
              <i class="bi bi-box-arrow-right"></i>
              <span>Sign Out</span>
            </a>
          </li>

        </ul>
      </li>
</div>
</nav>

    <main>
      <div class="w-100 overflow-hidden position-relative bg-black text-white" data-aos="fade">
  <div class="position-absolute w-100 h-100 bg-black opacity-75 top-0 start-0"></div>
  <div class="container py-vh-4 position-relative mt-5 px-vw-5 text-center">
  <div class="row d-flex align-items-center justify-content-center py-vh-5">
    <div class="col-12 col-xl-10">
      <span class="h5 text-secondary fw-lighter">Solusi IT terdepan</span>
      <h1 class="display-huge mt-3 mb-3 lh-1">Meningkatkan efisiensi tenaga IT dan operasional</h1>
    </div>
    <div class="col-12 col-xl-8">
      <p class="lead text-secondary">Dengan pengembangan perangkat lunak inovatif, kami membantu Anda mencapai target bisnis lebih cepat dan lebih efektif.</p>
    </div>
    <div class="col-12 text-center">
      <a href="#" class="btn btn-xl btn-light">Ajukan kemitraan sekarang!
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
</svg>
</a>
    </div>
  </div>
</div>

</div>

<div class="bg-dark">
  <div class="container px-vw-5 py-vh-5">
    <div class="row d-flex align-items-center">
      <div class="col-12 col-lg-7 text-lg-end" data-aos="fade-right">
        <span class="h5 text-secondary fw-lighter">Apa yang kami punya</span>
        <h2 class="display-4">MitraRynnxz siap membantu segala kebutuhan IT Anda, mulai dari pengembangan perangkat lunak hingga dukungan server.</h2>
      </div>
      <div class="col-12 col-lg-5" data-aos="fade-left">
        <h3 class="pt-5">Product Design & Strategy</h3>
        <p class="text-secondary">Kami memiliki ahli desain dan strategi pemasaran yang siap membantu produk Anda dikenal luas dengan cepat.<br>
          <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
          </svg>
        </p>
          <h3 class="border-top border-secondary pt-5 mt-5">Development & Engineering</h3>
            <p class="text-secondary">Tim kami juga terdiri dari ahli pengembangan perangkat lunak, baik website, mobile, maupun desktop, serta spesialis server.<br>
          <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
        </svg>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="bg-black py-vh-3">
  <div class="container bg-black px-vw-5 py-vh-3 rounded-5 shadow">

  <div class="row gx-5">
    <div class="col-12 col-md-6">
      <div class="card bg-transparent mb-5" data-aos="zoom-in-up">
        <div class="bg-dark shadow rounded-5 p-0">
          <img src="img/game-development.png" width="582" height="327" alt="abstract image" class="img-fluid rounded-5 no-bottom-radius" loading="lazy">
          <div class="p-5">
            <h2 class="fw-lighter">Game Development</h2>
            <p class="pb-4 text-secondary">Tim kami mengembangkan game yang inovatif dan menarik, dengan fokus pada pengalaman pengguna yang luar biasa dan desain yang memukau.</p>
            <a href="#" class="link-fancy link-fancy-light">Ajukan Kemitraan Sekarang!</a>
          </div>
        </div>
      </div>

      <div class="card bg-transparent" data-aos="zoom-in-up">
        <div class="bg-dark shadow rounded-5 p-0">
          <img src="img/web-development.png" width="582" height="442" alt="abstract image" class="img-fluid rounded-5 no-bottom-radius" loading="lazy">
          <div class="p-5">
            <h2 class="fw-lighter">Web Development</h2>
            <p class="pb-4 text-secondary">Kami menawarkan layanan pengembangan web yang profesional dan responsif, memastikan kehadiran online Anda menarik dan fungsional.</p>
            <a href="#" class="link-fancy link-fancy-light">Ajukan Kemitraan Sekarang!</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="p-5 pt-0 mt-5" data-aos="fade">
        <span class="h5 text-secondary fw-lighter">Apa yang sih yang kami ga punya?</span>
        <h2 class="display-4">Berikut jasa yang kami sediakan</h2>
      </div>
      <div class="card bg-transparent mb-5 mt-5" data-aos="zoom-in-up">
        <div class="bg-dark shadow rounded-5 p-0">
          <img src="img/it_support.jpg" width="582" height="390" alt="abstract image" class="img-fluid rounded-5 no-bottom-radius" loading="lazy">
          <div class="p-5">
            <h2 class="fw-lighter">IT Support</h2>
            <p class="pb-4 text-secondary">Kami menyediakan dukungan IT yang handal, siap membantu menyelesaikan masalah teknis dan memastikan sistem Anda berjalan lancar.</p>
            <a href="#" class="link-fancy link-fancy-light">Ajukan Kemitraan Sekarang!</a>
          </div>
        </div>
      </div>

      <div class="card bg-transparent" data-aos="zoom-in-up">
        <div class="bg-dark shadow rounded-5 p-0">
          <img src="img/desain-grafis.png" width="582" height="327" alt="abstract image" class="img-fluid rounded-5 no-bottom-radius" loading="lazy">
          <div class="p-5">
            <h2 class="fw-lighter">Desain Grafis</h2>
            <p class="pb-4 text-secondary">Layanan desain grafis kami menciptakan visual yang menarik dan kreatif, meningkatkan daya tarik brand Anda di pasar.</p>
            <a href="#" class="link-fancy link-fancy-light">Ajukan Kemitraan Sekarang!</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

</div>




<div class="bg-black">
<div class="container px-vw-5 py-vh-5">
  <div class="row d-flex align-items-center">
    <div class="col-12 col-lg-5 text-center text-lg-end" data-aos="zoom-in-right">
      <span class="h5 text-secondary fw-lighter">Berapa sih harga kemitraan kami?</span>
      <h2 class="display-4">Ajukan sekarang, Harga sesuai kontrak!</h2>
    </div>
    <div class="col-12 col-lg-7 bg-dark rounded-5 py-vh-3 text-center my-5" data-aos="zoom-in-up">
      <h2 class="display-huge mb-5">
        <span class="fs-4 me-2 fw-light">$</span><span class="border-bottom border-5">???</span><span class="fs-6 fw-light">/Kontrak</span></h2>
      <p class="lead text-secondary">Bergabunglah dalam kemitraan IT kami dengan biaya terjangkau yang dirancang untuk meningkatkan efisiensi dan inovasi bisnis Anda. Dapatkan akses ke solusi teknologi terkini tanpa menguras anggaran, sambil menikmati dukungan penuh dari tim ahli kami!</p>
      <a href="#" class="btn btn-xl btn-light">Ajukan Kemitraan Sekarang Juga!
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
</svg>
</a>
    </div>
  </div>
</div>

</div>

<div class="bg-dark py-vh-5">
<div class="container px-vw-5">
  <div class="row d-flex gx-5 align-items-center">
    <div class="col-12 col-lg-6">
      <div class="rounded-5 bg-black p-5 shadow" data-aos="zoom-in-right">
        <div class="fs-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


        </div>
        <p class="text-secondary lead">"Bermitra dengan MitraRynnxz telah mengubah cara kami beroperasi. Tim mereka selalu siap membantu dan memberikan solusi IT yang inovatif. Kami sangat puas dengan hasilnya!"</p>
        <div class="d-flex justify-content-start align-items-center border-top border-secondary pt-3">
          <img src="img/webp/person14.webp" width="96" height="96" class="rounded-circle me-3" alt="a nice person" data-aos="fade" loading="lazy">
          <div>
            <span class="h6 fw-5">Mulyono</span><br>
            <small class="text-secondary">COO, PT Omke gams</small>
          </div>
        </div>
      </div>
      <div class="rounded-5 bg-black p-5 shadow mt-5" data-aos="zoom-in-right">
        <div class="fs-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-half" viewBox="0 0 16 16">
  <path d="M5.354 5.119 7.538.792A.516.516 0 0 1 8 .5c.183 0 .366.097.465.292l2.184 4.327 4.898.696A.537.537 0 0 1 16 6.32a.548.548 0 0 1-.17.445l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256a.52.52 0 0 1-.146.05c-.342.06-.668-.254-.6-.642l.83-4.73L.173 6.765a.55.55 0 0 1-.172-.403.58.58 0 0 1 .085-.302.513.513 0 0 1 .37-.245l4.898-.696zM8 12.027a.5.5 0 0 1 .232.056l3.686 1.894-.694-3.957a.565.565 0 0 1 .162-.505l2.907-2.77-4.052-.576a.525.525 0 0 1-.393-.288L8.001 2.223 8 2.226v9.8z"/>
</svg>

        </div>
        <p class="text-secondary lead">"Kemitraan kami dengan MitraRynnxz telah meningkatkan efisiensi dan produktivitas tim kami secara signifikan. Mereka benar-benar memahami kebutuhan kami dan memberikan dukungan yang luar biasa!"</p>
        <div class="d-flex justify-content-start align-items-center border-top border-secondary pt-3">
          <img src="img/webp/person13.webp" width="96" height="96" class="rounded-circle me-3" alt="a nice person" data-aos="fade" loading="lazy">
          <div>
            <span class="h6 fw-5">Rehan Bakar</span><br>
            <small class="text-secondary">CIO, Bakar-bakar Inc.</small>
          </div>
        </div>
      </div>

    </div>
    <div class="col-12 col-lg-6">
      <div class="p-5 pt-0" data-aos="fade">
        <span class="h5 text-secondary fw-lighter">Apa yang mereka katakan</span>
        <h2 class="display-4">Siapa bilang kemitraan itu rugi?</h2>
      </div>
      <div class="rounded-5 bg-black p-5 shadow mt-5 gradient" data-aos="zoom-in-left">
        <div class="fs-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16">
  <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
</svg>


        </div>
        <p class="lead">"Bermitra dengan MitraRynnxz telah mengubah cara kami beroperasi. Tim mereka selalu siap membantu dan memberikan solusi IT yang inovatif. Kami sangat puas dengan hasilnya!"</p>
        <div class="d-flex justify-content-start align-items-center border-top pt-3">
          <img src="img/webp/person16.webp" width="96" height="96" class="rounded-circle me-3" alt="a nice person" data-aos="fade" loading="lazy">
          <div>
            <span class="h6 fw-5">Ryan Motherboard</span><br>
            <small>COO, Pisi Sultan Corp.</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>


<div class="container py-vh-5 d-flex justify-content-center align-items-center" style="height: 100vh;">
  <form enctype="multipart/form-data" method="post" action="">
    <h3 class="text-center mb-3">Kirim pengajuan kemitraan sekarang!</h3>

    <div class="row">
      <!-- Kolom Kiri -->
      <div class="col-md-6">
        <div class="mb-3">
          <label for="companyName" class="form-label">Nama Perusahaan</label>
          <input type="text" class="form-control bg-gray-800 border-dark" id="companyName" name="companyName" required>
        </div>
        <div class="mb-3">
          <label for="contactPerson" class="form-label">Nama Kontak</label>
          <input type="text" class="form-control bg-gray-800 border-dark" id="contactPerson" name="contactPerson" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control bg-gray-800 border-dark" id="email" name="email" required>
        </div>
      </div>
      
      <!-- Kolom Kanan -->
      <div class="col-md-6">
        <div class="mb-3">
          <label for="phone" class="form-label">Nomor Telepon</label>
          <input type="text" class="form-control bg-gray-800 border-dark" id="phone" name="phone" required>
        </div>
        <div class="mb-3">
          <label for="address" class="form-label">Alamat</label>
          <textarea class="form-control bg-gray-800 border-dark" id="address" name="address" rows="3" required></textarea>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Kolom Kiri -->
      <div class="col-md-6">
        <div class="mb-3">
          <label for="uploadImages" class="form-label">Upload Gambar (Multiple)</label>
          <input type="file" class="form-control" id="uploadImages" name="uploadImages[]" multiple accept="image/*">
        </div>
      </div>

      <!-- Kolom Kanan -->
      <div class="col-md-6">
        <div class="mb-3">
          <label for="uploadPDF" class="form-label">Upload Surat (PDF)</label>
          <input type="file" class="form-control" id="uploadPDF" name="uploadPDF" accept="application/pdf" required>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
      <button type="submit" class="btn btn-white btn-xl mb-4">Kirim Permintaan Kemitraan</button>
    </div>
  </form>
</div>


    </main>

    <footer class="bg-black border-top border-dark">
  <div class="container py-vh-4 text-secondary fw-lighter">
    <div class="row">
      <div class="col-12 col-lg-5 py-4 text-center text-lg-start">
            <a class="navbar-brand pe-md-4 fs-4 col-12 col-md-auto text-center" href="index.html">
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-stack" viewBox="0 0 16 16">
    <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.598.598 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.598.598 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.598.598 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535L7.733.063z"/>
    <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.598.598 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.659z"/>
  </svg>
  <span class="ms-md-1 mt-1 fw-bolder me-md-5">MitraRynnxz</span>
</a>

      </div>
      <div class="col border-end border-dark">
        <span class="h6">Company</span>
<ul class="nav flex-column">
  <li class="nav-item">
    <a href="#" class="link-fancy link-fancy-light">About us</a>
  </li>
  <li class="nav-item">
    <a href="#" class="link-fancy link-fancy-light">Legal</a>
  </li>

  <li class="nav-item">
    <a href="#" class="link-fancy link-fancy-light">Career</a>
  </li>
  <li class="nav-item">
    <a href="#" class="link-fancy link-fancy-light">Contact</a>
  </li>
</ul>
      </div>
      <div class="col border-end border-dark">
                <span class="h6">Services</span>
                <ul class="nav flex-column">
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Pricing</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Products</a>
                  </li>

                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Customers</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Portfolio</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Success Stories</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">More</a>
                  </li>
                </ul>
      </div>
      <div class="col">
                <span class="h6">Support</span>
                <ul class="nav flex-column">
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">About us</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Legal</a>
                  </li>

                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Career</a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="link-fancy link-fancy-light">Contact</a>
                  </li>
                </ul>
      </div>
    </div>
  </div>
  <div class="container text-center small py-vh-2 border-top border-dark">Made by
    <a href="#" class="link-fancy link-fancy-light" target="_blank">zzz</a>
  </div>
</footer>







<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/aos.js"></script>
<script>
AOS.init({
 duration: 800, // values from 0 to 3000, with step 50ms
});
</script>
<script>
  let scrollpos = window.scrollY
  const header = document.querySelector(".navbar")
  const header_height = header.offsetHeight

  const add_class_on_scroll = () => header.classList.add("scrolled", "shadow-sm")
  const remove_class_on_scroll = () => header.classList.remove("scrolled", "shadow-sm")

  window.addEventListener('scroll', function() {
    scrollpos = window.scrollY;

    if (scrollpos >= header_height) { add_class_on_scroll() }
    else { remove_class_on_scroll() }

    console.log(scrollpos)
  })
</script>


  </body>
</html>
