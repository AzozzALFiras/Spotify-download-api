<?php

// Developer: Azozz ALFiras | Date: 2022/09/02


// Include required files
require_once __DIR__ . '/Spotify.php';
require_once __DIR__ . '/YouTubeSearch.php';

// Check if the track ID is provided
if (!isset($_GET["trackID"]) || empty($_GET["trackID"])) {
    die(json_encode(['error' => 'Track ID must be defined']));
}

error_reporting(0); // Suppress warnings for production
$trackID = $_GET["trackID"];

// Fetch track details from Spotify
$spotifyFetcher = new Spotify($trackID);
$trackDetails = $spotifyFetcher->getTrackDetails();

if (!$trackDetails) {
    die(json_encode(['error' => 'Failed to fetch track details from Spotify']));
}

// Fetch YouTube video details
$youtubeFetcher = new YouTubeSearch($trackDetails);
$videoDetails = $youtubeFetcher->search();

if (empty($videoDetails['videoid'])) {
    die(json_encode(['error' => 'Failed to find a YouTube video for the given track']));
}

$videoId = $videoDetails['videoid'];
$apiUrl = "https://aiodown.com/wp-json/aio-dl/api/";
$apiKey = "0dc77e483d3904915d6718f47a14ab1991323f698f97dd1f4a73649035c42a12";

// Prepare the API request URL
$apiFullUrl = $apiUrl . "?url=" . urlencode("https://www.youtube.com/watch?v=$videoId") . "&key=$apiKey";

// Make a GET request to the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiFullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_error($ch)) {
    die(json_encode(['error' => 'cURL Error: ' . curl_error($ch)]));
}

curl_close($ch);

// Decode the API response
$data = json_decode($response, true);

if (empty($data) || !isset($data['medias'])) {
    die(json_encode(['error' => 'Invalid response from the API']));
}

// Extract the audio URL
$audioUrl = null;
foreach ($data['medias'] as $media) {
    if ($media['extension'] === 'm4a') {
        $audioUrl = $media['url'];
        break;
    }
}

if (!$audioUrl) {
    die(json_encode(['error' => 'No M4A audio found in the response']));
}

// Respond with the audio URL
header('Content-Type: application/json');
echo json_encode(['audio_url' => $audioUrl]);
