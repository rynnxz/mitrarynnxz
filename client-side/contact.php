<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../assets/vendor/autoload.php';  // Pastikan ini sesuai dengan path library PHPMailer

$mail = new PHPMailer(true);

try {
    // Konfigurasi server SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'arrientysmtp@gmail.com';  // Email Gmail kamu
    $mail->Password   = 'tnvo houl uzxh emyx';        // Ganti dengan App Password yang benar
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Penerima
    $mail->setFrom($_POST['email'], $_POST['name']);  // Email pengirim dari form
    $mail->addAddress('akbarriansyah229@gmail.com');    // Email penerima
    $mail->addReplyTo($_POST['email'], $_POST['name']); // Opsi jika ingin email balasan

    // Konten email
    $mail->isHTML(true); // Menggunakan format HTML
    $mail->Subject = $_POST['subject'];
    $mail->Body    = nl2br($_POST['message']); // Mengkonversi newline menjadi <br> untuk HTML
    $mail->AltBody = strip_tags($_POST['message']); // Plain text untuk klien email yang tidak mendukung HTML

    // Kirim email
    $mail->send();
    echo 'Pesan telah dikirim';
} catch (Exception $e) {
    echo "Pesan gagal dikirim. Error: {$mail->ErrorInfo}";
}
?>
