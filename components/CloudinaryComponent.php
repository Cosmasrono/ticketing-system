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
        $config = Configuration::instance([
            'cloud' => [
                'cloud_name' => $this->cloud_name,
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        $this->_cloudinary = new Cloudinary($config);
    }

    public function uploadImage($tmpFile)
    {
        try {
            $result = $this->_cloudinary->uploadApi()->upload($tmpFile, [
                'folder' => 'support_tickets',
                'overwrite' => true,
                'resource_type' => 'image'
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}