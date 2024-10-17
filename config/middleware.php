<?php

function authenticate() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }
}

function authorize($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['peran'] !== $role) {
        header('Location: ../index.php');
        exit();
    }
}