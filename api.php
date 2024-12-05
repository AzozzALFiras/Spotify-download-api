<?php
// Developer: Azozz ALFiras | Date: 2022/09/02

require_once __DIR__ . '/vendor/autoload.php'; // Load Guzzle via Composer

use GuzzleHttp\Client;

$apiKey = 'your_api_key_here'; // Replace with your actual API key
$videoId = 'your_video_id_here'; // Replace with your actual video ID

// Build the download URL
$fullUrl = "https://download.azozzalfiras.dev/api/v1/spotify";

if ($fullUrl) {
    // Initialize Guzzle HTTP client
    $client = new Client();

    try {
        // Send POST request
        $response = $client->post($fullUrl, [
            'form_params' => [
                'url' => "https://www.youtube.com/watch?v=$videoId",
                'key' => $apiKey,
            ],
        ]);

        // Get the response body and decode JSON
        $data = json_decode($response->getBody(), true);

        // Extract individual fields
        $url = $data['url'] ?? null;
        $title = $data['title'] ?? null;
        $thumbnail = $data['thumbnail'] ?? null;
        $duration = $data['duration'] ?? null;
        $source = $data['source'] ?? null;

        // Extract media information
        $medias = $data['medias'] ?? [];
        $urlAudio = null;

        foreach ($medias as $media) {
            // Check if the media has the desired extension (e.g., m4a)
            if ($media['extension'] === 'm4a') {
                $urlAudio = $media['url'];
                break;
            }
        }

        // Prepare and output JSON response
        $responseJson = [
            'audio_url' => $urlAudio,
        ];

        header('Content-Type: application/json');
        echo json_encode($responseJson);

    } catch (Exception $e) {
        // Handle exceptions (e.g., request errors)
        echo json_encode([
            'error' => $e->getMessage(),
        ]);
    }
}
?>
