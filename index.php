<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chemistry</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body data-bs-theme="dark">
    <div class="container mt-3">
        <div class="search-container">
            <form method="POST">
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search for a compound..." name="search_term"/>
                    <button type="submit" class="search-icon" name="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                            <path d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zM9.5 14C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                        </svg>
                    </button>
                </div>
            </form>
            <div class="glow"></div>
        </div>
        
        <div class="results-container mt-4">
        <?php
            if (isset($_POST['submit']) && !empty($_POST['search_term'])) {
                $search_term = urlencode($_POST['search_term']);
                $api_url = "https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/name/{$search_term}/JSON";
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
            
                if (curl_errno($ch)) {
                    echo 'cURL error: ' . curl_error($ch);
                } else {
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($http_code == 200) {
                        $data = json_decode($response, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            if (!empty($data['PC_Compounds'][0])) {
                                $compound = $data['PC_Compounds'][0];
                                echo "<h2>Compound Details for " . htmlspecialchars($search_term) . ":</h2>";
                                $iupac_names = [];
                                foreach ($compound['props'] as $prop) {
                                    if ($prop['urn']['label'] == "IUPAC Name") {
                                        $iupac_names[] = $prop['value']['sval'];
                                    }
                                }
                                $iupac_names = array_unique($iupac_names);
                                if (!empty($iupac_names)) {
                                    echo "<h3>IUPAC Names:</h3><ul>";
                                    foreach ($iupac_names as $iupac_name) {
                                        echo "<li>" . $iupac_name . "</li>";
                                    }
                                    echo "</ul>";
                                }
                                foreach ($compound['props'] as $prop) {
                                    if ($prop['urn']['label'] == "Molecular Formula") {
                                        echo "<p><strong>Molecular Formula:</strong> " . $prop['value']['sval'] . "</p>";
                                    }
                                }
                                foreach ($compound['props'] as $prop) {
                                    if ($prop['urn']['label'] == "Molecular Weight") {
                                        echo "<p><strong>Molecular Weight:</strong> " . $prop['value']['sval'] . " g/mol</p>";
                                    }
                                }
                                echo "<h3>Other Properties:</h3>";
                                if (!empty($compound['props'])) {
                                    echo "<ul>";
                                    foreach ($compound['props'] as $prop) {
                                        if (isset($prop['value']['sval'])) {
                                            if($prop['urn']['label'] !== "IUPAC Name"){
                                                echo "<li>" . $prop['urn']['label'] . ": " . $prop['value']['sval'] . "</li>";
                                            
                                            }
                                        } elseif (isset($prop['value']['fval'])) {
                                            echo "<li>" . $prop['urn']['label'] . ": " . $prop['value']['fval'] . "</li>";
                                        }
                                    }
                                    echo "</ul>";
                                } else {
                                    echo "<p>No additional data available.</p>";
                                }
                            } else {
                                echo "No data found for the searched compound.";
                            }
                        } else {
                            echo "Error decoding JSON: " . json_last_error_msg();
                        }
                    } else {
                        echo "API request failed with response code: " . $http_code;
                    }
                }
                curl_close($ch);
            }
        ?>

        </div>
    </div>
</body>
</html>
