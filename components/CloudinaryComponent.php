<?php
namespace app\components;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use yii\base\Component;

class CloudinaryComponent extends Component
{
    public $cloud_name;
    public $api_key;
    public $api_secret;

    private $_cloudinary;

    public function init()
    {
        parent::init();
        
        // Configure Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => $this->cloud_name,
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->_cloudinary = new Cloudinary();
    }

    public function upload($filePath, $options = [])
    {
        try {
            // Upload the file to Cloudinary
            $result = $this->_cloudinary->uploadApi()->upload($filePath, $options);
            
            return [
                'success' => true,
                'secure_url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            \Yii::error('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}