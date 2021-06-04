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

    if (isset($_POST['basic_name']) && isset($_POST['display_name']) && isset($_POST['initial_count'])) {

        $statement = $dbconn->prepare('insert into '.$schema['table name'].' values (NULL, :basic_name, :display_name, :count)');
        $statement->bindParam(':basic_name', $_POST['basic_name']);
        $statement->bindParam(':display_name', $_POST['display_name']);
        $statement->bindParam(':count', $_POST['initial_count']);
        $statement->execute();

    }

    function generateListing($schema, $product) {

        $listing = '<div class="product_listing">';

        foreach ($schema as $section) {

            if ($section === $schema['id']
                || $section === $schema['display name']
                || $section === $schema['count']) {

                $listing .= generateListingDiv($section, $product);

            }

        }

        $listing .= "</div>";

        return $listing;

    }

    function generateListingDiv($section, $product) {

        return '<div class='.$section.'>'.$product[$section].'</div>';

    }

?>

<html>

    <head>

        <link rel="stylesheet" href="stock.css">
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

                    <td><a href="/"><div><h2>Home</h2></div></a></td>
                    <td><a href="stock.php"><div><h2>Stock</h2></div></a></td>
                    <td><a href="update.php"><div><h2>Updates</h2></div></a></td>

                </tr>

            </table>

        </div>

        <div id="product_listing_container" class="content">

            <?php

                $listing_header = array($schema['id']=>'ID', $schema['display name']=>'Name', $schema['count']=>'Count');
                echo generateListing($schema, $listing_header);

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

                <input type="text" name="basic name" placeholder="Basic Name ('orange')" />
                <input type="text" name="display name" placeholder="Display Name ('Oranges')" />
                <input type="number" name="initial count" placeholder="Initial Count" min="1" />
                <input type="submit" value="Add Product"/>

            </form>
        
        </div>

    </body>

</html>
