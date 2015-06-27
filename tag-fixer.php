<?php

require_once("tag-finder.php");

/*****
 * Checks for a protocol prefix
 * @param string $url The url to check for a protocol
 * @return boolean Whether or not the URL has a protocol
 *****/
function hasPrefixProtocol($url=null) {
    if ($url == null) { return false; }
    $colon = strpos($url, ":");
    $slash = strpos($url, "/");
    if (is_numeric($colon) && is_numeric($slash)) {
        if ($colon < $slash) { return true; }
    }
    return false;
}

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
    if (hasPrefixProtocol($url) == true) { return $url; }
    
    // Check to see if removes a directory level. Assumes the proper path is
    // passed in the $domain parameter.
    if (substr($url, 0, 3) == "../") {
        if (substr($domain, -1, 1) == "/") {
            $url = substr($url, 3);
        } else {
            $url = substr($url, 2);
        }
    }
    
    // Start building the url
    $retval = $domain . $url;
    
    // Check to see if the $retval has a protocol
    if (hasPrefixProtocol($retval) == true) { return $retval; }
    
    // Make sure the protocol is in lowercase
    $protocol = strtolower($protocol);
    
    // If it has no protocol prefix, add one
    switch($protocol) {
        case "ftp":
        case "http":
        case "https":
        case "telnet":
            $retval = $protocol . "://" . $retval;
            break;
        default:
            $retval = "http://" . $retval;
    }
    
    // Finally, return the new domain
    return $retval;
}

function findBrokenLinks($html=null) {
    
}

?>