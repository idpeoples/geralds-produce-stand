<?php

    $configFileString = file_get_contents("./configuration/configuration.json");
    $config = json_decode($configFileString, true);
    $credentials = $config['database']['credentials'];
    $schema = $config['database']['schema'];

    try {

        $dbconn = new PDO('mysql:host=localhost;dbname='.$credentials['name'], $credentials['username'], $credentials['password']);
        $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {

        echo "<br>Connection failure: ".$e->getMessage();

    }

    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    foreach (array_keys($_POST) as $key) {

        if (strpos($key, 'change') != false) {

            if ($_POST[$key] != "") {

                $produce_id = strtok($key, '_');
                $sign = $_POST[$produce_id.'_buy_or_sell'];
                $current = intval($_POST[$produce_id.'_current']);
                $change = intval($_POST[$produce_id.'_change']);

                $newValue = 0;

                if ($sign === 'buy') {

                    $newValue = $current + $change;

                } elseif ($sign === 'sell') {
                    $newValue = ($current > $change) ? ($current - $change) : 0;

                    $newValue = ($current > $change) ? ($current - $change) : 0;
                }

                $statement = $dbconn->prepare('update '.$schema['table name'].' set '.$schema['count'].' = :newValue where '.$schema['id'].' = :produce_id');
                $statement->bindParam(':newValue', $newValue);
                $statement->bindParam(':produce_id', $produce_id);
                $statement->execute();

            }

        }

    }

    $queryString = 'select * from '.$schema['table name'].' where '.$schema['count'].' = 0';
    $queryStatement = $dbconn->query($queryString);
    $results = $queryStatement->execute();

    if ($results) {

        $resultsArray = $queryStatement->fetchAll();

        if (sizeof($resultsArray) > 0) {

            $message = 'Hello '.$config['contact']['name'].','."\r\n\r\n".'This message is to inform you that the following items have run out:'."\r\n";
            $comma = '';

            foreach ($resultsArray as $produce) {

                $message .= $comma.$produce[$schema['display name']]."\r\n";
                $comma = ', ';

            }

            $headers = array('From' => $config['contact']['from email'],
                             'Reply-To' => $config['contact']['from email'],
                             'X-Mailer' => 'PHP/' . phpversion());
            $mail_result = mail($config['contact']['to email'], 'Missing Items', $message, $headers, '-f'.$config['contact']['from email']);

        }

    }

    function generateUpdate($schema, $produce) {

        $update = '<div class="produce-update">';

        foreach ($schema as $section) {

            if ($section === $schema['display name']
                || $section === $schema['count']) {

                $update .= generateUpdateDiv($section, $produce);

            }

        }

        $update .= '<div><select name="'.$produce[$schema['id']].'_buy_or_sell"><option value="buy">Bought</option><option value="sell">Sold</option></select></div>';

        $update .= '<div><input type="number" name="'.$produce[$schema['id']].'_change" min="0" /></div>';

        $update .= '<input type="hidden" name="'.$produce[$schema['id']].'_current" value='.$produce[$schema['count']].'>';

        $update .= '</div>';

        return $update;

    }

    function generateUpdateDiv($section, $produce) {

        return '<div class='.$section.'>'.$produce[$section].'</div>';

    }

?>

<html>

    <head>

        <link rel="stylesheet" href="update.css">
        <link rel="icon" href="images/apple.jpg">
        <title>Updates - Gerald's Produce Stand</title>

    </head>

    <body>

        <div id="page-title" class="content">

            <h1>Gerald's Produce Stand</h1>

        </div>

        <div id="navbar" class="content">

            <table>

                <tr>

                    <td><a href="/geralds-produce-stand/"><div><h2>Home</h2></div></a></td>
                    <td><a href="stock.php"><div><h2>Stock</h2></div></a></td>
                    <td><a href="update.php"><div><h2>Updates</h2></div></a></td>

                </tr>

            </table>

        </div>

        <form method="POST">

            <div id="produce-update-container" class="content">

                <?php

                    echo '<div class="produce-update"><div>Name</div><div>Current Count</div><div>Action</div><div>Amount</div></div>';
                    $queryString = 'select * from '.$schema['table name'];
                    $queryStatement = $dbconn->query($queryString);
                    $results = $queryStatement->execute();

                    if ($results) {

                        $result_array = $queryStatement->fetchAll();

                        foreach ($result_array as $row) {

                            echo generateUpdate($schema, $row);

                        }

                    }

                ?>

            </div>

            <input type="submit" value="Update Product(s)" />

        </form>

    </body>

</html>
