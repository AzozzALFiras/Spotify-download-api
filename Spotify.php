<?php

// Developer: Azozz ALFiras | Date: 2022/09/02

class Spotify
{
    private $trackId;

    public function __construct($trackId) {
        $this->trackId = $this->extractTrackId($trackId);
    }

    // Function to extract track ID from the given input
    private function extractTrackId($trackId) {
        preg_match('/spotify:track:([a-zA-Z0-9]+)/', $trackId, $matches);
        return isset($matches[1]) ? $matches[1] : $trackId;
    }

    // Function to get content from a URL using cURL
    private function getURLContent() {
        $url = "https://open.spotify.com/track/" . $this->trackId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // Function to get track details from Spotify URL
    public function getTrackDetails() {
        $htmlContent = $this->getURLContent();

        $dom = new DOMDocument;
        @$dom->loadHTML($htmlContent);

        $xpath = new DOMXPath($dom);

        // Extracting the title from the <title> tag
        $titleNode = $xpath->query('//title')->item(0);
        $title = $titleNode ? $titleNode->textContent : 'No title found';

        // Extracting the thumbnail
        $thumbNode = $xpath->query('//meta[@property="og:image"]')->item(0);
        $thumbnail = $thumbNode ? $thumbNode->getAttribute('content') : 'No thumbnail found';

        // Setting the header and returning the JSON response
        header('Content-Type: application/json');
        return $title;
    }
}


?>
