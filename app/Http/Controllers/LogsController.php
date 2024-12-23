<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\BrandConfiguration;
use common\integration\GrayLog;
use common\integration\ManageLogging;
use common\integration\Utility\Encode;
use common\integration\Utility\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use common\integration\Utility\Str;

class LogsController extends Controller
{
    private $available_panels = [
        'admin',
    ];

    private $sms_email_panel = 'SMS/Email';
    private $selected_panels = [];

    /**
     * Show index page.
     *
     * @param Request $request
     * @return bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function index(Request $request)
    {
        // General settings
        $selfUrl = route(config('constants.defines.APP_LOGS_INDEX'));
        $data['cmsInfo'] = [
            'moduleTitle' => __("Management"),
            'subModuleTitle' => __("Logs"),
            'subTitle' => __("Logs")
        ];

        // Set search data for logs filtration and get logs
        $search = $this->handleSearchData($request);
        $page_limit = $request->page_limit ?? 100;

        $this->available_panels = $this->getAvailablePanels();

        // Set selected panels
        $this->selected_panels = !empty($request->panel) ? $request->panel : $this->available_panels;

        if ($request->has('search')) {

            $validator = $this->validateSearchData($search);

            if ($validator->fails()) {
                flash($validator->errors()->first(), 'danger');
                return redirect()->back();
            } else {
                // Set logs
                $logs = $this->getLogsData($search);
                $paginated_logs = !empty($logs) ? $this->paginate($request, $logs, $page_limit) : $logs;

                // Export file
                if ($request->has('export')) {
                    return $this->export($logs, $search);
                }
                if ($request->has('downloadLogFile')) {
                    return $this->downloadLogFile($search);
                }
            }
        }

        return view('logs.index')->with([
            'selfUrls' => $selfUrl,
            'cmsInfo' => $data['cmsInfo'],
            'available_panels' => $this->available_panels,
            'search' => $search,
            'page_limit' => $page_limit,
            'logs' => $paginated_logs ?? [],
        ]);
    }

    private function validateSearchData($searchData)
    {
        $fromDate = Carbon::parse($searchData['from_date']);
        $toDate = Carbon::parse($searchData['to_date']);
        $searchData['date_range_diff'] = $fromDate->diffInDays($toDate) + 1;

        return Validator::make($searchData, [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date'],
            'panel' => 'required|array',
            'date_range_diff' => ['required', 'integer', 'max:2']
        ], [
            'panel.required' => __('The :object field is required', ['object' => 'Panel']),
            'from_date.required' => __('The :object field is required', ['object' => __('From Date')]),
            'date_range_diff.*' => __('Date range should be maximum 2 days.')
        ]);
    }

    /**
     * Paginate data from array.
     *
     * @param Request $request
     * @param $data - data for pagination
     * @param $page_limit
     * @return LengthAwarePaginator
     */
    public function paginate(Request $request, $data, $page_limit)
    {
        $current_page = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($data);
        $current_page_results = $collection->slice(($current_page * $page_limit) - $page_limit, $page_limit)->all();
        $paginated_results = new LengthAwarePaginator($current_page_results, count($collection), $page_limit);
        $paginated_results->setPath($request->url());

        return $paginated_results;
    }

    /**
     * Set and format search data.
     *
     * @param Request $request
     * @return mixed
     */
    private function handleSearchData(Request $request)
    {

        if (isset($request->from_date) && !empty($request->from_date)) {
            $date = explode('-', $request->from_date);
            $from_date = $date[0] ? trim($date[0]) : '';
            $to_date = $date[1] ? trim($date[1]) : '';

            $search['from_date'] = $from_date;
            $search['to_date'] = $to_date;
        } else {
            $search['from_date'] = $search['to_date'] = date('Y/m/d');
        }
        $search['search'] = $request->search ?? null;
        $search['panel'] = $request->panel ?? [];
        $search['search_phrase'] = $request->search_phrase ?? '';
        $search['daterange'] = $search['from_date'] . " - " . $search['to_date'];

        return $search;
    }

    /**
     * Get logs data by search data.
     *
     * @param $search
     * @param bool $cronjob
     * @return array
     */
    public function getLogsData($search, $cronjob = false)
    {
        $logs_list = [];
        $logs_data = [];

        $dates = $this->setDates($search['from_date'], $search['to_date']);
        $panels = $cronjob ? $this->available_panels : $this->selected_panels;
        foreach ($panels as $panel) {
            $panel = ($panel == 'user') || ($panel == $this->sms_email_panel) ? '' : $panel;
            $logs_list = array_merge($logs_list, $this->getLogsListFromPanel('/' . $panel, $dates));
        }

        $readLogDataFunction = BrandConfiguration::call([BackendAdmin::class, 'shouldSearchLogsViaNewFlow']) ?
            'readLogDataNew' : 'readLogData';

        foreach ($logs_list as $log) {
            $logs_data = array_merge($logs_data, $this->{$readLogDataFunction}($log, $search['search_phrase'], $cronjob));
        }

        return $logs_data;
    }

    /**
     * Set array of all dates by day between from_date and to_date.
     *
     * @param $date_from
     * @param $date_to
     * @return array
     */
    private function setDates($date_from, $date_to)
    {
        $format = 'Y-m-d';
        $date_from = date($format, strtotime($date_from));
        $date_to = date($format, strtotime($date_to));

        $dates = [$date_from];

        while (end($dates) < $date_to) {
            $dates[] = date($format, strtotime(end($dates) . ' +1 day'));
        }

        return $dates;
    }

    /**
     * Get all log file paths for selected panel by selected dates.
     *
     * @param $panel_name - panel where logs should be get from
     * @param $dates - dates for selecting log files
     * @return array
     */
    private function getLogsListFromPanel($panel_name, $dates)
    {
        $panel_storage_path = base_path() . '/storage/logs';

        $log_files = [];

        foreach ($dates as $date) {
            $formatted_date = preg_replace('/\//', '-', $date);
            $file_path = $panel_storage_path . '/laravel-' . $formatted_date . '.log';

            if (file_exists($file_path)) {
                $formatted_panel_name = preg_replace('/\//', '', $panel_name);
                $log_files[] = [
                    'file_path' => $file_path,
                    'panel' => empty($formatted_panel_name) ? 'user' : $formatted_panel_name,
                    'date' => $date
                ];
            }
        }

        return $log_files;
    }

    /**
     * Get all lines from log file.
     * If search phrase is not empty, get lines that contains search phrase.
     *
     * @param $log_file - array with log file data
     * @param $search_phrase
     * @param $first_result - set as true if only first result needed and false otherwise
     * @return array
     */
    private function readLogData($log_file, $search_phrase, $first_result)
    {
        $matches = [];
        $logs = [];

        $file_data = file_get_contents($log_file['file_path']);
        // $file_data = trim(preg_replace('/\s\s+/', ' ', file_get_contents($log_file['file_path'])));
        $pattern = '[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:",\.\?\\\]';
//        preg_match_all(
//            "/(\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\].*[\s\S]*?)(?=\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\]|$|{$pattern})/",
//            $file_data,
//            $matches
//        );

        preg_match_all(
            "/(\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\].*[\s\S]*?)(?=\[\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}\])/",
            $file_data,
            $matches
        );

        foreach ($matches[0] as $log_data) {

            $log_data = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }, $log_data);

            if (!empty($search_phrase)) {
                if (preg_match("/" . preg_quote($search_phrase, "/") . "/", $log_data)) {
                    $logs[] = [
                        'data' => Encode::urlDecode($log_data),
                        'panel' => $log_file['panel'],
                        'date' => $log_file['date']
                    ];

                    // Break the loop if only first result is needed
                    if ($first_result) {
                        break;
                    }

                }
            } else {
                $logs[] = [
                    'data' => Encode::urlDecode($log_data),
                    'panel' => $log_file['panel'],
                    'date' => $log_file['date']
                ];
            }

        }

        return $logs;
    }


    public function readLogDataNew(array $log_file, string $searchTerm, $first_result): array
    {
        $logs = [];
        $buffer = '';
        $entryStartPattern = '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/'; // Pattern for detecting log entry start

        if (($handle = fopen($log_file['file_path'], 'r')) !== false) {
            while (($line = fgets($handle)) !== false) {
                if (Str::preg_match($entryStartPattern, $line) && !empty($buffer)) {
                    $this->processBuffer($buffer, $searchTerm, $log_file, $logs);
                    $buffer = ''; // Reset the buffer for the new entry
                    if (!empty($searchTerm) && $first_result && !empty($logs)) {
                        break;
                    }
                }
                $buffer .= $line;
            }

            if (!empty($buffer)) {
                $this->processBuffer($buffer, $searchTerm, $log_file, $logs);
            }
            fclose($handle);
        }

        return $logs;
    }

    protected function processBuffer(string $logEntry, string $searchTerm, $log_file, array &$logs)
    {
        // Perform a case-insensitive search within the log entry
        if (Str::strIntPos($logEntry, $searchTerm) !== false) {
            $logEntry = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }, $logEntry);
            $logs[] = [
                'data' => Encode::urlDecode($logEntry),
                'panel' => $log_file['panel'],
                'date' => $log_file['date']
            ];
        }
    }


    /**
     * Export logs data to txt file.
     *
     * @param $data - export data
     * @param $search - search form data
     * @return bool|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export($data, $search)
    {
        $logData = [];
        $logData['action'] = 'Logs_REPORT_AT_ADMIN ';
        $logData['searchParems'] = $search;
        $this->createLog($this->_getCommonLogData($logData));

        $date_range = preg_replace('/\//', '-', $search['daterange']);
        $filename = !empty($search['search_phrase']) ?
            $search['search_phrase'] . '_' . $date_range . '.txt'
            : 'Logs_' . $date_range . '.txt';
        $contents = implode("\n", array_column($data, 'data'));

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $filename);
    }

    public function downloadLogFile($search)
    {
        $logData = [];
        $logData['action'] = 'download_Log_FileLog_AT_ADMIN ';
        $logData['searchParems'] = $search;
        $base_url = str_replace('admin', '', base_path());

        $panel = '/';
        $this->createLog($this->_getCommonLogData($logData));
        if ($search['from_date'] == $search['to_date'] && count($search['panel']) == 1) {
            if ($search['panel'][0] == 'admin') {
                $panel = '/admin';
            } elseif ($search['panel'][0] == 'ccpayment') {
                $panel = '/ccpayment';
            } elseif ($search['panel'][0] == 'merchant') {
                $panel = '/merchant';
            }
            $panel_storage_path = $base_url . strtolower($panel) . '/storage/logs';
            $formatted_date = preg_replace('/\//', '-', $search['to_date']);
            $file_path = $panel_storage_path . '/laravel-' . $formatted_date . '.log';
            if (file_exists($file_path)) {
                $headers = array(
                    'Content-type' => 'text/plain'
                );
                return \Response::download($file_path, $formatted_date . '-' . $search['panel'][0] . '.log', $headers);
            } else {
                flash(__('There is no File!'), 'danger');
                return back();
            }

        }
        flash(__('Please Select From and to same date and only one panel!'), 'danger');
        return back();
    }

    public function getAvailablePanels()
    {
        return $this->available_panels;
    }

    private function showLogFromGrayLog(Request $request)
    {
        $available_panels = $this->available_panels;
        $self_url = route(config('constants.defines.APP_LOGS_INDEX'));

        $cmsInfo = [
            'moduleTitle' => __("Management"),
            'subModuleTitle' => __("Logs"),
            'subTitle' => __("Logs")
        ];

        $search = $request->all();
        $search['panel'] = $request->input('panel', []);
        $search['search'] = $request->input('search', null);
        if ($request->has('daterange')) {
            $daterange = $request->input('daterange');
            [$from_date, $to_date] = Str::explode(' - ', $daterange);
        } else {
            $from_date = $to_date = Carbon::today()->format('Y/m/d');
            $search['daterange'] = "$from_date - $to_date";
        }

        $graylog = new GrayLog();
        $logs = $graylog->logSearch(
            $search + [
                'from_date' => $from_date,
                'to_date' => $to_date,
                'page_limit' => $request->get('page_limit', 100),
                'page' => $request->get('page', 1),
                'has_quires' => (bool)count($request->query)
            ]
        );
        $page_limit = $request->get('page_limit', 100);

        $logs_string = $logs->implode('message.message', '\n');

        return view(
            'logs.graylog',
            compact('cmsInfo', 'self_url', 'logs', 'search', 'page_limit', 'logs_string', 'available_panels')
        );
    }
}
