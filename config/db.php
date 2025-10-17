<?php
function db_connect(): ?mysqli
{
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'draft-shop';

    $mysqli = new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_errno) {
        return null;
    }
    return $mysqli;
}
