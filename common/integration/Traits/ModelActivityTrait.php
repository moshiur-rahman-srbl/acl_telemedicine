<?php
	
	namespace common\integration\Traits;
	use common\integration\Brand\Configuration\Backend\BackendMix;
	use common\integration\BrandConfiguration;
	use common\integration\ManageLogging;
	use common\integration\ManipulateDate;
	use common\integration\Models\AuditTrail;
	use common\integration\Utility\Arr;
	use common\integration\Utility\Exception;
	use common\integration\Utility\Json;
	use common\integration\Utility\Str;
	use Illuminate\Database\Eloquent\Model;
	
	trait ModelActivityTrait
	{
		/*
		 * THE IGNOREABLE TABLE LIST
		 */
		
		private static function ignoreAbleTablesList()
		{
			$table_list = [
				'audit_trails',
				'requests',
				'requests_data',
				'tmp_payment_records',
				'tmp_object_storage',
				'sales',
				'tmp_sale_automations',
				'sale_asynchronous_processes',
				'tmp_payment_record_actions',
				'transactionable',
				'sale_integrators',
				'sale_billings',
				'sale_properties',
				'sale_tax_info',
				'sale_reports',
				'payment_reports',
				'sale_json',
			];
			
			return $table_list;
		}
		private static function needAudit() {
			return BrandConfiguration::call([BackendMix::class, 'enableAuditTrails']);
		}
		
		private static function isStringValue($value)
		{
			return Str::isString($value);
		}
		
		private static function isTableAllowed()
		{
			return !Arr::isAMemberOf((new static)->getTable(), static::ignoreAbleTablesList());
		}
		protected function bootIfNotBooted()
		{
			if (! isset(static::$booted[static::class])) {
				
				static::$booted[static::class] = true;
				$this->fireModelEvent('booting', false);
				
				static::booting();
				static::boot();
				static::bootModelEventLogger();
				static::booted();
				
				$this->fireModelEvent('booted', false);
			}
			
		}
		
		protected static function bootModelEventLogger()
		{
			if(self::needAudit() && self::isTableAllowed()){
				try{
					$manage_log = new ManageLogging();
					$log_data['action'] = 'AUDIT_TRAIL';
					foreach (static::getRecordActivityEvents() as $eventName) {
							static::$eventName(function (Model $model) use ($eventName, $manage_log, $log_data) {
								$log_data['event_name'] = $eventName;
								if (self::needAudit() === true && !app()->runningInConsole()) {
									// dump((new static)->getTable(), $eventName);
									// dump($eventName, $model->toArray());
									// dd(static::prepareModelData($model, $eventName));
									$log_data['audit_trails_tbl_message'] = (new AuditTrail())->storeData
									(static::prepareModelData($model,
										$eventName));
								}
								$log_data['model_id'] = $model->id;
								$log_data['message'] = 'Data Store Successfully';
								$manage_log->createLog($log_data);
							});
						}
				}catch (\Exception $e){
					$log_data['message'] = Exception::fullMessage($e, true);
					$manage_log->createLog($log_data);
			
				}
			
			}
			
			return true;
		}
		
		/*
		 * PREPARE DATA
		 */
		
		private static function prepareModelData($model, $event): array
		{
			$to_now = ManipulateDate::toNow();
			$model_data = static::getModelAction($model, $event);
			$prepare_data = [];
			$auth_user = auth()->user() ? auth()->user() : null;
			foreach ($model_data as $tbl_field => $value){
				if(
					(!empty($value)) || !empty($model->getOriginal($tbl_field))
					&&
					(self::isStringValue($value) && self::isStringValue($model->getOriginal($tbl_field)))
				){
					$prepare_data[] = [
						'tbl_name' => $model->getTable(),
						'tbl_field' => $tbl_field,
						'old_value' => $value,
						'new_value' =>  $model->getOriginal($tbl_field),
						'updated_by' => $auth_user ? $auth_user->id : 0,
						'tbl_item_id' => $model->id,
						'type' => self::getActionName($event),
						'session_id' => session()->getId(),
						'log_id' => app()->has('log_id') ? app()->get('log_id') : "",
						'email' => $auth_user ? $auth_user->email : 0,
						'ip_address' => (new class {
							use HttpServiceInfoTrait;
						})->getClientIpAddress(),
						'created_at' => $to_now,
						'updated_at' => $to_now,
						'created_at_int' => ManipulateDate::getDateFormat($to_now, 'Ymd'),
					];
				}
			}
			return $prepare_data;
		}
		
		private static function getModelAction($model, $eventName)
		{
			$get_data = [];
			if(
				$eventName == AuditTrail::EVENT_UPDATING ||
				$eventName == AuditTrail::EVENT_UPDATED
			){
				$get_data = $model->withoutRelations()->getDirty();
			}elseif(
				$eventName == AuditTrail::EVENT_DELETING ||
				$eventName == AuditTrail::EVENT_DELETED
			){
				$get_data = $model->withoutRelations()->toArray();
			}elseif(
				$eventName == AuditTrail::EVENT_CREATING ||
				$eventName == AuditTrail::EVENT_CREATED ||
				$eventName == AuditTrail::EVENT_SAVING ||
				$eventName == AuditTrail::EVENT_SAVED
			)
			{
				$get_data = $model->withoutRelations()->toArray();
			}
			
			return $get_data;
		}
		
		
		protected static function getRecordActivityEvents()
		{
			return [
				// AuditTrail::EVENT_RETRIEVED,
				// AuditTrail::EVENT_CREATING,
				AuditTrail::EVENT_CREATED,
				// AuditTrail::EVENT_SAVING,
				AuditTrail::EVENT_SAVED,
				// AuditTrail::EVENT_UPDATING,
				AuditTrail::EVENT_UPDATED,
				// AuditTrail::EVENT_DELETING,
				AuditTrail::EVENT_DELETED,
			];
		}
		
		protected static function getActionName($event)
		{
			
			return match (Str::toLower($event)) {
				// 'retrieved' => AuditTrail::ACTION_GET,
				// 'creating' => AuditTrail::ACTION_CREATE,
				'created' => AuditTrail::ACTION_CREATE,
				// 'saving' => AuditTrail::ACTION_CREATE,
				'saved' => AuditTrail::ACTION_CREATE,
				// 'updating' => AuditTrail::ACTION_UPDATE,
				'updated' => AuditTrail::ACTION_UPDATE,
				// 'deleting' => AuditTrail::ACTION_DELETE,
				'deleted' => AuditTrail::ACTION_DELETE,
			};
			
		}
	}