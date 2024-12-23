<?php
	
	namespace common\integration\Override\Headers;
	
	use common\integration\Utility\Str;
	
	class ManipulateRequestHeader
	{
		private $request_or_response;
		private $header_name = '';
		private $prepare_policies= '';
		
		
		const CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
		const PERMISSION_POLICY = 'Permissions-Policy';
		
		
		
		public function __construct($request_or_response, $header_name)
		{
			$this->request_or_response = $request_or_response;
			$this->header_name = $header_name;
			$this->prepare_policies = $this->{Str::camelCase($header_name)}();
			
		}
		
		
		public function setHeader(): void
		{
			
			$this->request_or_response->headers->set(
				$this->header_name,
				$this->getPolicy()
			);
			
		}
		
		private function ContentSecurityPolicy(): array
		{
			return [
				'worker-src' => ["*"],
				'child-src' => ["*"],
				'img-src' => ["*","data:"],
				'default-src' => ["*", "'self'", "'unsafe-inline'","'unsafe-eval'", "data:","gap:","content:"],
				'style-src' => ["*", "'unsafe-inline'", "'self'"],
			];
		}
		
		private function permissionsPolicy(): array
		{
			return [
				'autoplay=(self), camera=(), encrypted-media=(self), fullscreen=(), geolocation=(self), gyroscope=(self), magnetometer=(), microphone=(), midi=(), payment=(), sync-xhr=(self), usb=()'
			];
		}
		
		private function isEnableKeyPair(): bool
		{
			
			return !($this->header_name == self::PERMISSION_POLICY);
			
		}
		
		private function getPolicy()
		{
			return collect($this->prepare_policies)
				->filter()
				->map(function($value, $key){
					
					$key = $this->isEnableKeyPair() ?"{$key} ":"";
					return $key.collect($value)
							->filter()
							->implode(' ');
				})->implode(' ; ');
		}
		
	}