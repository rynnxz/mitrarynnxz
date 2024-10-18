<?php
      // Koneksi ke database
  $host = 'localhost';
  $user = 'root';
  $password = '';
  $dbname = 'kemitraan-app'; // Ganti dengan nama database kamu
  $conn = new mysqli($host, $user, $password, $dbname);
  
  if ($conn->connect_error) {
      die("Koneksi gagal: " . $conn->connect_error);
  }
?>