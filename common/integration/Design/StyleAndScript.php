<?php
	
	namespace common\integration\Design;
	
	
	use common\integration\BrandConfiguration;
    use common\integration\Utility\Arr;
	use Elhebert\SubresourceIntegrity\Sri;
	
	class StyleAndScript
	{
		public $css = [];
		public $js = [];
		
		const CSS = 'css';
		const JS = 'js';


        public function bootstrapV5MinCss($panel = "", $path = "")
        {
            if(!empty($panel) || !empty($path)){
                //put the path finder logic here
                $path = $path;
            }else {
                //default path
                $path = "adminca/assets/vendors/bootstrap/dist/css";
            }
            $file = "bootstrap-v5.min.css";
            $this->add($path, $file, self::CSS);
        }

        public function bootstrapV5MinCss_01($panel = "", $path = "")
        {
            if(!empty($panel) || !empty($path)){
                //put the path finder logic here
                $path = $path;
            }else {
                //default path
                $path = "assets/vendor/bootstrap/css/";
            }
            $file = "bootstrap-v5.min.css";
            $this->add($path, $file, self::CSS);
        }

        public function fontAwesomeMinCss($panel = "", $path = "")
        {
            if(!empty($panel) || !empty($path)){
                //put the path finder logic here
                $path = $path;
            }else {
                //default path
                $path = "adminca/assets/vendors/font-awesome/css";
            }
            $file = "font-awesome.min.css";
            $this->add($path, $file, self::CSS);

        }

        public function lineAwesomeMinCss($panel = "", $path = "")
        {
            if(!empty($panel) || !empty($path)){
                //put the path finder logic here
                $path = $path;
            }else {
                //default path
                $path = "adminca/assets/vendors/line-awesome/css";
            }
            $file = "line-awesome.min.css";
            $this->add($path, $file, self::CSS);

        }
		
		public function stylesCss()
		{
			if(!empty($panel) || !empty($path)){
				//put the path finder logic here
				$path = $path;
			}else {
				//default path
				$path = "assets/css";
			}
			$file = "styles.css";
			$this->add($path, $file, self::CSS);
		}

        public function themifyIconsCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/themify-icons/css";
            }
            $file = "themify-icons.css";
            $this->add($path, $file, self::CSS);
        }


        public function animateMinCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/animate.css";
            }
            $file = "animate.min.css";
            $this->add($path, $file, self::CSS);
        }


        public function toastrMinCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/toastr";
            }
            $file = "toastr.min.css";
            $this->add($path, $file, self::CSS);
        }

        public function bootstrapSelectMinCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/bootstrap-select/dist/css";
            }
            $file = "bootstrap-select_v1.14.0.min.css";
            $this->add($path, $file, self::CSS);
        }


        public function mainMinCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/css";
            }
            $file = "main.min.css";
            $this->add($path, $file, self::CSS);
        }
		
		public function loginCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "login.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customV5Css($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca".'/'.'assets/css';
			}
			$file = "custom-v5.css";
			$this->add($path, $file, self::CSS);
		}

        public function brandStylesColors($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = config('brand.styles.colors');
            }
            $file = "";
            $this->add($path, $file, self::CSS);
        }

        public function mainV5Css($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/css";
            }
            $file = "main-v5.css";
            $this->add($path, $file, self::CSS);
        }

        public function mainV5Css_01($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "assets/css";
            }
            $file = "main-v5.css";
            $this->add($path, $file, self::CSS);
        }


		

		
		public function newstyleCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "newstyle.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customCss_02($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "custom.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customIdentificationCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "customIdentification.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function forcedCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca/assets/css";
			}
			$file = "forced_css.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function bootstrapMinCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/bootstrap/css";
			}
			$file = "bootstrap.min.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function bootstrapMinCss_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/bootstrap/css";
			}
			$file = "bootstrap.min.css";
			$this->add($path, $file, self::CSS);
		}
		
		
		
		public function mainCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "main.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "custom.css";
			$this->add($path, $file, self::CSS);
		}
		public function sweetalertCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/bootstrap-sweetalert/dist";
			}
			$file = "sweetalert.css";
			$this->add($path, $file, self::CSS);
		}
		public function customCss_04($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/custom/css";
			}
			$file = "custom.css";
			$this->add($path, $file, self::CSS);
		}
		public function styleCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/css";
			}
			$file = "style.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function overrideBoothStrapCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/css";
			}
			$file = "override-bootstrap.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function daterangePickerCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/daterangepicker";
			}
			$file = "daterangepicker.css";
			$this->add($path, $file, self::CSS);
		}
		public function datePickerJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/daterangepicker";
			}
			$file = "daterangepicker.js";
			$this->add($path, $file, self::JS);
		}
		
		public function cropieCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/cropie";
			}
			$file = "croppie.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function faqCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "faq.css";
			$this->add($path, $file, self::CSS);
		}


        public function faqCss_01($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "assets/custom/css";
            }
            $file = "faq.css";
            $this->add($path, $file, self::CSS);
        }
		
		public function intlTelInputCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "intlTelInput.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function appCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "css";
			}
			$file = "app.css";
			$this->add($path, $file, self::CSS);
		}
		public function intlTelInputCss_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "custom/css";
			}
			$file = "intlTelInput.css";
			$this->add($path, $file, self::CSS);
		}

		public function bootstrapMinCss_02($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/bootstrap/css";
			}
			$file = "bootstrap.min.css";
			$this->add($path, $file, self::CSS);
		}
		public function alertifyCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/alertifyjs/dist/css";
			}
			$file = "alertify.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function alertifyCss_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "vendor/alertifyjs/dist/css";
			}
			$file = "alertify.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customCss_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/css";
			}
			$file = "custom.css";
			$this->add($path, $file, self::CSS);
		}
		
		
		public function brandMerchantTemplatedCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca".'/'.'assets/css';
			}
			$file = "sipaymerchanttemplate.css";
			$this->add($path, $file, self::CSS);
		}

        public function customstyleCss($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "css";
            }
            $file = "customstyle.css";
            $this->add($path, $file, self::CSS);
        }
		
		public function paybullCardStyleCss($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/css";
			}
			$file = "paybull_card_style.css";
			$this->add($path, $file, self::CSS);
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		/*
		 * JS
		 */
		
		
		
		
		
		
		public function alertifyCss_02($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/alertifyjs/dist/css";
			}
			$file = "alertify.css";
			$this->add($path, $file, self::CSS);
		}
		
		public function customCss_03($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/css";
			}
			$file = "custom.css";
			$this->add($path, $file, self::CSS);
		}
		
		
		public function bootstrapBundleMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/bootstrap/js";
			}
			$file = "bootstrap.bundle.min.js";
			$this->add($path, $file, self::JS);
		}
		
		public function bootstrapV5BundleMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/bootstrap/js";
			}
			$file = "bootstrap-v5.bundle.min.js";
			$this->add($path, $file, self::JS);
		}

        public function bootstrapV5BundleMinJs_01($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                if($panel == BrandConfiguration::PANEL_MERCHANT){
                    $path = "adminca/assets/vendors/bootstrap/dist/js/";
                }
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendor/bootstrap/js";
            }
            $file = "bootstrap-v5.bundle.min.js";
            $this->add($path, $file, self::JS);
        }
		
		public function alertifyJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/alertifyjs/dist/js";
			}
			$file = "alertify.js";
			$this->add($path, $file, self::JS);
		}

        public function alertifyJs_01($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "vendor/alertifyjs/dist/js";
            }
            $file = "alertify.js";
            $this->add($path, $file, self::JS);
        }

		
		public function jqueryJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/jquery";
			}
			$file = "jquery.js";
			$this->add($path, $file, self::JS);
		}
		
		public function appJs_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "app.js";
			$this->add($path, $file, self::JS);
		}
		
		
		public function appJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca/assets/js";
			}
			$file = "app.js";
			$this->add($path, $file, self::JS);
		}
		
		public function prefixInputJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "prefix-input.js";
			$this->add($path, $file, self::JS);
		}
		
		public function cleaveMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "cleave.min.js";
			$this->add($path, $file, self::JS);
		}
		
		
		public function addCreditJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "add_credit.js";
			$this->add($path, $file, self::JS);
		}
		
		public function sweetalertMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/bootstrap-sweetalert/dist";
			}
			$file = "sweetalert.min.js";
			$this->add($path, $file, self::JS);
		}
		
		
		public function jqueryMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/jquery/dist";
            }
            $file = "jquery.min.js";
            $this->add($path, $file, self::JS);
        }
		
		public function jqueryMinJs_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/vendor/jquery";
			}
			$file = "jquery.min.js";
			$this->add($path, $file, self::JS);
		}

        public function popperMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/popper.js/dist/umd";
            }
            $file = "popper.min.js";
            $this->add($path, $file, self::JS);
        }

        public function bootstrapV5MinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/bootstrap/dist/js";
            }
            $file = "bootstrap-v5.min.js";
            $this->add($path, $file, self::JS);
        }

        public function bootstrapV5MinJs_01($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "assets/vendors/bootstrap/dist/js";
            }
            $file = "bootstrap-v5.min.js";
            $this->add($path, $file, self::JS);
        }

        public function metisMenuMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/metisMenu/dist";
            }
            $file = "metisMenu.min.js";
            $this->add($path, $file, self::JS);
        }
		
		public function customJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/custom/js";
			}
			$file = "custom.js";
			$this->add($path, $file, self::JS);
		}
		
		public function customJs_01($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/js";
			}
			$file = "custom.js";
			$this->add($path, $file, self::JS);
		}

        public function jquerySlimscrollMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/jquery-slimscroll";
            }
            $file = "jquery.slimscroll.min.js";
            $this->add($path, $file, self::JS);
        }

        public function idleTimerMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/jquery-idletimer/dist";
            }
            $file = "idle-timer.min.js";
            $this->add($path, $file, self::JS);
        }

        public function toastrMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/toastr";
            }
            $file = "toastr.min.js";
            $this->add($path, $file, self::JS);
        }

        public function jqueryValidateMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/jquery-validation/dist";
            }
            $file = "jquery.validate.min.js";
            $this->add($path, $file, self::JS);
        }
		
		public function dataTableMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca".'/'.'assets/vendors/dataTables';
			}
			$file = "datatables.min.js";
			$this->add($path, $file, self::JS);
		}
		
		public function bootstrapDatetimepickerMinJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "adminca".'/'.'assets/vendors/smalot-bootstrap-datetimepicker/js';
			}
			$file = "bootstrap-datetimepicker.min.js";
			$this->add($path, $file, self::JS);
		}

        public function bootstrapSelectMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/vendors/bootstrap-select/dist/js";
            }
            $file = "bootstrap-select_v1.14.0.min.js";
            $this->add($path, $file, self::JS);
        }

        public function appMinJs($panel = "", $path = "")
        {
            if (!empty($panel) || !empty($path)) {
                // Put the path finder logic here
                $path = $path;
            } else {
                // Default path
                $path = "adminca/assets/js";
            }
            $file = "app.min.js";
            $this->add($path, $file, self::JS);
        }
		
		public function raphaelJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "raphael-2.1.4.min.js";
			$this->add($path, $file, self::JS);
		}
		
		public function justgageJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "js";
			}
			$file = "justgage.js";
			$this->add($path, $file, self::JS);
		}
		
		public function scriptsJs($panel = "", $path = "")
		{
			if (!empty($panel) || !empty($path)) {
				// Put the path finder logic here
				$path = $path;
			} else {
				// Default path
				$path = "assets/js";
			}
			$file = "scripts.js";
			$this->add($path, $file, self::JS);
		}






        public function add($path, $filename, $type) {
			
			$path = asset($path);
			if(self::CSS == $type){
				$this->css[$filename] = $path;
				return $this->css;
				
			}elseif(self::JS == $type){
				$this->js[$filename] = $path;
				return $this->js;
			}
			
		}
		
		
		// remove css
		public function remove($filename) {
			if(array_key_exists($filename, $this->css)) {
				unset($this->css[$filename]);
			}
			
			if(array_key_exists($filename, $this->js)) {
				unset($this->js[$filename]);
			}
		}
		
		// print css
		public function print($type) {
			$output = '';
			
			if(self::CSS == $type){
				
				if(count($this->css)) {
					foreach($this->css as $filename => $path) {
                        $src = "{$path}/{$filename}";
                        if(empty($filename)){
                            $src = "{$path}";
                        }

						$hashed_value = '';
						
						if(config('subresource-integrity.enabled')){
							
							$hashed_value = (new Sri(config('subresource-integrity.algorithm')))->html("$src");
							
						}
						
						$output .= "<link rel='stylesheet' href='$src' $hashed_value>\n";
						
					}

                    $this->css = [];
				}

			}elseif(self::JS == $type){
				
				if(count($this->js)) {
					foreach($this->js as $filename => $path) {

                        $src = "{$path}/{$filename}";
                        if(empty($filename)){
                            $src = "{$path}";
                        }
						
						$hashed_value = '';
						
						if(config('subresource-integrity.enabled')){
							
							$hashed_value = (new Sri(config('subresource-integrity.algorithm')))->html("$src");
						}
						
						$output .= "<script  src='$src' $hashed_value></script>\n";
						
					}

                    $this->js = [];
				}
			}
			
			echo $output;
		}
	}