<?php

namespace App\Utils;

use App\Models\AccountManager;
use App\Models\Merchant;
use common\integration\BrandConfiguration;

class AccountManagerMerchantFilter
{

    public $exists = false;
    public $merchant_ids = [];
    public $requested_merchant_ids = '';
    public $filtered_merchant_ids = [];
    public $requested_merchant_user_ids = '';
    public $filtered_merchant_user_ids = [];
    public $need_merchant_user_id = false;
    public $need_merchant_keys = false;
    public $merchant_keys = [];
    public $has_filtered_merchant_ids = true;
    public $set_new_search_params = false;
    public $request_manager_merchant = false;
    public $request_manager_merchant_list = [];
    private $request;


    public function filterAccountManagerMerchant($authObj, $request, &$search, &$merchantObj = null, $need_merchant_user_id = false, $requested_merchant_user_ids = false, $set_new_search_params = false, $search_key = '', $requested_manager_merchant = '')
    {

        $this->request = $request;


        if (BrandConfiguration::restrictMerchantByAccountManager() &&
            isset($authObj->is_merchant_restricted_by_account_manager) && $authObj->is_merchant_restricted_by_account_manager == 1) {

            if($set_new_search_params && !empty($search_key)){
                $this->set_new_search_params = true;
                $this->setSearchParams($search, false, $search_key);
            }

            if(!empty($requested_manager_merchant)){
                $this->request_manager_merchant = true;
                $this->request_manager_merchant_list = $requested_manager_merchant;
            }

            $this->exists = true;

            if (isset($request->merchantid)) {
                $this->requested_merchant_ids = $request->merchantid;
            } elseif (isset($request->merchant_id)) {
                $this->requested_merchant_ids = $request->merchant_id;
            }

            $this->need_merchant_user_id = $need_merchant_user_id;


            $merchant_id_list = AccountManager::query()
                ->where('user_id', $authObj->id)
                ->pluck('merchant_id')->toArray();

            if ($merchantObj instanceof Merchant) {
                $merchantObj->merchant_ids = !empty($merchant_id_list) && count($merchant_id_list) > 0 ? $merchant_id_list : [-1];
            }


            $filtered_merchant_ids = [];
            $filtered_merchant_user_ids = [];
            $requested_merchant_user_id_list = [];


            if (!empty($this->requested_merchant_ids) && !$requested_merchant_user_ids) {
                if (is_array($this->requested_merchant_ids)) {

                    foreach ($this->requested_merchant_ids as $merchant_id) {
                        if (in_array($merchant_id, $merchant_id_list)) {
                            $filtered_merchant_ids[] = $merchant_id;
                        }

                    }

                } else {
                    if (in_array($this->requested_merchant_ids, $merchant_id_list)) {
                        $filtered_merchant_ids[] = $this->requested_merchant_ids;
                    }
                }
            } else {
                $filtered_merchant_ids = $merchant_id_list;
            }


            if ($this->need_merchant_user_id && count($filtered_merchant_ids) > 0) {
                $filtered_merchant_user_ids = Merchant::query()
                    ->whereIn('id', $filtered_merchant_ids)
                    ->pluck('user_id')->toArray();
            }


            if($requested_merchant_user_ids){
                $this->requested_merchant_user_ids = $request->merchantid ?? $filtered_merchant_user_ids;
            }


            if ($this->requested_merchant_user_ids && count($filtered_merchant_user_ids) > 0) {

                if (is_array($this->requested_merchant_user_ids)) {
                    foreach ($this->requested_merchant_user_ids as $merchant_user_id) {
                        if (in_array($merchant_user_id, $filtered_merchant_user_ids)) {
                            $requested_merchant_user_id_list[] = $merchant_user_id;
                        }
                    }
                } else {
                    if (in_array($this->requested_merchant_user_ids, $filtered_merchant_user_ids)) {
                        $requested_merchant_user_id_list[] = $this->requested_merchant_user_ids;
                    }
                }

            }


            // merchant id settings
            if (!empty($filtered_merchant_ids) && count($filtered_merchant_ids) > 0) {
                $this->filtered_merchant_ids = $filtered_merchant_ids;
                $this->has_filtered_merchant_ids = true;
            } else {
                $this->filtered_merchant_ids = [-1];
                $this->has_filtered_merchant_ids = false;
            }


            // user id settings
            if ($this->need_merchant_user_id) {

                if ($this->requested_merchant_user_ids) {

                    if (!empty($requested_merchant_user_id_list) && count($requested_merchant_user_id_list) > 0) {
                        $this->filtered_merchant_user_ids = $requested_merchant_user_id_list;
                    } else {
                        $this->filtered_merchant_user_ids = [-1];
                    }

                } else {
                    if (!empty($filtered_merchant_user_ids) && count($filtered_merchant_user_ids) > 0) {
                        $this->filtered_merchant_user_ids = $filtered_merchant_user_ids;
                    } else {
                        $this->filtered_merchant_user_ids = [-1];
                    }
                }

            }

            // Merchant Keys

            if ($this->need_merchant_keys && !empty($merchantObj)) {

                $this->merchant_keys = $merchantObj->getAllMetchants()->pluck('merchant_key')->toArray();

            }

            $this->setSearchParams($search);

        }
    }

    public function modifySearchArray(&$search)
    {

        $this->setSearchParams($search, true);

    }

    private function setSearchParams(&$search, $by_request = false, $search_key='')
    {

        if ($this->exists) {
            if ($by_request) {

                if (isset($search['merchantid'])) {
                    $search['merchantid'] = $this->request->merchantid ?? [];
                }
                if (isset($search['merchant_id'])) {
                    $search['merchant_id'] = $this->request->merchant_id ?? [];
                }

            } else {

                if ($this->need_merchant_user_id) {

                    if (isset($search['merchantid'])) {
                        $search['merchantid'] = $this->filtered_merchant_user_ids;
                    }
                    if (isset($search['user_id'])) {
                        $search['user_id'] = $this->filtered_merchant_user_ids;
                    }


                } else {
                    if (isset($search['merchantid'])) {
                        $search['merchantid'] = $this->filtered_merchant_ids;
                    }
                    if (isset($search['merchant_id'])) {
                        $search['merchant_id'] = $this->filtered_merchant_ids;
                    }
                }

                if (!$this->need_merchant_user_id){
                    if (isset($search['user_id'])) {
                        $search['merchant_id'] = $this->filtered_merchant_user_ids;
                    }
                }

                if($this->need_merchant_keys){
                    $search['merchant_keys'] = $this->merchant_keys;
                }

                if($this->request_manager_merchant && !empty($this->request_manager_merchant_list)){

                    //dd($this->request_manager_merchant_list, $search['merchant_id']);
                    if (isset($search['merchant_id'])) {
                        $search['merchant_id'] =  array_intersect($this->request_manager_merchant_list, $search['merchant_id']);
                    }

                    if (isset($search['merchantid'])) {
                        $search['merchantid'] =  array_intersect($this->request_manager_merchant_list, $search['merchantid']);
                        if(empty($search['merchantid'])){
                            $search['merchantid'] = -1;
                        }
                    }

                }

            }


        }else{

            if($this->set_new_search_params && !empty($search_key)){
                $search[$search_key] = isset($this->request->$search_key) ? $this->request->$search_key : '';
            }

        }

    }


}
