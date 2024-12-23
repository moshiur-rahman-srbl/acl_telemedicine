<?php

namespace common\integration\Utility;

use common\integration\BrandConfiguration;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class Helper
{

    const APP_DEV_ENV_PATTERN = '_dev';
    const APP_PROD_ENV_PATTERN = '_prod';
    const APP_PROV_ENV_PATTERN = '_prov';

    const APP_SIPAY_PROD_ENVIRONMENT = 'sp_prod';
    const APP_SIPAY_NGIX_PROD_ENVIRONMENT = 'sp_nginx';
    const APP_PROALPARA_ADMIN_PROD_ENVIRONMENT = 'pl_admin_prod';
    const APP_YEMEKPAY_ADMIN_PROD_ENVIRONMENT = 'yp_sr_admin_prod';
    const APP_YEMEKPAY_PROV_ENVIRONMENT = 'yp_prov';
	
	const APP_SIPAY_PROV_ENVIRONMENT = 'sp_prov';
	const APP_HEPSIPAY_PROD_ENVIRONMENT = 'hp_prod';

    public static function getPanelValue($panel_name = null)
    {
        $panel_values = [
            BrandConfiguration::PANEL_USER => 1, // 'user' key form Constants Value of Panel
            BrandConfiguration::PANEL_ADMIN => 2, // 'admin' key form Constants Value of Panel
            BrandConfiguration::PANEL_CCPAYMENT => 3, // 'ccpayment' key form Constants Value of Panel
            BrandConfiguration::PANEL_MERCHANT => 4, // 'merchant' key form Constants Value of Panel
        ];


        if (empty($panel_name)) {
            $panel_name = config('constants.defines.PANEL');
        }
        if (array_key_exists($panel_name, $panel_values)) {
            $panel_value = $panel_values[$panel_name];
        } else {
            $panel_value = false;
        }

        return $panel_value;
    }

    public static function isUserPanel()
    {
        return config()->get('constants.defines.PANEL') == BrandConfiguration::PANEL_USER;
    }

    public static function isDevServerEnvironment(): bool
    {
        return Str::of(config('app.env'))
            ->lower()
            ->contains(self::APP_DEV_ENV_PATTERN);
    }

    public static function isProdServerEnvironment():bool
    {
        return Str::of(app()->environment())
            ->lower()
            ->contains(self::APP_PROD_ENV_PATTERN);
    }

    public static function isProvServerEnvironment():bool
    {
        return Str::of(app()->environment())
            ->lower()
            ->contains(self::APP_PROV_ENV_PATTERN);

    }

    public static function isLocalServerEnvironment(): bool
    {
        return app()->environment('local');
    }

    public static function isNginxServerEnvironment(): bool
    {
        return app()->environment([
            self::APP_SIPAY_PROD_ENVIRONMENT, self::APP_SIPAY_NGIX_PROD_ENVIRONMENT
        ]);
    }

    public static function isSpNginxServerEnvironment(): bool
    {
        return Str::of(config('app.env'))
            ->lower()
            ->contains(self::APP_SIPAY_NGIX_PROD_ENVIRONMENT);
    }

    public static function collectionPagination($items, $request,$total, $perPage = 10, $page = 1): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
	    $items = $items instanceof Collection ? $items : Collection::make($items);;
        return new LengthAwarePaginator($items->forPage($page, $perPage), $total, $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
    }
	
	public static function emailSocialMediaUrl($image_name = null){
		
		$url = Storage::url('assets/email_template_images');
		
		if(BrandConfiguration::enableCustomEmailFooterImage()){
			$url .= '/'.config('brand.name_code').'/'.$image_name;
		}else{
			$url .= '/'.$image_name;
		}
		
		
		return $url;
	
	}

    public static function appDomain(): string
    {
        $app_url = config('app.url');
        try {
            $app_domain = parse_url($app_url)['host'];
        } catch (Throwable $throwable) {
            Exception::log($throwable, 'CURRENT_URL_FAILED_TO_PARSE');
        }

        return $app_domain ?? $app_url;
    }

    public static function isSpProvServerEnvironment(): bool
    {
        return Str::of(config('app.env'))
            ->lower()
            ->contains(self::APP_SIPAY_PROV_ENVIRONMENT);
    }
	
	/**
	 * Retry a callable function multiple times with optional backoff.
	 *
	 * @param int $times The maximum number of attempts.
	 * @param callable $callback The function to call.
	 * @param int $sleepMilliseconds Time to sleep between attempts (in milliseconds).
	 * @param callable|null $ignore A function to determine if the exception should be ignored.
	 * @param array $exceptions An array of exception classes to retry on.
	 * @return mixed The return value of the callback.
	 * @throws \Throwable If the maximum attempts are reached and not retried.
	 */
	
	public static function reTry(
		int $times,
		callable $callback,
		int $sleepMilliseconds = 0,
		&$attempt_counter = 0,
		callable $ignore = null,
		array $exceptions = []
	){
		
		$attempts = 0;
		$backoff = [];
		
		if (Arr::isOfType($times)) {
			$backoff = $times;
			$times = Arr::count($times) + 1;
		}
		
		beginning:
		$attempts++;
		$times--;
		$attempt_counter++;
		
		try {
			return $callback($attempts);
		} catch (\Throwable $e) {
			
			if ($ignore && $ignore($e)) {
				throw $e;
			}
			
			$shouldRetry = empty($exceptions) || Arr::isAMemberOf(get_class($e), $exceptions);
			
			if ($times < 1 || !$shouldRetry) {
				throw $e;
			}
			
			$sleepMilliseconds = $backoff[$attempts - 1] ?? $sleepMilliseconds;
			if ($sleepMilliseconds) {
				
				self::usleep(
					\common\integration\Utility\Str::value($sleepMilliseconds, $attempts, $e)
				);
			}
			
			goto beginning;
		}
	}
	
	public static function usleep(int $microseconds)
	{
		$microseconds = $microseconds * 1000;
		usleep($microseconds);
	}


}