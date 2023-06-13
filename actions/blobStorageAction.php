<?php
$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $folder . "/vendor/autoload.php";
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

# Setup a specific instance of an Azure::Storage::Client
$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('AZURE_STORAGE_ACCOUNT_NAME').";AccountKey=".getenv('AZURE_STORAGE_ACCOUNT_KEY');
$blobClient = BlobRestProxy::createBlobService($connectionString);

?>