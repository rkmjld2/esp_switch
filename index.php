<?php

require_once "db.php";

/*
 * Process individual pin control
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["pin"]) && isset($_POST["value"])) {

        $pin = $_POST["pin"];
        $value = intval($_POST["value"]);

        $allowed = ["D1","D2","D3","D4","D5","D6","D7","D8"];

        if (in_array($pin, $allowed) && ($value === 0 || $value === 1)) {

            $sql = "UPDATE esp_control SET `$pin` = ? WHERE id = 1";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("i", $value);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /*
     * ALL ON
     */
    if (isset($_POST["all_on"])) {

        $sql = "
            UPDATE esp_control SET
            D1=1,
            D2=1,
            D3=1,
            D4=1,
            D5=1,
            D6=1,
            D7=1,
            D8=1
            WHERE id=1
        ";

        $conn->query($sql);
    }

    /*
     * ALL OFF
     */
    if (isset($_POST["all_off"])) {

        $sql = "
            UPDATE esp_control SET
            D1=0,
            D2=0,
            D3=0,
            D4=0,
            D5=0,
            D6=0,
            D7=0,
            D8=0
            WHERE id=1
        ";

        $conn->query($sql);
    }

    header("Location: index.php");
    exit;
}


/*
 * Read pin states
 */
$result = $conn->query(
    "SELECT D1,D2,D3,D4,D5,D6,D7,D8
     FROM esp_control
     WHERE id=1"
);

$row = $result->fetch_assoc();

if (!$row) {
    $row = [
        "D1"=>0,
        "D2"=>0,
        "D3"=>0,
        "D4"=>0,
        "D5"=>0,
        "D6"=>0,
        "D7"=>0,
        "D8"=>0
    ];
}


/*
 * Controller status
 */
$status_result = $conn->query(
    "SELECT last_seen
     FROM controller_status
     WHERE id=1"
);

$status_row = $status_result->fetch_assoc();

$controller_online = false;

if ($status_row && $status_row["last_seen"]) {

    $last_seen = strtotime($status_row["last_seen"]);

    if ((time() - $last_seen) <= 15) {
        $controller_online = true;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>ESP8266 8 Channel Controller</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 850px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.15);
}

h1 {
    text-align: center;
    margin-bottom: 25px;
}

.controller {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
}

.online {
    color: green;
}

.offline {
    color: red;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.card {
    border: 1px solid #ccc;
    padding: 18px;
    border-radius: 10px;
    text-align: center;
}

.pin {
    font-size: 22px;
    font-weight: bold;
}

.status {
    font-size: 18px;
    margin: 10px;
}

.on {
    color: green;
    font-weight: bold;
}

.off {
    color: red;
    font-weight: bold;
}

button {
    border: none;
    padding: 10px 20px;
    margin: 5px;
    border-radius: 7px;
    font-size: 16px;
    cursor: pointer;
}

.btn-on {
    background: green;
    color: white;
}

.btn-off {
    background: red;
    color: white;
}

.all {
    text-align: center;
    margin-top: 25px;
}

.all-on {
    background: #006400;
    color: white;
}

.all-off {
    background: #8b0000;
    color: white;
}

.refresh {
    text-align: center;
    margin-top: 20px;
    color: #555;
}

@media(max-width:600px) {

    .grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">

<h1>ESP8266 D1-D8 CONTROL</h1>

<div class="controller">

Controller Status:

<?php if ($controller_online): ?>

<span class="online">ONLINE</span>

<?php else: ?>

<span class="offline">OFFLINE</span>

<?php endif; ?>

</div>


<div class="grid">

<?php

for ($i = 1; $i <= 8; $i++):

    $pin = "D" . $i;
    $state = intval($row[$pin]);

?>

<div class="card">

<div class="pin">
    <?php echo $pin; ?>
</div>

<div class="status">

<?php if ($state == 1): ?>

<span class="on">ON</span>

<?php else: ?>

<span class="off">OFF</span>

<?php endif; ?>

</div>


<form method="POST">

<input type="hidden"
       name="pin"
       value="<?php echo $pin; ?>">

<?php if ($state == 0): ?>

<button
    type="submit"
    name="value"
    value="1"
    class="btn-on">
    ON
</button>

<?php else: ?>

<button
    type="submit"
    name="value"
    value="0"
    class="btn-off">
    OFF
</button>

<?php endif; ?>

</form>

</div>

<?php endfor; ?>

</div>


<div class="all">

<form method="POST">

<button
    type="submit"
    name="all_on"
    class="all-on">
    ALL ON
</button>

<button
    type="submit"
    name="all_off"
    class="all-off">
    ALL OFF
</button>

</form>

</div>


<div class="refresh">

Page automatically refreshes every 5 seconds

</div>

</div>


<script>

setTimeout(function() {
    location.reload();
}, 5000);

</script>

</body>
</html>
