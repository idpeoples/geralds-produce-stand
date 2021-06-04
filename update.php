<?php

    // extract the configuration data from the JSON file and separate it into two objects for ease of use
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

    // by iterating through the keys in $_POST, we don't need to check if the values are set
    foreach (array_keys($_POST) as $key) {

        // $_POST includes several keys which we don't need
        if (strpos($key, 'change') != false) {

            // verify that the key has data
            if ($_POST[$key] != "") {

                // extract the other data from $_POST by using the produce_id stored in the key name
                $produce_id = strtok($key, '_');
                $sign = $_POST[$produce_id.'_buy_or_sell'];
                $current = intval($_POST[$produce_id.'_current']);
                $change = intval($_POST[$produce_id.'_change']);

                $newValue = 0;

                if ($sign === 'buy') {

                    $newValue = $current + $change;

                } elseif ($sign === 'sell') {

                    // we should not be allowed to sell more product that we have
                    $newValue = ($current > $change) ? ($current - $change) : 0;

                }

                $statement = $dbconn->prepare('update '.$schema['table name'].' set '.$schema['count'].' = :newValue where '.$schema['id'].' = :produce_id');
                $statement->bindParam(':newValue', $newValue);
                $statement->bindParam(':produce_id', $produce_id);
                $statement->execute();

            }

        }

    }

    // PHP PDO module doesn't allow table names and others to be preparation parameters, so sometimes we have to use string concatenation
    // these concatenations do not contain user input so there is no risk of SQL injection
    $queryString = 'select * from '.$schema['table name'].' where '.$schema['count'].' = 0';
    $queryStatement = $dbconn->query($queryString);
    $results = $queryStatement->execute();

    if ($results) {

        $resultsArray = $queryStatement->fetchAll();

        if (sizeof($resultsArray) > 0) {

            // this trick with the comma variable is from my competitive programming days
            // it ensures that commas are only placed where expected, even if it does result in extra assignments
            $message = 'Hello '.$config['contact']['name'].','."\r\n\r\n".'This message is to inform you that the following items have run out:'."\r\n";
            $comma = '';

            foreach ($resultsArray as $produce) {

                $message .= $comma.$produce[$schema['display name']]."\r\n";
                $comma = ', ';

            }

            // I had an issue with using the PHP mail() function, but it turned out that my ISP blocks the port mail() uses
            // tested to work on the live server though
            // note that the 'from' and '-f' fields are required for the message to be accepted by the mail program
            $headers = array('From' => $config['contact']['from email'],
                             'Reply-To' => $config['contact']['from email'],
                             'X-Mailer' => 'PHP/' . phpversion());
            $mail_result = mail($config['contact']['to email'], 'Missing Items', $message, $headers, '-f'.$config['contact']['from email']);

        }

    }

    // as in stock.php these functions are used to generate the divs of the table automatically
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

        // I include a hidden parameter here so that we can verify the current value and update correctly
        // this is also used to generate the email
        $update .= '<input type="hidden" name="'.$produce[$schema['id']].'_current" value='.$produce[$schema['count']].'>';

        $update .= '</div>';

        return $update;

    }

    function generateUpdateDiv($section, $produce) {

        return '<div class='.$section.'>'.$produce[$section].'</div>';

    }

?>

<!DOCTYPE html>
<html>

    <head>

        <link rel="stylesheet" href="style/update.css">
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

                    // I couldn't use the same header generation trick from stock.php because the columns are checked more strictly and won't allow arbitrary headers
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
