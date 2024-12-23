<?php

namespace common\integration\Traits;

use App\Models\RefundHistory;
use common\integration\ManageLogging;
use common\integration\Models\FinalizationReportAction;
use common\integration\Models\PaymentReportAction;
use common\integration\Models\Sale;
use common\integration\Models\Transaction;
use common\integration\Utility\Arr;
use common\integration\Utility\Exception;
use common\integration\Utility\Helper;
use common\integration\Utility\Json;

trait ModelUpdateListener
{

    private static $ignoreEvents = false;

    public static function setIgnoreEvents($ignore = true): void
    {
        self::$ignoreEvents = $ignore;
    }

    protected static function boot()
    {
        static::updated(function ($model) {
            $table_name = $model->getTable();

            if ($model->isDirty() && !self::$ignoreEvents && Arr::isAMemberOf($table_name, PaymentReportAction::accessibleTable())){

                if (!isset($model->id) && !isset($model->order_id)){
                    $subject = "EXCEPTION_UPDATE_SALE";
                    $message = "There is a exception on ". $table_name ." . please check log with email subject.";

                    (new ManageLogging())->createLog([
                        'action'    => $subject,
                        'data'      => Json::encode($model)
                    ]);
                    (new Exception())->sendExceptionEmail($subject, $message);
                }else{
                    if(isset($model->id)){
                        $reference_type = PaymentReportAction::REFERENCE_SALE_ID;
                        $reference_id = $model->id;
                    }else{
                        $reference_type = PaymentReportAction::REFERENCE_ORDER_ID;
                        $reference_id = $model->order_id;
                    }

                    (new PaymentReportAction())->insertData(
                        PaymentReportAction::TYPE_UPDATE,
                        $table_name,
                        $reference_id,
                        $reference_type,
                        null,
                        $model->getDirty()
                    );
                }

            }
            if ($model->isDirty() && !self::$ignoreEvents && Arr::isAMemberOf($table_name, FinalizationReportAction::accessibleTable())){

                try {
                    $reference_type = 0;
                    if($table_name == (new Transaction())->getTable()){
                        $reference_type = FinalizationReportAction::REFERENCE_TYPE_TRANSACTIONABLE;
                    } elseif($table_name == (new Sale())->getTable()){
                        $reference_type = FinalizationReportAction::REFERENCE_TYPE_SALE;
                    }elseif ($table_name = (new RefundHistory())->getTable()){
                        $reference_type = FinalizationReportAction::REFERENCE_TYPE_REFUND_HISTORY;
                    }
                    (new FinalizationReportAction())->insertData(
                        FinalizationReportAction::TYPE_UPDATE,
                        $table_name,
                        $model->id,
                        $reference_type,
                        null,
                        $model->getDirty()
                    );
                }catch (\Throwable $throwable){
                    Exception::log($throwable, "MODEL_UPDATE_LISTENER_FINALIZATION_REPORT_EXCEPTION");
                }
            }
        });
        parent::boot();

    }
}