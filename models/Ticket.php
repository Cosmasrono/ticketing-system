<?php

namespace app\models;

use Yii; // Make sure this line is included
 
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use \DateTime;  // Add this import
use \DateTimeZone;  // Add this import too
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class Ticket extends ActiveRecord
{

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_CLOSED = 'closed';
    const STATUS_ESCALATED = 'escalated';
    const STATUS_REOPEN = 'reopen';
    const STATUS_DELETED = 'deleted';
    const STATUS_REASSIGNED = 'reassigned';

    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $uploadedFile;
 
    public static function tableName()
    {
        return 'ticket'; // make sure this matches your actual table name
    }




    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => function() { 
                    return date('Y-m-d H:i:s');  // This will store exact current datetime
                }
            ],
        ];
    }

    public function rules()
    {
        return [
            [['selectedModule', 'issue', 'description'], 'required'],
            ['screenshot', 'string'], // For base64 data
             [['created_at'], 'safe'],
            [['created_by'], 'integer'],
            [['selectedModule', 'issue', 'status'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['approved_at'], 'safe'],
            // [['approved_by'], 'integer'],
            [['escalation_comment'], 'string'],
            [['assigned_to'], 'integer'],
            [['escalated_to'], 'required', 'when' => function($model) {
                return $model->status === 'reassigned';
            }, 'whenClient' => "function (attribute, value) {
                return $('#ticket-status').val() === 'reassigned';
            }"],
            ['uploadedFile', 'file', 
                'skipOnEmpty' => true, 
                'extensions' => ['png', 'jpg', 'jpeg', 'gif'],
                'maxSize' => 5 * 1024 * 1024, // 5MB limit
                'maxFiles' => 1,
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                'wrongMimeType' => 'Only JPEG, PNG and GIF images are allowed.',
                'tooBig' => 'The file was larger than 5MB. Please upload a smaller file.',
            ],
            [['screenshot'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 5*1024*1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'selectedModule' => 'Select Module',
            'issue' => 'Select Issue',
            'description' => 'Description',
            'screenshot' => 'Screenshot',
            'screenshot_base64' => 'Screenshot Base64',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'escalation_comment' => 'Escalation Comment',
            'assigned_to' => 'Assigned To',
        ];
    }

  

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }


    public function getAssignedDeveloper()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }





    public function getDeveloper()
    {
        return $this->hasOne(User::class, ['id' => 'developer_id']);
    }

    public function getAssignedTo()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }


    // public function approve()
    // {
    //     $this->status = 'approved';
    //     return $this->save();
    // }

    public function cancel()
    {
        $this->status = 'cancelled';
        return $this->save();
    }
    // public function getCompany()
    // {
    //     return $this->hasOne(Company::class, ['id' => 'company_id']);
    // }

    // public static function findTicketsByCompanyEmail($email)
    // {
    //     return self::find()->where(['company_email' => $email])->all();
    // }

    public function approve()
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_at = new \yii\db\Expression('NOW()');
        $this->approved_by = Yii::$app->user->id;

        return $this->save(false);
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['assign'] = ['assigned_to', 'status'];
        return $scenarios;
    }

   
    

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::info("afterSave called for ticket {$this->id}. Changed attributes: " . json_encode($changedAttributes));

        // Your custom logic here
    }

    public function getAssignedUser()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_to']);
    }

    public function getClosedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'closed_by']);
    }

    public function getRaisedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'raised_by']); // Relation to the user who raised the ticket
    }

    public function getRemainingTimeInSeconds()
    {
        // Implement your logic here
        // For example:
        $createdAt = strtotime($this->created_at);
        $now = time();
        $difference = $now - $createdAt;
        $remainingTime = 3600 - $difference; // Assuming 1 hour limit
        return max(0, $remainingTime);
    }

    public function getElapsedTime()
    {
        if ($this->timer_start === null) {
            return '0:00:00';
        }
        $now = new \DateTime();
        $start = new \DateTime($this->timer_start);
        $interval = $now->diff($start);
        return $interval->format('%H:%I:%S');
    }

    public function getTimeTaken()
    {
        if ($this->status === 'closed' && $this->created_at && $this->closed_at) {
            $createdAt = new \DateTime($this->created_at);
            $closedAt = new \DateTime($this->closed_at);
            $interval = $createdAt->diff($closedAt);
            
            $days = $interval->d;
            $hours = $interval->h;
            $minutes = $interval->i;
            
            $timeTaken = [];
            if ($days > 0) $timeTaken[] = $days . ' day' . ($days > 1 ? 's' : '');
            if ($hours > 0) $timeTaken[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) $timeTaken[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            
            return implode(', ', $timeTaken);
        } else {
            return 'Reviewing';
        }
    }

    public function getModule()
    {
        return $this->hasOne(Module::class, ['id' => 'module_id']);
    }

    public static function getStatusCounts()
    {
        $counts = static::find()
            ->select(['status', 'COUNT(*) as count'])
            ->groupBy('status')
            ->indexBy('status')
            ->column();

        $statuses = ['approved', 'assigned', 'pending', 'cancelled'];
        foreach ($statuses as $status) {
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
        }

        return $counts;
    }

    // Add other relations and methods as needed

    public function getAssignButtonText()
    {
        return $this->status === self::STATUS_ESCALATED ? 'Reassign' : 'Assign';
    }

    public function getCompanyName()
    {
        return $this->user->company_name; // Assuming there's a relation to User model
    }

    public function getCompanyEmail()
    {
        return $this->user ? $this->user->company_email : null;
    }

    public $company_name; // Add this if it's not a database field but needed in the model

    public function fields()
    {
        return array_merge(parent::fields(), [
            'company_name' => function ($model) {
                return $model->companyName;
            },
        ]);
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_ESCALATED => 'Escalated',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REOPEN => 'Reopen',
            self::STATUS_REASSIGNED => 'Reassigned',
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['updated_at', 'assigned_to', 'screenshot_base64']);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->created_at = $this->ensureInteger($this->created_at);
        $this->closed_at = $this->ensureInteger($this->closed_at, true);

        return true;
    }

    private function ensureInteger($value, $allowNull = false)
    {
        if ($allowNull && ($value === null || $value === '')) {
            return null;
        }
        return $value === null || $value === '' ? time() : (int)$value;
    }

    /**
     * Escalate the ticket
     * @return boolean whether the ticket was escalated successfully
     */
    public function escalate()
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_CLOSED, self::STATUS_ESCALATED])) {
            return false;
        }

        $this->status = self::STATUS_ESCALATED;
        $this->escalated_at = new Expression('NOW()');
        
        return $this->save();
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getCreatedAtKenyan()
    {
        if ($this->created_at) {
            $date = new DateTime($this->created_at, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Africa/Nairobi'));
            return $date->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function getStatusLabel()
    {
        $labels = [
            'open' => 'Open',
            'closed' => 'Closed',
            'pending' => 'Pending',
            // Add other statuses as needed
        ];
        
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function calculateTimeTaken()
    {
        if ($this->assigned_at && $this->closed_at) {
            $assigned = new \DateTime($this->assigned_at);
            $closed = new \DateTime($this->closed_at);
            $interval = $assigned->diff($closed);
            
            // Format the time taken
            $timeTaken = '';
            if ($interval->d > 0) {
                $timeTaken .= $interval->d . ' days ';
            }
            if ($interval->h > 0) {
                $timeTaken .= $interval->h . ' hours ';
            }
            if ($interval->i > 0) {
                $timeTaken .= $interval->i . ' minutes';
            }
            
            return trim($timeTaken);
        }
        return null;
    }

    // Helper method to get resolution time
    public function getResolutionTime()
    {
        if ($this->closed_at && $this->created_at) {
            $created = new \DateTime($this->created_at);
            $closed = new \DateTime($this->closed_at);
            return $closed->diff($created)->format('%d days, %h hours, %i minutes');
        }
        return null;
    }

    // Helper method to get response time
    public function getResponseTime()
    {
        if ($this->assigned_at && $this->created_at) {
            $created = new \DateTime($this->created_at);
            $assigned = new \DateTime($this->assigned_at);
            return $assigned->diff($created)->format('%d days, %h hours, %i minutes');
        }
        return null;
    }

    // public function beforeSave($insert)
    // {
    //     if (!parent::beforeSave($insert)) {
    //         return false;
    //     }

    //     try {
    //         // Debug logging
    //         Yii::debug("Screenshot base64 data length: " . strlen($this->screenshot_base64), 'ticket');
            
    //         // Clean up base64 data if it's empty
    //         if (empty($this->screenshot_base64)) {
    //             $this->screenshot_base64 = null;
    //         }

    //         // No need for file operations - just store the base64 string directly
    //         if (!empty($this->screenshot_base64)) {
    //             // Extract image data
    //             if (preg_match('/^data:image\/(\w+);base64,/', $this->screenshot_base64)) {
    //                 // Extract image data
    //                 list($type, $data) = explode(';', $this->screenshot_base64);
    //                 list(, $data) = explode(',', $data);
    //                 list(, $type) = explode(':', $type);
    //                 list(, $extension) = explode('/', $type);

    //                 // Generate filename
    //                 $filename = 'ticket_' . uniqid() . '.' . $extension;
                    
    //                 // Save path
    //                 $uploadPath = Yii::getAlias('@webroot/uploads/tickets/');
                    
    //                 // Create directory if it doesn't exist
    //                 if (!file_exists($uploadPath)) {
    //                     if (!mkdir($uploadPath, 0777, true)) {
    //                         Yii::error('Failed to create upload directory');
    //                         return false;
    //                     }
    //                 }

    //                 // Save file
    //                 if (file_put_contents($uploadPath . $filename, base64_decode($data))) {
    //                     $this->screenshot = $filename;  // Save filename to database
    //                     Yii::debug('Image saved successfully: ' . $filename);
    //                 } else {
    //                     Yii::error('Failed to save image file');
    //                     return false;
    //                 }
    //             }
    //         }

    //         return true;
    //     } catch (\Exception $e) {
    //         Yii::error('Error in beforeSave: ' . $e->getMessage());
        //     return false;
        // }   
    

    public function getLocalCreatedAt()
    {
        if ($this->created_at) {
            $timestamp = strtotime($this->created_at);
            return Yii::$app->formatter->asDatetime($timestamp, 'php:Y-m-d H:i:s');
        }
        return null;
    }

    // Add getter methods for formatted dates
    public function getFormattedCreatedAt()
    {
        return Yii::$app->formatter->asDatetime($this->created_at, 'php:Y-m-d H:i:s');
    }

    public function getFormattedAssignedAt()
    {
        return $this->assigned_at ? 
            Yii::$app->formatter->asDatetime($this->assigned_at, 'php:Y-m-d H:i:s') : 
            'Not Assigned';
    }

    public function getFormattedClosedAt()
    {
        return $this->closed_at ? 
            Yii::$app->formatter->asDatetime($this->closed_at, 'php:Y-m-d H:i:s') : 
            'Not Closed';
    }

    public $selectedModule;
    public $screenshot;
    public $screenshot_base64;

    public static function getModulesList()
    {
        return [
            'Power BI' => 'Power BI',
            'Members Portal' => 'Members Portal',
            'Mobile App' => 'Mobile App',
            'CRM' => 'CRM',
        ];
    }

    public function getCompanyNameFromEmail($email)
    {
        if (empty($email)) {
            return null;
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return null;
        }
        $domain = $parts[1];
        return ucwords(str_replace(['.com', '.org', '.net'], '', $domain));
    }

    public function getEscalationHistory()
    {
        return $this->hasOne(TicketEscalation::class, ['ticket_id' => 'id']);
    }

    public function getEscalatedTo()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_to']);
    }

    public function getEscalatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'escalated_by']);
    }

    // public function beforeSave($insert)
    // {
    //     if (!parent::beforeSave($insert)) {
    //         return false;
    //     }

    //     Yii::debug("beforeSave check:", 'ticket');
    //     Yii::debug([
    //         'has_base64' => !empty($this->screenshot_base64),
    //         'base64_length' => strlen($this->screenshot_base64)
    //     ], 'ticket');

    //     return true;
    // }

    public function getScreenshotImage()
    {
        if (empty($this->screenshot_base64)) {
            return null;
        }
        
        // Add the data URI prefix if it's missing
        if (strpos($this->screenshot_base64, 'data:image') === false) {
            return 'data:image/jpeg;base64,' . $this->screenshot_base64;
        }
        
        return $this->screenshot_base64;
    }

    // Add validation method for base64 image
    public function validateBase64Image($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        // Check if it's a valid base64 string
        if (!preg_match('/^data:image\/(png|jpeg|jpg|gif);base64,/', $this->$attribute)) {
            $this->addError($attribute, 'Invalid image format');
            return;
        }

        // Get file size in bytes
        $base64_size = strlen(base64_decode(explode(',', $this->$attribute)[1]));
        
        // Check file size (e.g., 5MB limit)
        if ($base64_size > 5 * 1024 * 1024) {
            $this->addError($attribute, 'Image file size should not exceed 5MB');
            return;
        }
    }

    // Method to save base64 image
    public function saveBase64Image()
    {
        if (empty($this->screenshot_base64)) {
            Yii::error('No base64 data available');
            return true;
        }

        try {
            // Debug output
            Yii::debug('Starting base64 image save');
            
            // Extract image data
            if (!preg_match('/^data:image\/(\w+);base64,/', $this->screenshot_base64)) {
                Yii::error('Invalid base64 image format');
                return false;
            }

            // Extract image data
            list($type, $data) = explode(';', $this->screenshot_base64);
            list(, $data) = explode(',', $data);
            list(, $type) = explode(':', $type);
            list(, $extension) = explode('/', $type);

            // Generate filename
            $filename = 'ticket_' . uniqid() . '.' . $extension;
            
            // Save path
            $uploadPath = Yii::getAlias('@webroot/uploads/tickets/');
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true)) {
                    Yii::error('Failed to create upload directory');
                    return false;
                }
            }

            // Save file
            if (file_put_contents($uploadPath . $filename, base64_decode($data))) {
                $this->screenshot = $filename;  // Save filename to database
                Yii::debug('Image saved successfully: ' . $filename);
                return true;
            }

            Yii::error('Failed to save image file');
            return false;
        } catch (\Exception $e) {
            Yii::error('Error saving base64 image: ' . $e->getMessage());
            return false;
        }
    }

    // public function beforeSave($insert)
    // {
    //     if (!parent::beforeSave($insert)) {
    //         return false;
    //     }

    //     // Debug logging
    //     Yii::debug("Screenshot base64 data length: " . strlen($this->screenshot_base64), 'ticket');
        
    //     // Clean up base64 data if it's empty
    //     if (empty($this->screenshot_base64)) {
    //         $this->screenshot_base64 = null;
    //     }

    //     return true;
    // }

    // Helper method to get the image URL
    public function getScreenshotUrl()
    {
        if (empty($this->screenshot)) {
            return null;
        }

        // If the screenshot already includes the data URI scheme, return it as is
        if (strpos($this->screenshot, 'data:image/') === 0) {
            return $this->screenshot;
        }

        // Otherwise, add the data URI scheme
        try {
            // Determine image type
            $finfo = finfo_open();
            $binary = base64_decode($this->screenshot);
            $mimeType = finfo_buffer($finfo, $binary, FILEINFO_MIME_TYPE);
            finfo_close($finfo);

            return 'data:' . $mimeType . ';base64,' . $this->screenshot;
        } catch (\Exception $e) {
            Yii::error('Error getting screenshot URL: ' . $e->getMessage());
            return null;
        }
    }

    // public function beforeSave($insert)
    // {
    //     if (!parent::beforeSave($insert)) {
    //         return false;
    //     }

    //     try {
    //         // If screenshot is a base64 string, keep it as is
    //         if (is_string($this->screenshot) && base64_decode($this->screenshot, true)) {
    //             return true;
    //         }

    //         // If screenshot is an UploadedFile instance, convert to base64
    //         if ($this->screenshot instanceof UploadedFile) {
    //             $fileContent = file_get_contents($this->screenshot->tempName);
    //             if ($fileContent === false) {
    //                 throw new \Exception('Could not read uploaded file');
    //             }
                
    //             $this->screenshot = base64_encode($fileContent);
    //             return true;
    //         }

    //         return true;

    //     } catch (\Exception $e) {
    //         Yii::error('Screenshot save error: ' . $e->getMessage());
    //         return false;
    //     }
    // }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        try {
            // Handle file upload
            if ($this->uploadedFile instanceof UploadedFile) {
                Yii::info('Processing uploaded file in beforeSave', 'ticket');
                
                // Read file content
                $fileContent = file_get_contents($this->uploadedFile->tempName);
                if ($fileContent === false) {
                    throw new \Exception('Could not read uploaded file');
                }

                // Convert to base64 and store
                $this->screenshot = base64_encode($fileContent);
                Yii::info('File converted to base64 successfully', 'ticket');
            }

            return true;

        } catch (\Exception $e) {
            Yii::error('Screenshot save error: ' . $e->getMessage(), 'ticket');
            return false;
        }
    }

    // Add this validation method
    public function validateScreenshot($attribute, $params)
    {
        if ($this->$attribute instanceof UploadedFile) {
            // Additional validation if needed
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($this->$attribute->type, $allowedTypes)) {
                $this->addError($attribute, 'Only JPG, PNG and GIF files are allowed.');
            }
        }
    }
}
