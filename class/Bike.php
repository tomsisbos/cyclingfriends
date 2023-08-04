<?php

class Bike extends Model {
    
    private $container_name = 'user-profile-bikes';
    protected $table = 'bikes';
    public $id;
    public $user;
    public $number;
    public $type;
    public $model;
    public $components;
    public $wheels;
    public $description;
    public $img_blob;
    public $img_size;
    public $img_name;
    public $img_type;

    function __construct($id = NULL) {
        parent::__construct();
        if ($id != NULL) $this->load($id);
    }

    public function load ($id) {
        $this->id          = $id;
        $data = $this->getData($this->table);
        $this->user        = new User($data['user_id']);
        $this->number      = $data['number'];
        $this->type        = $data['type'];
        $this->model       = $data['model'];
        $this->components  = $data['components'];
        $this->wheels      = $data['wheels'];
        $this->description = $data['description'];
        $this->filename    = $data['filename'];
        $this->img_size    = $data['img_size'];
        $this->img_name    = $data['img_name'];
        $this->img_type    = $data['img_type'];
    }

    public function getType () {
        switch ($this->type) {
            case 'other': return 'その他';
            case 'roadbike': return 'ロードバイク';
            case 'citybike': return 'ママチャリ';
            case 'mountainbike': return 'マウンテンバイク';
            case 'gravelcxbike': return 'グラベル／シクロクロスバイク';
        }
    }

    public function create ($user_id) {
        // Get next bike number for this user
        $findLastBikeNumber = $this->getPdo()->prepare('SELECT number FROM bikes WHERE user_id = ? ORDER BY number DESC');
        $findLastBikeNumber->execute([$user_id]);
        $number = intval($findLastBikeNumber->fetch(PDO::FETCH_COLUMN)) + 1;
        // Get next auto increment key and store it as bike id
        $this->id = getNextAutoIncrement($this->table);
        // Create a new empty bike for this user
        $insertBikeInfos = $this->getPdo()->prepare('INSERT INTO bikes (user_id, number, type, model, components, wheels, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertBikeInfos->execute([$user_id, $number, '', '', '', '', '']);
        $this->user = new User($user_id);
        $this->number = $number;
    }

    public function update ($type, $model, $components, $wheels, $description, $number) {
        $updateBikeInfos = $this->getPdo()->prepare('UPDATE bikes SET bike_type = ?, bike_model = ?, bike_components = ?, bike_wheels = ?, bike_description = ? WHERE user_id = ? AND number = ?');
        $updateBikeInfos->execute(array($type, $model, $components, $wheels, $description, $number));
        $this->type = $type;
        $this->model = $model;
        $this->components = $components;
        $this->wheels = $wheels;
        $this->description = $description;
    }

    /**
     * Only update one value
     * @param string $column Corresponding column
     * @param string $value value to update
     */
    public function updateValue ($column, $value) {
        $updateBikeInfos = $this->getPdo()->prepare("UPDATE bikes SET {$column} = ? WHERE id = ?");
        $updateBikeInfos->execute(array($value, $this->id));
    }

    public function isSet ($user_id, $number) {
        $checkIfExists = $this->getPdo()->prepare('SELECT id FROM bikes WHERE user_id = ? AND number = ?');
        $checkIfExists->execute(array($user_id, $number));
        if ($checkIfExists->rowCount() > 0) return true;
        else return false;
    }

    // Function for downloading & displaying user's bike image as a presized square
    public function displayImage () {
        
        // If the user has uploaded an image, use it as bike image
        if (isset($this->filename)) { 

            // Connect to blob storage
            require Bike::$root_folder . '/actions/blobStorage.php';

            // Retrieve blob url
            echo '<img class="pf-bike-image" src="' .$blobClient->getBlobUrl($this->container_name, $this->filename). '" />';
            
        // Else, use a profile picture corresponding to user's randomly attribuated icon
        } else echo '<img class="pf-bike-image" src="\media\default-bike-' . rand(1,9) . '.svg" />';

    }

    // Function for uploading a bike image
    public function uploadImage ($file) {

        $temp_image = new TempImage($file['name']);

        // Store temp file as *jpg or *png
        $result = $temp_image->convert($file['tmp_name']);
        if (!$result) return array('error' => 'アップロードしたファイル形式は対応しておりません。対応可能なファイル形式：' .implode(', ', $temp_image->accepted_formats));

        // Sort upload data into variables
        $img_size = $file['size'];
        $img_name = $temp_image->name;
        $img_type = $file['type'];
        $img_blob = $temp_image->treatFile($temp_image->temp_path);
        $filename = setFilename('img');
        $metadata = [
            'user_id' => $this->user->id,
            'datetime' => (new DateTime('now'))->setTimezone('Asia/Tokyo')->format('Y-m-d H:i:s')
        ];

        // Connect to blob storage
        require Bike::$root_folder . '/actions/blobStorage.php';

        $blobClient->createBlockBlob($this->container_name, $filename, $img_blob);
        $blobClient->setBlobMetadata($this->container_name, $filename, $metadata);

        // If he does, update data in the database
        $updateImage = $this->getPdo()->prepare('UPDATE bikes SET filename = ?, img_size = ?, img_name = ?, img_type = ? WHERE id = ?');
        $updateImage->execute(array($filename, $img_size, $img_name, $img_type, $this->id));

        return array('success' => 'バイク写真を更新しました !');

        /*
        
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
            $error = "ファイルアップロード中に問題が発生しました。";
            return array(false, $error);
                
        } else {
                
            // Displays an error message if file size exceeds $max_size
            $img_size = $_FILES['bikeimagefile']['size'];
            if ($img_size > $max_size) {
                $error = 'アップロードしたファイルがサイズの上限 (5Mb)を超えています。サイズを縮小して再度試してください。';
                return array(false, $error);
            }
            
            // Displays an error message if format is not accepted
            $img_type = $_FILES['bikeimagefile']['type'];
            if ($img_type != 'image/jpeg') {
                $error = 'アップロードしたファイルは*.jpg形式のファイルではありません。*.jpg形式の画像データで再度試してください。';
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
            $updateImage = $this->getPdo()->prepare('UPDATE bikes SET img_blob = ?, img_size = ?, img_name = ?, img_type = ? WHERE id = ?');
            $updateImage->execute(array($img_blob, $img_size, $img_name, $img_type, $id));
            
            $success = 'バイクの写真が更新されました！';		
            return array(true, $success);
        }*/
    }

}