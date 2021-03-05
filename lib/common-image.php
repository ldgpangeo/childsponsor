<?php
/*
 * convert a hexadecimal color into decimal RBG values
 * from
 *   http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
 */
function hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   debug ("color $hex becomes $r, $g, $b");
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}

function rgb2hex($rgb) {
   $hex = "#";
   $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
   $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

   return $hex; // returns the hex value including the number sign (#)
}


// Calculate RGB Colors of a pixel:
// Returns an object ($obj->red, $obj->green, $obj->blue, $obj->bw)
function rgb($pixelData){
    $arr = array();
    $arr["red"] = ($pixelData >> 16) & 0xFF;
    $arr["green"] = ($pixelData >> 8) & 0xFF;
    $arr["blue"] = $pixelData & 0xFF;
    $arr["bw"] = ($arr["red"] + $arr["green"] + $arr["blue"]) / 3;
    return (object)$arr;
}
// Calculate the average RGB of an entire image:
// Returns an object ($obj->red, $obj->green, $obj->blue, $obj->bw)
function averageRGB($img){
    $red = 0;
    $green = 0;
    $blue = 0;
    $pixel = 0;
    $count = 0;
    $width = imagesx($img);
    $height = imagesy($img);
    for($x = 0; $x < $width; $x++){
        for ($y = 0; $y < $height; $y++){
            $pixel = imagecolorat($img, $x, $y);
            $red += $pixel >> 16 & 0xFF;
            $green += $pixel >> 8 & 0xFF;
            $blue += $pixel & 0xFF;
            $count++;
        }
    }
    $bw   = (($red / $count) + ($green / $count) + ($blue / $count)) / 3;
    $avgR = ($bw / ($red / $count)) * 1.3;
    $avgG = ($bw / ($green / $count)) * 1.3;
    $avgB = ($bw / ($blue / $count)) * 1.3;
    return (object)array("red"=>$avgR, "green"=>$avgG, "blue"=>$avgB, "bw"=>$bw);
}
// Automatically adjusts the colors of an image
// Returns an image resource
function adjustcolor($location,$r,$g,$b){
    $str = file_get_contents($location);
    if(!$str){
        return false;
    }
    $img = imagecreatefromstring($str);
    $width = imagesx($img);
    $height = imagesy($img);
    for($x = 0; $x < $width; $x++){
        for($y = 0; $y < $height; $y++){
            $pixel = imagecolorat($img, $x, $y);
            $rgb = rgb($pixel);
            $red   = floor($rgb->red * (1+$r/100));
            $green = floor($rgb->green * (1+$g/100));
            $blue  = floor($rgb->blue * (1+$b/100));
            if($red > 255) $red = 255;
			if ($red <0) $red = 0;
            if($green > 255) $green = 255;
			if ($green <0) $green = 0;
            if($blue > 255) $blue = 255;
			if ($blue <0) $blue = 0;
			debug ("new color is $red, $green, $blue");
            $new_color = imagecolorallocate($img, $red, $green, $blue);
            imagesetpixel($img, $x, $y, $new_color);
        }
    }
    return $img;
}


?>