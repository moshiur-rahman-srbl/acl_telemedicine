<?php
	
	namespace common\integration\Utility;
	
	class BrandInfoManipulation
	{
		public static function getCompanyWebsite()
		{
			$company_website = config('brand.contact_info.website');
			if( $company_website && !Str::contains($company_website,['https://', 'http://']) ){
				$company_website = 'https://' . $company_website;
			}
			return $company_website;
		}
	}