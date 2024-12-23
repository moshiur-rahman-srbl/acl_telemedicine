<?php

namespace common\integration\Utility;

use App\Exports\ExportExcel;
use App\Exports\ExportExcelMultipleSheets;
use App\Http\Controllers\Traits\FileUploadTrait;
use common\integration\BrandConfiguration;
use common\integration\Exports\ReportExportView;
use common\integration\GlobalFunction;
use common\integration\Utility\Str;
use common\integration\Utility\Encode;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\File as LaravelFile;
class File
{
use FileUploadTrait;

    public const LOGO_TYPE_DEFAULT = 1;
    public const LOGO_TYPE_WHITE = 2;
    public const LOGO_TYPE_V_POS = 3;
    public const LOGO_TYPE_V_POS_WHITE = 4;

    const FORMAT_CSV = 1;
    const FORMAT_XLS = 2;
    const FORMAT_PDF = 3;

    public static function readCsv($file) {
        $arr = [];
        $header = NULL;
        $stream = fopen($file, "r");
        if($stream){
            while (!feof($stream)){
                $csv = fgetcsv($stream);
                if(!is_array($csv)){
                    continue;
                }
                if(is_null($header)){
                    $header = $csv;
                }
                elseif(is_array($header) && count($header) == count($csv)){
                    $arr[] = array_combine($header, $csv);
                }
            }
            fclose($stream);
        }
        return $arr;
    }


    public static function spreadsheetToArr($file, $headers_index = 0, $null = null, $should_calculate_formulas = true, $should_format = false, $mapped_header_arr = []):?array
    {
        $arr = [];
        $iterator = 0;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $tmp = $worksheet->toArray($null, $should_calculate_formulas, $should_format);
        }

        $headers = $tmp[$headers_index];

        if(!empty($mapped_header_arr)){
            $mapped_header = [];
            foreach ($headers as $header){
                if(array_key_exists($header,$mapped_header_arr)){
                    $mapped_header[] = $mapped_header_arr[$header];
                }else{
                    $mapped_header[] = $header;
                }
            }
            if(!empty($mapped_header)){
                $headers = $mapped_header;
            }
        }

        unset($tmp[$headers_index]);
        array_walk($tmp, function ($v) use ($headers, &$arr){$arr[] = array_combine($headers, $v);});

        $spreadsheet->__destruct();
        $spreadsheet = NULL;
        unset($spreadsheet);

        return $arr;
    }


    public static function arrToSpreadsheet($arr,$file,$writer_type = IOFactory::READER_XLSX)
    {
        $headings = Arr::keys(Arr::first($arr));
      //  array_unshift($arr, $headings);
        $callback = function ($k, &$v) {
            $v = Str::whiteTrim($v);
            is_numeric($v) ? $v = $v." ":"";
        };
        $arr = Arr::walkRecursive($arr, $callback);
        $arr = Arr::unsetRecursive($arr, "null");
/*        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($arr, NULL, 'A1');
        $writer = new Xlsx($spreadsheet);
        $writer->save($file);*/
        $list = collect($arr);
        return \Rap2hpoutre\FastExcel\Facades\FastExcel::data($list)->export($file);

    }



    public static function getStoragePath(string $disk = 'public')
    {
        return Storage::disk($disk)->path('');
    }

    public static function getFullStoragePath($path, $disk = 'public'){
        return Storage::disk($disk)->path($path);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isFileExists(string $path): bool
    {
        return file_exists($path);
    }

    /*
     * $is_full_path_return will work only when $should_store is true
     */
    public function fileExport($file_type, $data, $file_name, $header = null, $view_blade = null,
                               $should_store = false, $path = null, $disk = 'public', $title = null , $is_multi_sheets = false, $is_full_path_return = true, $is_fast_excel = false, $without_heading = false, $extras=[]){

        ini_set('max_execution_time', '500');
        $tmp_file_name = $file_name;
	    $file_name = self::manipulateBrandResourceDynamicPath($file_name);

        if ($file_type == "pdf_attacment"){
            $should_store = true;
            $file_type = "pdf";
        }

        $filePath = $file_name.'.'.$file_type;

        if ($should_store){
            if (!empty($path)){
                $filePath = $path.'/'.$file_name.'.'.$file_type;
                $this->makeDir($path, $disk);
            }

            if ($disk != 'public'){

            }
        }

        if ($file_type == "pdf"){

            ini_set("pcre.backtrack_limit", "5000000");

            $pdf = new \Mpdf\Mpdf();

            if (Url::isNonSecureConnection()){
                $pdf->curlAllowUnsafeSslRequests = true;
            }

            $html = view($view_blade,compact('data','header','file_name'))->render();
            $pdf->WriteHTML($html);

            if ($should_store){
                $pdfloc = Storage::disk($disk)->path($filePath);
                $pdf->Output($pdfloc, 'F');
                return $pdfloc;

            }else{
                $pdf->Output($file_name.".".$file_type, 'D');
                return true;
            }



        } else if ($file_type == "xls" || $file_type == "xlsx"|| $file_type == "csv"){
            $fileNameWithType =  $tmp_file_name.'.'.$file_type;
            if ($is_fast_excel){

                $tmp_path  = $path . '/' . $file_name . '.' . $file_type;
                $full_path = File::getFullStoragePath($tmp_path);

                $fastExcel = new FastExcel($data);
                if ($without_heading) {
                    $fastExcel->withoutHeaders();
                }

                $full_file_path = $fastExcel->export($full_path);

                return !empty($full_file_path) ? $tmp_path: '';
            }

            if( !empty($view_blade) && ($file_type == "xls" || $file_type == "xlsx") && ( isset($extras['multi_heading_excel_download']) && $extras['multi_heading_excel_download'] )) {

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new ReportExportView($view_blade, $data),
                    $fileNameWithType
                  );

            }

            if ($should_store){
                $pdfloc = Storage::disk($disk)->path($filePath);
                \Maatwebsite\Excel\Facades\Excel::store(new ExportExcel($data, $header), $filePath);
                return $is_full_path_return ? $pdfloc : $filePath;

            } else {
                if ($is_multi_sheets) {
                    return \Maatwebsite\Excel\Facades\Excel::download(new ExportExcelMultipleSheets($data, $header, $title), $fileNameWithType);
                } else {
                    return \Maatwebsite\Excel\Facades\Excel::download(new ExportExcel($data, $header), $fileNameWithType);
                }
            }

        }
    }

    public static function makeDirectory($directory, $permission = 0777, $is_recursive = false)
    {
        mkdir($directory, $permission, $is_recursive);
    }

    public static function getDirectory($path)
    {
        return dirname($path);
    }

    public static function isDirectory($directoryPath)
    {
        return is_dir($directoryPath);
    }

    /**
     * @param int $type
     * @return string
     */
    public static function getLogoUrl(int $type = self::LOGO_TYPE_DEFAULT):string
    {
        $default_logo_url = Storage::url(config('brand.logo'));
        $logo_url         = match ($type) {
            self::LOGO_TYPE_WHITE => self::isFileExists(Storage::path(config('brand.logo_white'))) ? Storage::url(config('brand.logo_white')) : '',
            self::LOGO_TYPE_V_POS => self::isFileExists(Storage::path(config('brand.logo_v_pos'))) ? Storage::url(config('brand.logo_v_pos')) : '',
            self::LOGO_TYPE_V_POS_WHITE => self::isFileExists(Storage::path(config('brand.logo_v_pos_white'))) ? Storage::url(config('brand.logo_v_pos_white')) : Storage::url(config('brand.logo_white')),
            default => $default_logo_url,
        };
        return !empty($logo_url) ? $logo_url : $default_logo_url;
    }

    public static function isFile($file){
        return is_file($file);
    }

    /**
     * @param $path file path not full path
     * @param $disk can be public or local
     * @return bool
     */
    public static function isFileExistsByDisk($path, $disk = 'public'){
        return Storage::disk($disk)->exists($path);
    }

    public static function extractFromZip($zipPath, $extractPath, $fileName){
        $status = false;
        $zipInstance = new \ZipArchive();
        $zipFile = $zipInstance->open($zipPath);
        if ($zipFile){
            $status = $zipInstance->extractTo($extractPath, $fileName);
            $zipInstance->close();
        }
        return $status;
    }

    public static function cleanDireactory(String $directoryPath)
    {
        return LaravelFile::cleanDirectory($directoryPath);
    }

    /**
     * @param $directoryPath file path not full path
     * @param $storage_disk can be public or local
     * @return bool
     */
    public static function removeDirectory(String $directoryPath) {
        return Storage::deleteDirectory($directoryPath);
    }

    public static function deleteFile(String $filePath) {
        if (Storage::exists($filePath)){
            return Storage::delete($filePath);
        }
        return false;
    }

    public static function base64Decode($data) {
        return base64_decode($data);
    }

    public static function getContents(String $file_url,bool $use_include_path = false,$context = null, int $offset = 0) {
        return file_get_contents($file_url, $use_include_path, $context, $offset);
    }

    public static function streamContextCreate(array $options) {
        return stream_context_create($options);
    }

    public function base64ToImageFile($file, $path, $fileExt = 'png', $file_name=null) {
        $file = Str::replace('data:image/png;base64,', '', $file);
        $file = Str::replace(' ', '+', $file);
        $imageName = time().'.'.$fileExt;
        if($file_name) {
            $imageName = $file_name .'.'.$fileExt;
        }
        $file = self::base64Decode($file);
        $filePath = $path ."/" . $imageName;
        $this->makeDir($path);
        $file_upload = $this->uploadFile($file, $filePath,null,false);
        if($file_upload) {
            return [
                "file_path" => $filePath
            ];
        }

        return false;
    }

    public static function getAllScriptFiles($dir) {
        $scriptFiles = [];
        $allowedExtensions = array('sh', 'js', 'css'); // Add any other script file extensions here

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $allowedExtensions)) {
                    $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $scriptFiles[] = $relativePath;
                }
            }
        }

        return $scriptFiles;
    }

	public static function cssJsFilePathConfiguration($path)
	{
		return str_replace( ['\/', "\\"], '/',
			str_replace(
				':/','://',
				trim(
					preg_replace('/\/+/', '/', $path), '/')
			)
		);
	}


    public static function writeToPhpArray($data, $filename) {
	    file_put_contents($filename, '');
        $phpArrayString = '<?php' . PHP_EOL . PHP_EOL;
        $phpArrayString .= '$hashes = ' . var_export($data, true) . ';' . PHP_EOL . PHP_EOL;
        $phpArrayString .= '?>';
        file_put_contents($filename, $phpArrayString);
    }

    public static function getBase64FileContent($base64file)
    {
        $status = true;
        $status_message = $file_content = '';
        $size = 0;

        $matches = Str::preg_match('/data:(.*?\/(.*?));base64,/', $base64file, true);
        $mimeTypes = $matches[1] ?? '';
        $file_extension = self::getbase64FileExtention($mimeTypes);

        if (!$file_extension) {
            $status = false;
            $status_message = __('Unsupported file type');
        } else {
            $base64encoded_content = preg_replace('/data:(.*?\/(.*?));base64,/', '', $base64file);
            $file_content = Encode::base64Decode($base64encoded_content);
            $size = (Str::len(Str::replace('=', '', $file_content)) * (3 / 4))/1048576; //converted to mb
        }

        return [$status, $status_message, $file_content, $file_extension, $size];
    }

    public static function getbase64FileExtention($mimeType)
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        $extension = isset($mimeTypes[$mimeType]) ? $mimeTypes[$mimeType] : null;
        return $extension;
    }


	public static function manipulateBrandResourceDynamicPath($file_name, $folder_prefix = 'dynamic-content')
	{

		if(!Storage::exists($folder_prefix)){
			(new class {
				use FileUploadTrait;
			})->makeDir($folder_prefix);
		}

		if(!Str::contains($file_name, ["/", "\\"])){
			$file_name = $folder_prefix.DIRECTORY_SEPARATOR.$file_name;
		}

		return $file_name;
	}

    const FORMAT_LIST = [
        self::FORMAT_CSV => 'csv',
        self::FORMAT_XLS => 'xlsx',
        self::FORMAT_PDF => 'pdf'
    ];

}
