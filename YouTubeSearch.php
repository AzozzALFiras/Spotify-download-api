<?php

// Developer: Azozz ALFiras | Date: 2022/09/02

class YouTubeSearch {

    private $title;

    public function __construct($title) {
        $this->title = $title;
    }

    private function getVideoIdFromSearch($searchQuery) {
        $searchUrl = "https://www.youtube.com/results?search_query=$searchQuery";
        $searchPage = file_get_contents($searchUrl);
        if (preg_match('/\/watch\?v=([^\"]+)/', $searchPage, $matches)) {
            // Extracting the video ID from the search results
            $videoIdFromSearch = explode("\\", $matches[1])[0]; // Extracting the part before '\\'
            return urldecode($videoIdFromSearch); // Decoding the video ID
        } else {
            return false;
        }
    }

    public function search() {
        $searchQuery = urlencode($this->title);
        $videoIdFromSearch = $this->getVideoIdFromSearch($searchQuery);
        if ($videoIdFromSearch) {
            $videoId = urldecode($videoIdFromSearch); // Decoding the video ID
            $response = array(
                "video_id_from_search" => $videoIdFromSearch,
                "videoid" => $videoId
            );

            // Remove "| Spotify" from the title
            $title = str_replace(" | Spotify", "", $this->title);

            // Search on YouTube with modified title
            $searchQuery = urlencode($title);
            $videoId = $this->getVideoIdFromSearch($searchQuery);
            if ($videoId) {
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

        return $response;
    }
}