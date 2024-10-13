<?php
// Include Composer's autoloader
require 'vendor/autoload.php';

use GuzzleHttp\Client;

function fetchWikiSummary($query) {
    $client = new Client();
    try {
        // Make a GET request to the Wikipedia API
        $response = $client->request('GET', 'https://en.wikipedia.org/w/api.php', [
            'query' => [
                'action' => 'query',
                'format' => 'json',
                'prop' => 'extracts',
                'exintro' => true,
                'explaintext' => true,
                'titles' => $query,
            ],
            'verify' => false
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        if (isset($data['query']['pages']) && is_array($data['query']['pages'])) {
            $page = array_shift($data['query']['pages']);
            $title = $page['title'] ?? 'No title found';
            $extract = $page['extract'] ?? 'No content found';
        } else {
            $title = 'Error';
            $extract = 'No content found for the provided query.';
        }

        return [
            'title' => $title,
            'summary' => $extract
        ];
    } catch (Exception $e) {
        return [
            'title' => 'Error',
            'summary' => 'An error occurred: ' . $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    $query = trim($_POST['query']);
    $response = fetchWikiSummary($query);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wikipedia Bot</title>
    <script>
        async function fetchWikiSummary(event) {
            event.preventDefault();
            const query = document.getElementById('query').value.trim();
            if (query !== "") {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `query=${query}`
                });
                const data = await response.json();
                document.getElementById('result').innerText = `${data.title}\n${data.summary}`;
            } else {
                document.getElementById('result').innerText = "Please enter a valid query.";
            }
        }
    </script>
</head>
<body>
    <h1>Wikipedia Bot</h1>
    <form onsubmit="fetchWikiSummary(event)">
        <label for="query">Search Wikipedia:</label>
        <input type="text" id="query" name="query" required>
        <button type="submit">Search</button>
    </form>
    <pre id="result"></pre>
</body>
</html>
