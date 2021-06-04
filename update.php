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

                $product_id = strtok($key, '_');
                $sign = $_POST[$product_id.'_buy_or_sell'];
                $current = intval($_POST[$product_id.'_current']);
                $change = intval($_POST[$product_id.'_change']);

                $newValue = 0;
                
                if ($sign === 'buy') {

                    $newValue = $current + $change;

                } else if ($sign === 'sell') {

                    $newValue = ($current > $change) ? ($current - $change) : 0;

                }

                $statement = $dbconn->prepare('update '.$schema['table name'].' set '.$schema['count'].' = :newValue where '.$schema['id'].' = :product_id');
                $statement->bindParam(':newValue', $newValue);
                $statement->bindParam(':product_id', $product_id);
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

            foreach ($resultsArray as $product) {

                $message .= $comma.$product[$schema['display name']]."\r\n";
                $comma = ', ';

            }

            $headers = array('From' => $config['contact']['from email'],
                             'Reply-To' => $config['contact']['from email'],
                             'X-Mailer' => 'PHP/' . phpversion());
            $mail_result = mail($config['contact']['to email'], 'Missing Items', $message, $headers, '-f'.$config['contact']['from email']);

        }

    }

    function generateUpdate($schema, $product) {

        $update = '<div class="product_update">';

        foreach ($schema as $section) {

            if ($section === $schema['display name']
                || $section === $schema['count']) {

                $update .= generateUpdateDiv($section, $product);

            }

        }

        $update .= '<div><select name="'.$product[$schema['id']].'_buy_or_sell"><option value="buy">Bought</option><option value="sell">Sold</option></select></div>';

        $update .= '<div><input type="number" name="'.$product[$schema['id']].'_change" min="0" /></div>';

        $update .= '<input type="hidden" name="'.$product[$schema['id']].'_current" value='.$product[$schema['count']].'>';

        return $update;

    }

    function generateUpdateDiv($section, $product) {

        return '<div class='.$section.'>'.$product[$section].'</div>';

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

                    <td><a href="/"><div><h2>Home</h2></div></a></td>
                    <td><a href="stock.php"><div><h2>Stock</h2></div></a></td>
                    <td><a href="update.php"><div><h2>Updates</h2></div></a></td>

                </tr>

            </table>

        </div>

        <div id="produce-update-container" class="content">

            <form method="POST">

                <?php

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

                <input type="submit" value="Update Product(s)" />

            </form>

        </div>

    </body>

</html>
