<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Setting;
use App\Models\Statistics;
use common\integration\AppRequestValidation;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    use FileUploadTrait, CommonLogTrait;

    public function edit(Request $request)
    {
        if (BrandConfiguration::allowLogChecker()) {
            $statisticsInstance = new Statistics;
        }

        if ($request->isMethod('post')) {
            if (!isset($request->approved_by_checker)) {
                $validator = Validator::make($request->all(), AppRequestValidation::getSiteSettingValidationRules());
                if ($validator->fails()) {
                    flash($validator->errors()->first());
                    return back();
                }
            }
            if (BrandConfiguration::allowLogChecker()) {
                $is_application_online = 0;
                if (isset($request->is_application_online) && $request->is_application_online == 1) {
                    $is_application_online = 1;
                }
                $statisticsInstance->updateById(1, ['is_application_online' => $is_application_online]);
            }

            /**
             * maker checker specific part
             */
            $auth = auth()->user();

            $extras = [
                'attachment_folder' => "storage/setting",
            ];


            /**
             * maker checker specific part
             */

            return $this->update($request);


            return redirect()->back()->withInput();

        } else {

            $first_column = false;

            $cmsInfo = [
                'moduleTitle' => __("Site Setting"),
                'subModuleTitle' => __('Site Setting'),
                'subTitle' => __('xx')
            ];

            $dynamic_route = Config::get('constants.defines.APP_SITE_SETTINGS_EDIT');
            $isEdit = true;
            $setting = new Setting();

            $settings = $setting->getSettingByCompanyId(Auth::user()->company_id);
            $settings_extra_data = $this->getSettingsData($setting);
            if (!isset($setting->id)) {
                $settings = $setting->getSetting();
                $first_column = true;
            }
            $is_application_online = 0;
            if (BrandConfiguration::allowLogChecker()) {
                $is_application_online = $statisticsInstance->findById(1, 'is_application_online')->is_application_online;
            }
            $advertisement_code_show = true;
            if (!BrandConfiguration::isWalletPaymentExist()) {
                $advertisement_code_show = false;
            }
            return view('settings.edit_view', compact('is_application_online', 'cmsInfo', 'first_column', 'dynamic_route', 'isEdit', 'settings', 'advertisement_code_show', 'settings_extra_data'));
        }
    }

    private function getSettingsData($setting)
    {
        $data = [
            'btrns_company_code' => $setting->setting(Setting::BTRNS_COMPANY_CODE_KEY, null),
            'tax_number' => $setting->setting(Setting::TAX_NUMBER_KEY, null),
            'tax_office' => $setting->setting(Setting::TAX_OFFICE_KEY, null),
            'full_company_name' => $setting->setting(Setting::FULL_COMPANY_NAME_KEY, null),
        ];
        if (BrandConfiguration::isAllowedSiteSettingHelpDocument()) {
            $data = array_merge($data, ['help_doc' => $setting->setting(Setting::HELP_DOC, null)]);
        }
        return $data;
    }

    private function update(Request $request)
    {
        $setting = new Setting();
        $settingObj = $setting->findById($request->id);

        $comapny_id = Auth::user()->company_id;
        if (!empty($request->input('company_id'))) {
            $comapny_id = $request->input('company_id');
        }


        if (!empty($request->logo_path)) {

            $this->deleteFile($settingObj->logo_path);
            $logol = $this->uploadFile($request['logo_path'], 'storage/setting');

            if (isset($request->approved_by_checker)) {
                $logol = $request->logo_path ?? '';
            }

            Cache::put('logo_path', $logol);

        } else {
            $logol = $settingObj->logo_path;
        }
        if (!empty($request->favicon_path)) {
            $this->deleteFile($settingObj->favicon_path);
            $fav = $this->uploadFile($request['favicon_path'], 'storage/setting');

            if (isset($request->approved_by_checker)) {
                $fav = $request->favicon_path ?? '';
            }

            Cache::put('favicon_path', $fav);
        } else {
            $fav = $settingObj->favicon_path;
        }

        $data = [
            'title' => $request->title,
            'logo_path' => $logol,
            'admin_name' => $request->admin_name,
            'admin_phone' => $request->admin_phone,
            'company_address' => $request->company_address,
            'favicon_path' => $fav,
            'footer' => $request->footer,
            'footer_tr' => $request->footer_tr,
            'advertisment' => $request->advertisment,
            'company_id' => $comapny_id
        ];

        if (BrandConfiguration::isAllowedSiteSettingHelpDocument() && (!empty($request->help_doc) || $request->hasFile('help_doc'))) {

            $path_name = 'merchant/documents/help';
            if ($request->has('approved_by_checker') && $request->approved_by_checker == "approved_by_checker") {

                $doc_file = $request->help_doc;
                $doc_file_extension = $this->getExtension($doc_file);
                $path = $this->moveFile($doc_file, $path_name . "/document." . $doc_file_extension);

            } else {
                $doc_file = $request->file('help_doc');
                if (!empty($doc_file)) {
                    $doc_file_extension = $doc_file->getClientOriginalExtension();
                    $path = $this->uploadFile($doc_file, $path_name, 'document' . '.' . $doc_file_extension);
                }
            }
            if (!empty($path)) {
                $request->help_doc = $path_name . '/document' . '.' . $doc_file_extension;
            }
        }

        $data_as_key_value = $setting->getPreparedKeyValueData($request, $comapny_id);


        $settingedit = $setting->updateSetting($settingObj->id, $data);

        if (count($data_as_key_value) > 0) {
            $setting->updateSettingWithKey($data_as_key_value, ['key'], ['value', 'display_name']);
        }

        GlobalFunction::setBrandCache('title', $request->title, null);

        $footers = ['footer_en' => $request->footer, 'footer_tr' => $request->footer_tr];


        GlobalFunction::setBrandCache('footer', $footers, null);

        if (!empty($request->integration) || $request->hasFile('integration')) {
            $path_name = 'merchant/integration';

            if ($request->has('approved_by_checker') && $request->approved_by_checker == "approved_by_checker") {
                $file = $request->integration;
                $path = $this->moveFile($file, $path_name . "/integration.zip");
            } else {
                $file = $request->file('integration');
                $path = $this->uploadFile($file, $path_name, 'integration.zip');
            }

        }


        if ($settingedit) {
            GlobalFunction::setBrandCache(Setting::ADMIN_SETTING_KEY, $setting->getSettings(), null);
            flash(__('The record has been updated successfully!'), 'success');
        }
        return back();
    }


    public function destroy($id)
    {
        //
    }
}
