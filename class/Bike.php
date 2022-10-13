<?php

class Bike extends Model {
    
    protected $table = 'bikes';

    function __construct($id = NULL) {
        $this->id               = $id;
        $data = $this->getData($this->table);
        $this->user             = new User ($data['user_id']);
        $this->number           = $data['number'];
        $this->type             = $data['type'];
        $this->model            = $data['model'];
        $this->components       = $data['components'];
        $this->wheels           = $data['wheels'];
        $this->description      = $data['description'];
        $this->img_blob         = $data['img_blob'];
        $this->img_size         = $data['img_size'];
        $this->img_name         = $data['img_name'];
        $this->img_type         = $data['img_type'];

    }

    // Function for downloading & displaying user's bike image as a presized square
    public function displayImage () {
        
        // If the user has uploaded an image, use it as bike image
        if (isset($this->img_blob)) {
            echo '<img class="bike-image-img" src="data:image/jpeg;base64,' . base64_encode($this->img_blob) . '" />';
            
        // Else, use a profile picture corresponding to user's randomly attribuated icon
        }else {
            echo '<img class="bike-image-img" src="\includes\media\default-bike-' .$this->user->getDefaultPropicId(). '.svg" />';
        }
    }

    // Function for uploading a bike image
    public function uploadImage() {
        require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
        
        // Declaration of variables
        $return        = false;
        $id            = $_POST['bike-id'];
        $img_blob      = '';
        $img_size      = 0;
        $max_size      = 5000000;
        $img_name      = '';
        $img_type      = '';
        $return        = is_uploaded_file($_FILES['bikeimagefile']['tmp_name']);
        
        // Displays an error message if any problem through upload
        if (!$return) {
            $error = "A problem has occured during file upload.";
            return array(false, $error);
                
        } else {
                
            // Displays an error message if file size exceeds $max_size
            $img_size = $_FILES['bikeimagefile']['size'];
            if ($img_size > $max_size) {
                $error = 'The image you uploaded exceeds size limit (5Mb). Please reduce the size and try again.';
                return array(false, $error);
            }
            
            // Displays an error message if format is not accepted
            $img_type = $_FILES['bikeimagefile']['type'];
            if ($img_type != 'image/jpeg') {
                $error = 'The file you uploaded is not at *.jpg format. Please try again with a *.jpg image file.';
                return array(false, $error);
            }
                
            // Sort upload data into variables
            $img_name = $_FILES['bikeimagefile']['name'];
            if (img_compress($_FILES['bikeimagefile']['tmp_name'], $img_size)[0] == false) {
                return array(false, img_compress($_FILES['bikeimagefile']['tmp_name'], $img_size)[1]);
            } else {
                $img_blob = img_compress($_FILES['bikeimagefile']['tmp_name'], $img_size)[1];
            }
                
            // Update data
            $updateImage = $db->prepare('UPDATE bikes SET img_blob = ?, img_size = ?, img_name = ?, img_type = ? WHERE id = ?');
            $updateImage->execute(array($img_blob, $img_size, $img_name, $img_type, $id));
            
            $success = 'Bike image has been correctly updated !';		
            return array(true, $success);
        }
    }

}