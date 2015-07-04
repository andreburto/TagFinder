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
 * Check a link and see if it works
 * @param string $url This is the url to check
 * @return int Returns a status code
 *****/
function checkLink($url=null) {
    if ($url == null) { return false; }
    // Initialize the curl call
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    // Execute the call and close the call
    curl_exec($ch);
    $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    curl_close($ch);
    // Return the code as an integer value
    return $status;
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

/*****
 * Finds the broken links in a block of HTML.
 * @param string $html Block of HTML code
 * @param string $domain The base domain to use
 * @param string $protocol The protocol you want to check
 * @param string $attr Defaults to href
 * @return array Returns an array of the url and status code for it
 *****/
function findBrokenLinks($html=null, $domain=null, $protocol=null, $attr="href") {
    if ($html == null) { return false; }
    // Convert the HTML string into an array of elements
    $doc = parseHtml($html);
    if ($doc == false) { return false; }
    // Confirm the attribute type
    if ($attr != "href" && $attr != "src") { return false; }
    // Fish the opening link tags from array
    $tag = ($attr=="href"?'a':'img');
    $type = ($attr=="href"?'B':'S');
    $a = findSpecificTag($doc, $tag, $type);
    
    if ($a == false || count($a) == 0) { return false; }
    
    $links = array();
    foreach($a as $el) {
        $url = getAttribute($el, $attr);
        $code = 999;
        
        // Add a domain if you can or need to do so
        if ($domain != null && hasPrefixProtocol($url) == false) {
            $url = fixLinkDomain($url, $domain, $protocol);
        }
        
        // If there's a protocol prefix
        if (hasPrefixProtocol($url) == true) { $code = checkLink($url); }
        
        // Add an entry to the array
        $links[] = array('URL' => $url, 'CODE' => $code);
    }
    
    // No links
    if (count($links) == 0) { return false; }
    // Links
    return $links;
}

/*****
 * Builds the string for a tag out of an element array.
 * @param array $element The element in array format
 * @return string The node in string format
 *****/
function makeTag($element=null) {
    if (is_array($element) == false) { return false; }
    // If it's text, return the string
    if ($element['TAG'] == false) { return $element['TXT']; }
    // If it's an End tag, just return that
    if ($element['TYPE'] == 'E') { return sprintf("</%s>", $element['TAG']); }
    // An array to push bits
    $parts = array($element['TAG']);
    // Collect attributes
    if (isset($element['ATTR']) == true) {
        $atts = array();
        foreach($element['ATTR'] as $att) {
            $parts[] = sprintf("%s=\"%s\"", $att['key'], $att['val']);
        }
    }
    // Start the tag
    $html = "<" . implode(" ", $parts);
    if ($element['TYPE'] == 'S') { $html .= " /"; }
    // End the tag
    $html .= ">";
    // Return the HTML as a string
    return $html;
}

?>