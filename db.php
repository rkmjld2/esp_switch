<?php

/*
 * TiDB Cloud database connection
 *
 * IMPORTANT:
 * Do NOT put the actual password in this file.
 * Set DB_HOST, DB_PORT, DB_USER, DB_PASSWORD and DB_NAME
 * in Render Environment Variables.
 */

$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: "4000";
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$dbname = getenv("DB_NAME");


/*
 * Check required environment variables.
 */
if (!$host || !$user || !$password || !$dbname) {

    http_response_code(500);

    die("Database environment variables are missing.");
}


/*
 * Initialize MySQLi.
 */
$conn = mysqli_init();

if (!$conn) {

    http_response_code(500);

    die("mysqli_init() failed.");
}


/*
 * Enable TLS/SSL for TiDB Cloud.
 */
mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);


/*
 * Connect to TiDB Cloud.
 *
 * TiDB Cloud SQL port is normally 4000.
 */
if (!mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $dbname,
    intval($port),
    NULL,
    MYSQLI_CLIENT_SSL
)) {

    http_response_code(500);

    die("Database connection failed: " . mysqli_connect_error());
}


/*
 * Use UTF-8.
 */
if (!$conn->set_charset("utf8mb4")) {

    http_response_code(500);

    die("Failed to set database character set.");
}

?>
