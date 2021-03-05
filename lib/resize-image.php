<?php

function resize_jpg($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors) {
	# define the input image
	$src_image = imagecreatefromjpeg($src);
	if ($src_image === false) {
		$errors = "Unable to create image from file $src";
		return false;
	}

	# define the output image
	$dest_image = imagecreatetruecolor($dest_width,$dest_height);
	if ($dest_image === false) {
		$errors = "Unable to create output image ($dest)";
		return false;
	}

	# resize from input to output
	$res = imagecopyresampled($dest_image, $src_image, 0,0,0,0,$dest_width,$dest_height,$src_width,$src_height);
	if ($res === false) {
		$errors = "Unable to resample image ($src)";
	}

	# Save new image to disk
	$res = imagejpeg($dest_image,$dest,60);
	if ($res === false) {
		$errors = "Unable to save image file ($dest)";
		return false;
	}
	return true;
}

function resize_gif($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors) {
	# define the input image
	$src_image = imagecreatefromgif($src);
	if ($src_image === false) {
		$errors = "Unable to create image from file $src";
		return false;
	}

	# define the output image
	$dest_image = imagecreatetruecolor($dest_width,$dest_height);
	if ($dest_image === false) {
		$errors = "Unable to create output image ($dest)";
		return false;
	}

	# resize from input to output
	$res = imagecopyresampled($dest_image, $src_image, 0,0,0,0,$dest_width,$dest_height,$src_width,$src_height);
	if ($res === false) {
		$errors = "Unable to resample image ($src)";
	}

	# Save new image to disk
	$res = imagegif($dest_image,$dest);
	if ($res === false) {
		$errors = "Unable to save image file ($dest)";
		return false;
	}
	return true;
}

function resize_png($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors) {
	# define the input image
	$src_image = imagecreatefrompng($src);
	if ($src_image === false) {
		$errors = "Unable to create image from file $src";
		return false;
	}

	# define the output image
	$dest_image = imagecreatetruecolor($dest_width,$dest_height);
	if ($dest_image === false) {
		$errors = "Unable to create output image ($dest)";
		return false;
	}

	# resize from input to output
	$res = imagecopyresampled($dest_image, $src_image, 0,0,0,0,$dest_width,$dest_height,$src_width,$src_height);
	if ($res === false) {
		$errors = "Unable to resample image ($src)";
	}

	# Save new image to disk
	$res = imagepng($dest_image,$dest);
	if ($res === false) {
		$errors = "Unable to save image file ($dest)";
		return false;
	}
	return true;
}

function resize_image ($src,$dest, $width,&$errors,$square=false) {
	debug("Resize_image($src, $dest, $width, $errors)");
	$success = true;
	if (!is_file($src)) {
		$errors = "Input file not found - file size may be too large ($src)";
		return false;
	}
	
	#  get size of source file
	$metainfo = getimagesize($src);
	if ($metainfo === false) {
		$errors = "Unable to get image information. ($src) ";		
		return false;
	}
	$src_width  = $metainfo[0];
	$src_height = $metainfo[1];
	
	#  determine the image type
	$src_type   = exif_imagetype($src);
	debug ("image type is $src_type for $src");
	switch ($src_type) {
		case  IMAGETYPE_GIF :
			$type = "gif";
			break;
		case IMAGETYPE_JPG :
		case IMAGETYPE_JPEG :
			$type = "jpg";
			break;
		case IMAGETYPE_PNG :
			$type = "png";
			break;
		default:
			$errors = "Unsupported image type ($src_type).";
			return false;
	}
	
	if ($src_width > $src_height) {
		$ratio = $width / $src_width ;
	} else {
		$ratio = $width / $src_height ;
	}
	debug ("source image is $src_width wide by $src_height wide");
	
	#  if width is greater than desired, resize the image
	if ($ratio < 1) {
		$dest_width  = floor($src_width * $ratio);
		$dest_height = floor($src_height * $ratio);
	} else {
		#  retain the original size
		$dest_width = $src_width;
		$dest_height = $src_height;
	}
	#  if square is set, make the image a square of the indicated size
	if ($square) {
		$dest_width = $width;
		$dest_height = $width;
	}

	switch ($type) {
		case "jpg" :
			return (resize_jpg($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors));
			break;
		case "gif" :
			return (resize_gif($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors));
			break;	
		case "png" :
			return (resize_png($src, $dest,$dest_width,$dest_height,$src_width,$src_height,&$errors));
			break;	
	}
		
	#  If you get here, report success
	return true;
} 


?>