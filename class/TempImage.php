<?php

class TempImage {

    private string $temp_folder;
    private int $compression_percentage = 85;
    public array $accepted_formats = ['jpg', 'jpeg', 'heic', 'png'];
    public string $temp_name = 'temp.jpg';
    public string $name;
    public string $temp_path;
    public GdImage $image;

    public function __construct ($name) {
        $this->temp_folder = $_SERVER["DOCUMENT_ROOT"]. "/media/temp/";
        $this->name = $name;
    }
    
    /**
     * Get base64 string jpeg data inside a variable
     */
    private function base64ToJpeg (string $base64) {
        $this->temp_path = $this->temp_folder . $this->temp_name;
        base64_to_jpeg($base64, $this->temp_path);
        return imagecreateexif($this->temp_path);
    }

    /**
     * Get a base 64 string from a gd image
     * @return string // base64
     */
    private function getBase64 (gdImage $image) {
        ob_start(); 
        imagejpeg($image);
        $image_data = ob_get_contents();
        ob_end_clean(); 
        return base64_encode($image_data);
    }

    /**
     * Get path to a formated image from GdImage
     */
    private function formatImage (GdImage $img) {
        if (imagesx($img) > 1600) $img = imagescale($img, 1600); // only scale if img is wider than 1600px
        ///imagegammacorrect($img, 1.0, 1.1); // enhance gamma
        ///imagefilter($img, IMG_FILTER_CONTRAST, -5); // enhance contrast
        $this->temp_path = $this->temp_folder. "formated_" .$this->name;
        imagejpeg($img, $this->temp_path, $this->compression_percentage); // compress
        return $this->temp_path;
    }

    /**
     * format thumbnail
     */
    private function formatThumbnail () {
        imagegammacorrect($this->thumbnail, 1.0, 1.275);
        imagefilter($this->thumbnail, IMG_FILTER_CONTRAST, -12);
        return $this->getBase64($this->thumbnail);
    }

    /**
     * change temp name
     */
    public function setTempName ($temp_name) {
        $this->temp_name = $temp_name;
        $this->temp_path = $this->temp_folder . $this->temp_name;
    }

    public function setName (string $name) {
        $this->name = $name;
    }

    /**
     * Convert a file from $accepted_formats to jpg
     * @return string file path
     */
    public function convert (string $file) {

        // Get file format (extension)
        $ext = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));

        // If file format is accepted
        if (in_array($ext, $this->accepted_formats)) {

            // Prepare file name and path
            $this->setName(pathinfo($this->name, PATHINFO_FILENAME). '.jpg');
            $this->temp_path = $this->temp_folder . $this->temp_name;

            // jpg
            if ($ext == 'jpg' || $ext == 'jpeg') move_uploaded_file($file, $this->temp_path);

            // png
            if ($ext == 'png') {
                $this->setTempName('temp.png');
                move_uploaded_file($file, $this->temp_path);
            }

            // HEIC to jpg conversion
            else if ($ext == 'heic') $result = Maestroerror\HeicToJpg::convert($file)->saveAs($this->temp_path);

            return true;

        } else return false;
        
    }

    /**
     * Get a blob ready to store from base 64 data
     */
    public function treatBase64 (string $base64) {

        // Get image data as a *.jpeg file
        $this->image = $this->base64ToJpeg($base64);

        // Get path to formated image
        $this->temp_path = $this->formatImage($this->image, $this->name);

        // Return as blob
        return fopen($this->temp_path, 'r');
    }

    /**
     * Get a blob ready to store from uploaded file
     */
    public function treatFile (string $temp_path) {

        // Get image data as a gd file
        $this->temp_path = $this->temp_folder. $this->temp_name;
        move_uploaded_file($temp_path, $this->temp_path);
        $this->image = imagecreateexif($this->temp_path);

        // Get path to formated image
        $this->temp_path = $this->formatImage($this->image, $this->name);

        // Return as blob
        return fopen($this->temp_path, 'r');
    }

    /**
     * Get thumbnail from image (image need to have been previously generated)
     */
    public function getThumbnail () {
        
        // Scale image to thumbnail size
        $this->thumbnail = imagescale($this->image, 48, 36);

        // Correct image gamma and contrast
        $this->thumbnail = $this->formatThumbnail();
        
        return $this->thumbnail;
    }

}