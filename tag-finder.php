<?php

/*****
 * DEPRECIATED: Replaced by findNodes function.
 * @param string $str Block of HTML
 * @return array Unparsed elements in an array for further parsing
 *****/
function findTags($str=null) {
    if ($str==null) { return false; }
    $length = strlen($str);
    $temp = ""; $state = 0; $found = array();
    for($cnt=0;$cnt<$length;$cnt++) {
        $chr = substr($str, $cnt, 1);
        if ($chr == '<') {
            if ($state == 0) { $state = 1; }
            $temp .= $chr;
        }
        else if ($chr == '>') {
            if ($state == 1) { $state = 0; }
            $temp .= $chr;
            $found[] = strval($temp);
            $temp = "";
        }
        else {
            if ($state == 1) {
                $temp .= $chr;
            }
        }
    }
    return $found;
}

/*****
 * Find tags and text elements in a block of HTML.
 * @param string $str Block of HTML
 * @return array Tag and text elements in an array for further parsing
 *****/
function findNodes($str=null) {
    if ($str==null) { return false; }
    $length = strlen($str);
    $temp = ""; $state = 0; $type = 0; $found = array(); $cleanup = array();
    // Identify tags and text
    for($cnt=0;$cnt<$length;$cnt++) {
        $chr = substr($str, $cnt, 1);
        if ($chr == '<') {
            // If it's not within a tag
            if ($state == 0) { $state = 1; }
            if (strlen($temp) > 0) {
                $found[] = array('type'=>'TXT', 'string'=>strval($temp));
            }
            $temp = $chr;
        }
        else if ($chr == '>') {
            // If it's within a tag
            if ($state == 1) {
                $state = 0;
                $temp .= $chr;
                $found[] = array('type'=>'TAG', 'string'=>strval($temp));
                $temp = "";
            }
            // If it's outside a tag
            else {
                $temp .= $chr;
            }
        }
        else {
            $temp .= $chr;
        }
    }
    // Merge consecutive text nodes
    $temp = "";
    foreach($found as $node) {
        if ($node['type'] == 'TAG') {
            if (strlen($temp) > 0) {
                $cleanup[] = $temp;
                $temp = "";
            }
            $cleanup[] = $node['string'];
        } else {
            $temp .= $node['string'];
        }
    }
    if (strlen($temp) > 0) { $cleanup[] = $temp; }
    return $cleanup;
}

/*****
 * Parses tag and text nodes, parses attributes, and places them in an array.
 * Type of elements: T = text, S = single, B = beginning tag, E = end tag.
 * @param array $found Array of tag and text nodes from findNodes() function
 * @return array Fully parsed array of elements
 *****/
function parseNodes($found=null) {
    if ($found==null || is_array($found)==false) { return false; }
    $parsed = array();
    foreach($found as $item) {
        $temp = array();
        // Identify text-only nodes
        if (substr($item, 0, 1) != '<' && substr($item, -1, 1) != '>') {
            $temp['TYPE'] = 'T'; $temp['TXT'] = $item; $temp['TAG'] = false;
            $parsed[] = $temp;
            continue;
        }
        // Clean the greater then and less than characters
        if (substr($item, 0, 1)=='<') { $item = substr($item, 1); }
        if (substr($item, -1, 1)=='>') { $item = substr($item, 0, intval(strlen($item)-1)); }
        // Determine what kind of tag it is
        if (substr($item, 0, 1)=='/' ) {
            $temp['TYPE'] = 'E';
            $item = substr($item, 1);
        }
        else if (substr($item, -1, 1)=='/') {
            $temp['TYPE'] = 'S';
            $item = substr($item, 0, intval(strlen($item)-1));
        }
        else { $temp['TYPE'] = 'B'; }
        // If the type is E skip further parsing
        if ($temp['TYPE'] == 'E') {
            $temp['TAG'] = $item;
            $parsed[] = $temp;
            continue;
        }
        // If the type is S or B there's a bit more
        if (preg_match("/=/", $item)==false) {
            $temp['TAG'] = $item;
        }
        else {
            // Define the tag
            $parts = split(" ", $item, 2);
            $temp['TAG'] = $parts[0];
            $temp['ATTR'] = array();
            // Collect up attributes
            $length = strlen($parts[1]);
            $state = 0; $temp_key = ""; $temp_val = ""; $quotmrk = '"';
            for($cnt=0;$cnt<$length;$cnt++) {
                $chr = substr($parts[1],$cnt,1);
                if ($chr=='=') {
                    if ($state==0) {
                        $state = 1;
                        $quotmrk = substr($parts[1],$cnt+1,1);
                    }
                    else { $temp_val .= $chr; }
                }
                else if ($chr==$quotmrk) {
                    if ($state==1) { $state = 2; }
                    else if ($state == 2) {
                        $temp['ATTR'][] = array('key'=>strval($temp_key),
                                                'val'=>strval($temp_val));
                        $temp_key = "";
                        $temp_val = "";
                        $state = 0;
                    }
                }
                else if ($chr==' ') {
                    if ($state == 2) {
                        $temp_val .= $chr;
                    }
                }
                else {
                    if ($state == 0) { $temp_key .= $chr; }
                    else { $temp_val .= $chr; }
                }
            }
        }
        // Add it to the main list
        $parsed[] = $temp;
    }
    return $parsed;
}

/*****
 * Gets an attribute from an element array based on name
 * @param array $element The individual element from an array
 * @param string $aname The name of the attribute
 * @return string The value of the attribute
 *****/
function getAttribute($element=null, $aname=null) {
    if ($element == null || $aname == null) { return false; }
    if (is_array($element) == false) { return false; }
    if (isset($element['ATTR']) == false) { return false; }
    // Loop through and find the attribute
    foreach($element['ATTR'] as $atts) {
        // If the key is found return the value
        if ($atts['key'] == $aname) { return $atts['val']; }
    }
    return false;
}

/*****
 * Parses an HTML block into an array of tag data.
 * @param string $html A block of HTML
 * @return array Tag data
 *****/
function parseHtml($html=null) {
    if ($html==null) { return false; }
    $tags = findNodes($html);
    if (is_array($tags)==false) { return false; }
    return parseNodes($tags);
}

/*****
 * Finds a specfic tag type, such as: a, img, or div.
 * @param string $html A block of HTML
 * @param string $tag The tag to find
 * @param string $type Type of tag: T, E, S, B
 *****/
function findSpecificTag($html=null, $tag=null, $type=null) {
    if ($tag==null||$html==null) { return false; }
    $tags = array();
    if (is_string($html)) { $tags = parseHtml($html); }
    else if (is_array($html)) { $tags = $html;}
    else { return false; }
    
    $tags_filtered = array();
    if (isset($type)) { $type = strtoupper($type); }
    foreach($tags as $t) {
        if (isset($type)) { if ($type != $t['TYPE']) { continue; } }
        if ($tag != $t['TAG']) { continue; }
        $tags_filtered[] = $t;
    }
    if (count($tags_filtered) == 0) { return false; }
    return $tags_filtered;
}

/*****
 * Finds text nodes in the HTML.
 * @param string $html A block of HTML
 * @return array An array of text from the HTML
 *****/
function findText($html=null) {
    if ($html==null) { return false; }
    $tags = array(); $text = array();
    if (is_string($html)) { $tags = parseHtml($html); }
    else if (is_array($html)) { $tags = $html;}
    else { $tags = false; }
    
    if ($tags==false) { return false; }
    foreach($tags as $t) {
        if ($t['TAG']==false && $t['TYPE']=='T' && isset($t['TXT'])) {
            $text[] = $t['TXT'];
        }
    }
    return $text;
}

?>