<?php
namespace common\integration;
use Google\Cloud\Vision\VisionClient;

class ImageProcessing{
    private $creadentials ='';
    protected $replace_string = [
        'CARD',
        'IDENTITY',
        'TURKEY',
        'OF',
        'REPUBLIC',
        'KARTI',
        'KİMLİK',
        'CUMHURİYETİ',
        'TÜRKİYE',

        'PASAPORT',
        'Türü/Type',
        'Ulke',
        'KoduiCode',
        'af',
        'huing',
        'State',
        'Pasaport',
        'No./Poport',
        'No',
    ];

    protected $card_pattern = [
        'Id' => [
            'eantity',
        ],
        'sur_name' => [
            'Sumame',
        ],
        'given_name' => [
            'Given Name',
        ],
    ];

    protected $passport_pattern = [
        'Id' => [
            'TUR',
        ], 
        'sur_name' => [
            'Sumame',
        ],
        'given_name' =>[
            'nationality',
            'Uyuguationolity',
        ],
    ];

    public function __construct($google_redentials)
    {
        $this->creadentials = $google_redentials;
    }

    public function googleImageProcessing($file,$path){
        
        $file_location = $this->fileLocation($file,$path);
        $google_response = $this->googeResponse($file_location);
        $this->fileRemove($file_location);
   
        if($google_response){    
            if (strpos($google_response, 'CARD') !== false) {
                // CARD
                $string = str_replace($this->replace_string,' ',$google_response);
                $id = $this->getDetailsFromString($string,$this->patternToString($this->card_pattern['Id']));
                $sur_name = $this->getDetailsFromString($string,$this->patternToString($this->card_pattern['sur_name']));
                $given_name = $this->getDetailsFromString($string,$this->patternToString($this->card_pattern['given_name']));
            }else{
                // PASSPORT
                $string = str_replace($this->replace_string,' ',$google_response);
                $id = $this->getDetailsFromString($string,$this->patternToString($this->passport_pattern['Id']));
                $sur_name = $this->getDetailsFromString($string,$this->patternToString($this->passport_pattern['sur_name']));
                $given_name = $this->getDetailsFromString($string,$this->patternToString($this->passport_pattern['given_name']));
               
            }

            $actual_name = '';
            if($given_name != __('Not Found')){
                $actual_name .= $given_name.' ';
            }

            if($sur_name != __('Not Found')){
                $actual_name .= $sur_name.' ';
            }

            return [
                'card' => $id,
                // 'sur_name' => $sur_name,
                // 'given_name' => $given_name,
                'actual_name' => $actual_name !='' ? $actual_name : __('Not Found') ,
            ];
        }
        return false;
    }
    public function patternToString(array $pattern){
        $pattern = implode('|', $pattern);
        return "/{$pattern}/i";
    }
    public function getDetailsFromString($string,$pattern){
        $new_line = nl2br($string);
        $split_array = explode('<br />',$new_line);
        $address_array = [];
        if(sizeof($split_array)){
            foreach($split_array as $key => $val){
                $val_string = trim(preg_replace('/\s\s+/', '', $val));
                if(preg_match($pattern, $val_string)){
                    return  trim(preg_replace('/\s\s+/', '',$split_array[$key+1]));
                }
            }
        }
        return __('Not Found');
    }
    public function fileRemove($file_location){
        if(file_exists($file_location)){
            unlink($file_location );
        }
    }
    public function googeResponse($file_location){
        $credential_data = json_decode($this->creadentials,true);
        $client = new VisionClient(['keyFile' => $credential_data ]);

        // Annotate an image, detecting faces.
        $annotation = $client->image(
            fopen($file_location, 'r'),
            ['TEXT_DETECTION']
        );
        $response = $client->annotate($annotation);
        if($response->info()){

            $info = $response->info();       
            return $info['textAnnotations'][0]['description'];


        }else{
            return false;
        }
      

    }
    public function fileLocation($file,$destinationPath){
        $filename = str_random(8).$file->getClientOriginalName();
        $uploadSuccess = $file->move($destinationPath,$filename);
        $file_location = $destinationPath.'/'.$filename;
        return $file_location;
    }
}