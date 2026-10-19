<?php
$conn = new mysqli("localhost", "root", "", "techpulse");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$10$hSF2izQHZ3i63sHHcrcXzOJOucVoVCFMiOHsaRduAQCRSW6iaW2S6';