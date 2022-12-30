<?php
//you can set this as per your requirements
$qualityJpg = 80;
$qualityPng = 8;
$target_width = 1024;

//getting all images in a directory
$dir = "./beforeResize";
$dh  = opendir($dir);
while (false !== ($filename = readdir($dh))) {
    $files[] = $filename;
}

$images = preg_grep ('/\.jpg$/i', $files);
$images = array_merge($images, (preg_grep ('/\.JPG$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.png$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.PNG$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.jpeg$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.JPEG$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.gif$/i', $files)));
$images = array_merge($images, (preg_grep ('/\.GIF$/i', $files)));

$images = array_unique($images);
//$images is array of all image file names

//Creating folder for target
if (! file_exists("./resized")) {
    mkdir("./resized", 0777, true);
}
$targetFolder = "./resized";

//looping through all images and resizing one by one
foreach ($images as $image) {
    //source_folder + file_name (string) 
    $source = $dir."/".$image; 
    //targete_folder + file_name (string) 
    $target = $targetFolder."/".$image; 
    
    //If PNG image create resource / object using imagecreatefrompng
    if ((exif_imagetype($source) == IMAGETYPE_PNG)) {
        $imageObj = imagecreatefrompng($source);
    } elseif((exif_imagetype($source) == IMAGETYPE_JPEG)) { //If JPG image create resource / object using imagecreatefromjpeg
        $imageObj = imagecreatefromjpeg($source);
    } else {
    		echo "resizeImage: $image - This file type can not be resized here!\r\n";
        continue;
    }
    
    if (!$imageObj)
    {
    		echo "resizeImage: $image - Unable to resize!\r\n";
        continue;
    }
    
    list($origWidth, $origHeight) = getimagesize($source);
    
    if ($origWidth <= $target_width) {
        echo "resizeImage: $image - target width is less than current width . NOT resized!\r\n";
        continue;
    }
    
    $maxWidth = $target_width;
    $maxHeight = 0;
    if ($maxWidth == 0)
    {
        $maxWidth  = $origWidth;
    }
    if ($maxHeight == 0)
    {
        $maxHeight = $origHeight;
    }
    
    $widthRatio = $maxWidth / $origWidth;
    $heightRatio = $maxHeight / $origHeight;
    $ratio = min($widthRatio, $heightRatio);
    $newWidth  = (int)$origWidth  * $ratio;
    $newHeight = (int)$origHeight * $ratio;
    
    //creation of new image
    if ((exif_imagetype($source) == IMAGETYPE_PNG)) {
        $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $imageObj, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        $return_result = imagepng($newImage, $target, $qualityPng);
    } elseif((exif_imagetype($source) == IMAGETYPE_JPEG)) {
        $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
        imagecopyresampled($newImage, $imageObj, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        $return_result = imagejpeg($newImage, $target, $qualityJpg);
    } else {
    		echo "resizeImage: $image - This file type can not be resized here!\r\n";
    }
    
    if (TRUE == $return_result) {
				echo "resizeImage: $image resized SUCCESSFULLY!\r\n";
    } else {
        echo "resizeImage: $image NOT resized!\r\n";
    }
    
    imagedestroy($imageObj);
    imagedestroy($newImage);
}

echo "resizeImage: Completed\r\n";
?>
