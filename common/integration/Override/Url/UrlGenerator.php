<?php
	
	namespace common\integration\Override\Url;
	
	use common\integration\Brand\Configuration\All\Mix;
	use common\integration\BrandConfiguration;
	use common\integration\Utility\Url;
	use Illuminate\Http\Request;
	use Illuminate\Routing\RouteCollectionInterface;
	use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
	use Symfony\Component\Routing\Exception\RouteNotFoundException;
	
	class UrlGenerator extends BaseUrlGenerator
	{
		
		public function __construct(RouteCollectionInterface $routes, Request $request, $assetRoot = null)
		{
			parent::__construct($routes, $request, $assetRoot);
		}
		
		public function route($name, $parameters = [], $absolute = true)
		{
			if (! is_null($route = $this->routes->getByName($name))) {
				
				if(BrandConfiguration::call([Mix::class, 'allowUrlParamsEncryption'])){
					$parameters = Url::getRouteParamEncryptionDecryption($parameters, 'encode');
				}
				
				return $this->toRoute($route, $parameters, $absolute);
			}
			
			throw new RouteNotFoundException("Route [{$name}] not defined.");
		}
		
	}