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

    // check the POST data, if it exists, then we need to do an insert to display it on the page immediately
    if (isset($_POST['basic_name']) && isset($_POST['display_name']) && isset($_POST['initial_count'])) {

        $statement = $dbconn->prepare('insert into '.$schema['table name'].' values (NULL, :basic_name, :display_name, :count)');
        $basicName = sanitizeInput($_POST['basic_name']);
        $displayName = sanitizeInput($_POST['display_name']);
        $initialCount = sanitizeInput($_POST['initial_count']);
        $statement->bindParam(':basic_name', $basicName);
        $statement->bindParam(':display_name', $displayName);
        $statement->bindParam(':count', $initialCount);
        $statement->execute();

    }

    // Generate a listing div from the parameters $produce (the actual data) and
    // $schema, which holds the indices for $produce as well as the column names to use
    function generateListing($schema, $produce) {

        $listing = '<div class="produce-listing">';

        foreach ($schema as $section) {

            if ($section === $schema['id']
                || $section === $schema['display name']
                || $section === $schema['count']) {

                $listing .= generateListingDiv($section, $produce);

            }

        }

        $listing .= "</div>";

        return $listing;

    }

    // Used by generateListing to create the inner divs easily
    function generateListingDiv($section, $produce) {

        return '<div class='.$section.'>'.$produce[$section].'</div>';

    }

    // used to prevent HTML/Script injection attacks
    // replaces dangerous characters with HTML equivalents
    function sanitizeInput($text) {

        $replacements = array('<' => '&lt;', '>' => '&gt;', '&' => '&amp;', "\n" => '<br />');

        foreach (array_keys($replacements) as $thorn) {

            $text = str_replace($thorn, $replacements[$thorn], $text);

        }

        return $text;

    }

?>

<!DOCTYPE html>
<html>

    <head>

        <link rel="stylesheet" href="style/stock.css">
        <link rel="icon" href="images/apple.jpg">
        <title>Stock - Gerald's Produce Stand</title>

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

        <div id="produce-listing-container" class="content">

            <?php

                // This is a bit of a trick I came up with, it uses the generative functions to generate the table header
                $listing_header = array($schema['id']=>'ID', $schema['display name']=>'Name', $schema['count']=>'Count');
                echo generateListing($schema, $listing_header);

                // This is a pre-written query with no user input, therefore no need to used a prepared statement
                $queryString = "select * from ".$schema['table name'];
                $queryStatement = $dbconn->query($queryString);
                $results = $queryStatement->execute();

                if ($results) {

                    $result_array = $queryStatement->fetchAll();

                    foreach ($result_array as $row) {

                        echo generateListing($schema, $row);

                    }

                }

            ?>

        </div>

        <div id="form-container" class="content">

            <form method="POST">

                <input type="text" name="basic name" placeholder="Basic Name ('orange')" required />
                <input type="text" name="display name" placeholder="Display Name ('Oranges')" required />
                <input type="number" name="initial count" placeholder="Initial Count" min="1" />
                <input type="submit" value="Add Product"/>

            </form>

        </div>

    </body>

</html>
