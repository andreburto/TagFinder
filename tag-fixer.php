<?php

require_once("tag-finder.php");

/*****
 * Adds a domain to a relative link to check if it exists.
 * @param string $url The URL in a link
 * @param string $domain The domain for the link
 * @param string $protocol The protocol to add to the link
 *****/
function fixLinkDomain($url=null, $domain=null, $protocol=null) {
    // Must have these two parameters
    if ($url == null || $domain == null) { return false; }
    
    // Check to see if the url starts with a protocol, and if so return it.
    $colon = strpos($url, ":");
    $slash = strpos($url, "/");
    if (is_numeric($colon) && is_numeric($slash)) {
        if ($color < $slash) {
            return $url;
        }
    }
    
    // 
}

function findBrokenLinks($html=null) {
    
}

?>