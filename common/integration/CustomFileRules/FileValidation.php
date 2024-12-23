<?php

namespace common\integration\CustomFileRules;

use App\Models\UserSetting;
use common\integration\Utility\Encode;
use Illuminate\Contracts\Validation\Rule;

class FileValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    const USER_PROFILE_IMAGE_UPLOAD = 'user_profile_upload';
    const BASE_64_IMAGE_SOURCE = 'is_base64';
    const STRING_BASE64_SOURCE = 'is_string_base64';

    protected array $acceptableTypes = [];
    protected $file_size = 0;
    protected $error_message = '';
    protected $file_type_validation_status = true;
    protected $file_type_validation_error_message = '';
    protected $file_size_validation_status = true;
    protected $file_size_validation_error_message = '';
    protected $request_source;
    protected $base64_validation_error_message='';

    public function __construct(array $acceptableTypes = [], $size = 0, $request_source = '')
    {
        $this->acceptableTypes = $acceptableTypes;
        $this->file_size = $size;
        $this->request_source = $request_source;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;
        $file_type_validation_error_message = __('Acceptable :attribute format\'s are :accept_type', ['attribute' => $attribute, 'accept_type' => implode(', ', $this->acceptableTypes)]);
        $file_size_validation_error_message = __(':attribute size can not be greater than :file_size MB', ['attribute' => $attribute, 'file_size' => $this->file_size]);
        $base64_validation_error_message = __(':attribute field is not a valid Base64 encoding.', ['attribute' => $attribute]);

        if(is_file($value)){
            if(!in_array($value->getClientOriginalExtension(), $this->acceptableTypes)){
                $result = false;
                $this->file_type_validation_status = false;
                $this->error_message = $this->file_type_validation_error_message = $file_type_validation_error_message;
            }
            if($this->file_size != 0){
                $request_file_size = (($value->getSize()) / 1024)/1024;
                if($request_file_size >= $this->file_size){
                    $result = false;
                    $this->file_size_validation_status = false;
                    $this->error_message = $this->file_size_validation_error_message = $file_size_validation_error_message;
                }
            }
            if(!$this->file_type_validation_status && !$this->file_size_validation_status){
                $this->error_message = $this->file_type_validation_error_message.' . '.$this->file_size_validation_error_message;
            }
        }elseif ($this->request_source == self::STRING_BASE64_SOURCE ){
                if (empty($value)) {
                    $this->error_message = $this->base64_validation_error_message = $base64_validation_error_message;
                    $result = false;
                }
                $checkBase64String = Encode::base64Decode($value, true) !== false && Encode::base64Encode(Encode::base64Decode($value, true)) === $value;
                if(!$checkBase64String){
                    $result = false;
                    $this->error_message = $this->base64_validation_error_message = $base64_validation_error_message;
                }
        }else{

            if($this->request_source == self::USER_PROFILE_IMAGE_UPLOAD || $this->request_source == 'is_base64'){
                if (!preg_match('/^data:image\/(\w+);base64,/', $value)) {
                    $result = false;
                    $this->file_type_validation_status = false;
                    $this->error_message = $this->file_type_validation_error_message = $file_type_validation_error_message;
                }
                if($this->file_size != 0){
                    if (((int)(strlen(rtrim($value, '=')) * 3 / 4)) / 1048576 >= $this->file_size) {
                        $result = false;
                        $this->file_size_validation_status = false;
                        $this->error_message = $this->file_size_validation_error_message = $file_size_validation_error_message;
                    }
                    if(!$this->file_type_validation_status && !$this->file_size_validation_status){
                        $this->error_message = $this->file_type_validation_error_message.' . '.$this->file_size_validation_error_message;
                    }
                }
            }


        }


        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error_message;
    }
}
