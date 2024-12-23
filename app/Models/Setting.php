<?php

namespace App\Models;

use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use common\integration\Traits\QueryMethodOverride;
use common\integration\Override\Model\CustomBaseModel as Model;

class Setting extends Model
{
    use QueryMethodOverride;
    const ADMIN_COMPANY_ID = 2;
    const ADMIN_SETTING_KEY = "admin_site_setting";
    const BTRNS_COMPANY_CODE_KEY = "admin_btrns_company_code";
    const FULL_COMPANY_NAME_KEY = "admin_full_company_name";
    const TAX_NUMBER_KEY = "admin_tax_number";
    const TAX_OFFICE_KEY = "admin_tax_office";
    const HELP_DOC = "help_doc";

    protected $fillable = [
        'title','logo_path','company_id', 'admin_name', 'admin_phone','company_address','footer','footer_tr','favicon_path','advertisment'
    ];
    public function getSetting(){
        $setting = $this->first();

        return $setting;
    }

    public function getSettings(){
        return self::query()->get();
    }

    public function getSettingByCompanyId($companyid){
        $search['company_id'] = $companyid;
        return $this->filterSetting($search)->first();
    }

    public function setting($key, $default)
    {
        $search['key'] = $key;
        $setting = $this->filterSetting($search)->first();
        return !empty($setting)? $setting->value : $default;
    }

    private function filterSetting($search, $fresh = false){
        $is_collection = true;
        $queryData = collect(GlobalFunction::getBrandCache(self::ADMIN_SETTING_KEY));

        if ($fresh || count($queryData) == 0){
            $is_collection = false;
            $queryData = self::query();
        }

        if (isset($search['id'])){
            $is_collection ? $queryData = $queryData->where('id', $search['id']) : $queryData->where('id', $search['id']);
        }

        if (isset($search['key'])){
            $is_collection ? $queryData = $queryData->where('key', $search['key']) : $queryData->where('key', $search['key']);
        }

        if (isset($search['company_id'])){
            $is_collection ? $queryData = $queryData->where('company_id', $search['company_id']) : $queryData->where('company_id', $search['company_id']);
        }

        return $queryData;
    }

    public function findById($id){
        $search['id'] = $id;
        return $this->filterSetting($search)->first();
    }

    public function updateSetting($id, array $data){
        return self::query()->where('id', $id)->update($data);
    }

    public function updateSettingWithKey($data_as_key_value, $condition_columns, $update_columns)
    {
        return self::upsert($data_as_key_value, $condition_columns, $update_columns);
    }

    public function getPreparedKeyValueData($request, $comapny_id){

        $data_as_key_value = [];

        if (!empty($request->btrns_company_code)) {
            $data_as_key_value[] = [
                'key'          => self::BTRNS_COMPANY_CODE_KEY,
                'value'        => $request->btrns_company_code,
                'display_name' => __("BTRANS Company Code"),
                'type'         => 'text',
                'order'        => 12,
                'company_id'   => $comapny_id
            ];
        }
        if (!empty($request->full_company_name)) {
            $data_as_key_value[] = [
                'key'          => self::FULL_COMPANY_NAME_KEY,
                'value'        => $request->full_company_name,
                'display_name' => __("Full Company Name"),
                'type'         => 'text',
                'order'        => 13,
                'company_id'   => $comapny_id
            ];
        }
        if (!empty($request->tax_number)) {
            $data_as_key_value[] = [
                'key'          => self::TAX_NUMBER_KEY,
                'value'        => $request->tax_number,
                'display_name' => __("TAX Number"),
                'type'         => 'text',
                'order'        => 14,
                'company_id'   => $comapny_id
            ];
        }
        if (!empty($request->tax_office)) {
            $data_as_key_value[] = [
                'key'          => self::TAX_OFFICE_KEY,
                'value'        => $request->tax_office,
                'display_name' => __("TAX Office"),
                'type'         => 'text',
                'order'        => 15,
                'company_id'   => $comapny_id
            ];
        }
        if (BrandConfiguration::isAllowedSiteSettingHelpDocument() && !empty($request->help_doc) && is_string($request->help_doc)) {
            $data_as_key_value[] = [
                'key'          => self::HELP_DOC,
                'value'        => $request->help_doc,
                'display_name' => __("Help Doc"),
                'type'         => 'text',
                'order'        => 16,
                'company_id'   => $comapny_id
            ];
        }
        return $data_as_key_value;
    }
}
