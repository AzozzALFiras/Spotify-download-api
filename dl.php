<?php

// Developer: Azozz ALFiras | Date: 2022/09/02
// Function to extract video ID from YouTube URL

function getYouTubeVideoId($url) {
    $queryString = parse_url($url, PHP_URL_QUERY);
    parse_str($queryString, $params);
    if (isset($params['v'])) {
        return $params['v'];
    } else {
        return false;
    }
}

// Given title
$title = "Atrak - عطرك - song and lyrics by Jalal Al Zain | Spotify";

// Extracting video ID from the search results
$searchQuery = urlencode($title);
$searchUrl = "https://www.youtube.com/results?search_query=$searchQuery";
$searchPage = file_get_contents($searchUrl);
if (preg_match('/\/watch\?v=([^\"]+)/', $searchPage, $matches)) {
    // Extracting the video ID from the search results
    $videoIdFromSearch = explode("\\", $matches[1])[0]; // Extracting the part before '\\'
    $videoId = urldecode($videoIdFromSearch); // Decoding the video ID
    $response = array(
        "video_id_from_search" => $videoIdFromSearch,
        "videoid" => $videoId
    );
    
    // Remove "| Spotify" from the title
    $title = str_replace(" | Spotify", "", $title);
    
    // Search on YouTube with modified title
    $searchQuery = urlencode($title);
    $searchUrl = "https://www.youtube.com/results?search_query=$searchQuery";
    $searchPage = file_get_contents($searchUrl);
    if (preg_match('/\/watch\?v=([^\"]+)/', $searchPage, $matches)) {
        $videoId = urldecode($matches[1]); // Decode the video ID
        $response["modified_video_id_from_search"] = $videoId;
        
        // Extract channel and title from the video
        $videoUrl = "https://www.youtube.com/watch?v=$videoId";
        $videoPage = file_get_contents($videoUrl);
        if (preg_match('/<title>(.*?) - YouTube<\/title>/', $videoPage, $matches)) {
            $videoTitle = $matches[1];
            $response["video_title"] = $videoTitle;
        }
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/', $videoPage, $matches)) {
            $jsonData = json_decode($matches[1], true);
            $channelTitle = $jsonData['itemListElement'][0]['item']['name'];
            $response["channel_title"] = $channelTitle;
            
            // Search for other videos by the same channel with the same title
            $channelQuery = urlencode($channelTitle);
            $titleQuery = urlencode($videoTitle);
            $channelSearchUrl = "https://www.youtube.com/results?search_query=$channelQuery+$titleQuery";
            $response["search_for_other_videos"] = $channelSearchUrl;
        }
    } else {
        $response["error"] = "No video found with the modified title.";
    }
} else {
    $response["error"] = "No video found with the given title.";
}

// Convert response to JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>
