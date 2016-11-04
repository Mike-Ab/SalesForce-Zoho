<?php 
session_start();
////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////// CLICK TRACKING AND ADWARDS CREATIVE REGISTERATION ////////////////////////
////////////////////////////////////// BY MOHAMMAD ABBAS ///////////////////////////////////////////
error_reporting(0);
@ini_set('display_errors', 0);

require 'gcon.inc.php';
$socket = new GCONNECT;

class ADWORDS {
	
	const VERSION = '1.0.0';
	
	
	// define the parameters from adwords 
	
	
	private $matchtype_desc 	=	array (  									// keyword match
								'e' => 'Exact',
								'p' => 'Phrase',
								'b' => 'Broad'
								);
	public $matchtype;

	private $network_desc 		= 	array (										// Ad network
								'g' => 'Google Search',
								's' => 'Google Search Partner',
								'd' => 'Google Display Network'
								);
	public $network;
	
	private $device_desc 		= 	array (
								'm' => 'Mobile',
								't' => 'Tablet',
								'c' => 'Computer or Laptop'
								);
	public $device;
	
	public $devicemodel	=	'NA';												// Display Only
	
	public $creative 	= 	'';
	public $keyword 	= 	'NA'; 												// Search Only
	public $placement	= 	'NA';												// Display Only
	public $target		=	'NA';												// CATEGORY - Display Only
	
	//public $aceid		=	'';													// Experiment ID if running one
	
	
	public $user_ip = '';
	
	public $creative_exists = TRUE;
	
	public $template_info = array();
	public $creative_info = array();
	
	private function connect(){
		global $socket;
		if (!$socket->connected) $socket->connect();
		}
	
	private function DC(){
		global $socket;
		$socket->DC();
		}
	
	public function load_creative($creative_id){
		if ($creative_id == NULL || $creative_id == '') $creative_id = 'default';
		if ($creative_id == NULL || $creative_id == '') $this->creative_exists = FALSE;
		$this->connect();
		$c_qry = mysql_query ("SELECT * FROM Adwords_creatives WHERE creative_id LIKE '$creative_id'") or die ('error loading creative'  .mysql_error()); 
		if (mysql_num_rows($c_qry) == 0) {
			unset ($c_qry);
			$this->creative_exists = FALSE;
			$c_qry = mysql_query ("SELECT * FROM Adwords_creatives WHERE creative_id LIKE 'default'") or die ('error loading creative'  .mysql_error());
			}
		$c_array = mysql_fetch_array($c_qry);
		$this->creative_info['campaign'] = $c_array['campaign'];
		$this->creative_info['ad_group'] = $c_array['ad_group'];
		$this->creative_info['ad_type'] = $c_array['ad_type'];
		$this->creative_info['headline'] = $c_array['headline'];
		$this->creative_info['tagline1'] = $c_array['tagline1'];
		$this->creative_info['tagline2'] = $c_array['tagline2'];
		
		$this->creative_info['template_id'] = $c_array['template_id'];
		
		
		
		}
	
	public function load_template($template_id){
		$this->connect();
		$qry = mysql_query("SELECT * FROM  landing_templates WHERE template_id LIKE '$template_id' AND is_active = 'yes'")  or die ('error loading landing page template'  .mysql_error());
		if (mysql_num_rows ($qry) == 0 ) return FALSE;
		
		$array = mysql_fetch_array($qry);
		$this->template_info['title'] = $array['t_title'];
		$this->template_info['desc'] = $array['t_desc'];
		$this->template_info['fb_desc'] = $array['fb_desc'];
		$this->template_info['main_banner'] = $array['main_banner'];
		$this->template_info['main_banner_alt'] = $array['main_banner_alt'];
		$this->template_info['main_tag'] = $array['main_tag'];
		$this->template_info['tagline1'] = $array['tagline1'];
		$this->template_info['tagline2'] = $array['tagline2'];
		$this->template_info['cta1'] = $array['cta1'];
		$this->template_info['cta1_target'] = $array['cta1_target'];
		$this->template_info['cta2'] = $array['cta2'];
		$this->template_info['cta2_target'] = $array['cta2_target'];
		$this->template_info['vid_code'] = $array['vid_code'];
		$this->template_info['vid_title'] = $array['vid_title'];
		$this->template_info['vid_copy'] = $array['vid_copy'];
		$this->template_info['vid_link'] = $array['vid_link'];
		$this->template_info['template'] = $array['template_id'];
		$_SESSION['review_bg'] = $array['review_bg'];
		
		

		
		// GET SLIDES NOW
		$qry2 = mysql_query ("SELECT * FROM landing_sliders WHERE template_id LIKE '$template_id' AND is_active = 'yes' ORDER BY slide_order ASC") or die ('error loading slides '. mysql_error());
		while ($array2 = mysql_fetch_array($qry2)){
			$temp[] = array (	
							'img' 		=> $array2['slide_img'], 
							'alt' 		=> $array2['slide_alt'],
							'title' 	=> $array2['slide_title'],
							'content'	=> $array2['slide_content']			);
			}
		$this->template_info['slides'] = $temp;
		
		// GET BODY BLOCKS
		
		unset ($qry);
		unset ($array);
		
		$qry = mysql_query ("SELECT * FROM landing_body WHERE template_id LIKE '$template_id' ORDER BY body_order ASC") or die ('error loading body blocks '. mysql_error());

			while ($array = mysql_fetch_array($qry)){
			
			$response[] = array (	
							'title' 		=> $array['body_title'], 
							'content' 		=> $array['body_content'],
							'cta' 			=> $array['body_cta'],
							'cta_link'		=> $array['body_cta_link']			);
			}
		$this->template_info['body'] = $response;
		$this->DC();

		}// end of template init function
	
		
		public function init($creative_id){
			if ($creative_id){
				$this->populate_click_info();
				$this->load_creative($creative_id);
				$this->load_template($this->creative_info['template_id']);
				$this->marketing_info();
				$this->register_click();
				return ($this->template_info);
				}
				else {
				$this->populate_click_info();
				$this->marketing_info();
				$this->register_click();
				}
			}	
		
		public function marketing_info($specific_source = null){
			
			////////////////////// version 1.0.2 - Updated tracking for facebook and 


			
			if((isset($_REQUEST['s']) && !empty($_REQUEST['s'])) || $specific_source != ""){ // s indicates the source of the click - anything but google shit
				
				if ($specific_source == null) { 
					$specific_source = $_REQUEST['s'];
				}
				/// career wish
				if ($specific_source == 'cw'){ 	
										 		$_SESSION['specific_source'] = 'careerWish : '.$_REQUEST['cwid']; 
												$_SESSION['source_description'] = $_REQUEST['cwid']; 
												}
				
				/// transport NSW
				if ($specific_source == 'tnsw') 	$_SESSION['specific_source'] = 'Transport NSW';
				
				/// Social Media
				if ($specific_source == 'yt') 		$_SESSION['specific_source'] = 'YouTube Ads';
				if ($specific_source == 'ytv') 		$_SESSION['specific_source'] = 'YouTube Ads';
				if ($specific_source == 'ytvrm') 	$_SESSION['specific_source'] = 'YouTube Vid Rem';				
				if ($specific_source == 'ytb') 		$_SESSION['specific_source'] = 'YouTube Banners';
				if ($specific_source == 'ld') 		$_SESSION['specific_source'] = 'LinkedIn Ads';
				if ($specific_source == 'ldg') 		$_SESSION['specific_source'] = 'LinkedIn Generic';
				if ($specific_source == 'tw') 		$_SESSION['specific_source'] = 'Twitter';
				if ($specific_source == 'tpr') 		$_SESSION['specific_source'] = 'Trades Pre-Roll';
				if ($specific_source == 'ldim') 	$_SESSION['specific_source'] = 'LinkedIn InMail';				

				// CareerOne
				if ($specific_source == 'cos') 		$_SESSION['specific_source'] = 'CareerOne CS';
				if ($specific_source == 'codhh') 	$_SESSION['specific_source'] = 'CareerOne DHH';
				if ($specific_source == 'conet') 	$_SESSION['specific_source'] = 'CareerOne Networker';	
				if ($specific_source == 'coco') 	$_SESSION['specific_source'] = 'CareerOne Courses';			
				if ($specific_source == 'cew') 		$_SESSION['specific_source'] = 'CareerOne EDM Video';		
				if ($specific_source == 'cob') 		$_SESSION['specific_source'] = 'CareerOne Banners';	
				if ($specific_source == 'csol')		$_SESSION['specific_source'] = 'CareerOne Solus';	
				if ($specific_source == 'coss')		$_SESSION['specific_source'] = 'CareerOne Saved Search';	

				// Google
				if ($specific_source == 'rm') 		$_SESSION['specific_source'] = 'ReMarketing';
				if ($specific_source == 'tl') 		$_SESSION['specific_source'] = 'Trade License Campaign';
				if ($specific_source == 'seek') 	$_SESSION['specific_source'] = 'Google Banners';
				if ($specific_source == 'jr') 		$_SESSION['specific_source'] = 'JobsRapido';
				if ($specific_source == 'mraw') 	$_SESSION['specific_source'] = 'Google AdWords';
				if ($specific_source == 'mrawr') 	$_SESSION['specific_source'] = 'Google AdWords - RPL';	
				if ($specific_source == 'mrawg') 	$_SESSION['specific_source'] = 'Google AdWords - General';	
				if ($specific_source == 'mrawt') 	$_SESSION['specific_source'] = 'Google AdWords - Trades';
				if ($specific_source == 'mrawts') 	$_SESSION['specific_source'] = 'Google AdWords - Trade Services';					
				if ($specific_source == 'mrawq') 	$_SESSION['specific_source'] = 'Google AdWords - Qualifications';									
				if ($specific_source == 'mrawtl') 	$_SESSION['specific_source'] = 'Google AdWords - Trades Licence';	
				if ($specific_source == 'mrawc') 	$_SESSION['specific_source'] = 'Google AdWords - Competitors';	
				if ($specific_source == 'gbt') 		$_SESSION['specific_source'] = 'Google Banners - Trades';	
				if ($specific_source == 'gbnt') 	$_SESSION['specific_source'] = 'Google Banners - Non-Trades';	
				if ($specific_source == 'gael') 	$_SESSION['specific_source'] = 'Google Adwords - Electro';	
				if ($specific_source == 'gatc') 	$_SESSION['specific_source'] = 'Google Adwords - Training Courses';					
				if ($specific_source == 'gagen') 	$_SESSION['specific_source'] = 'Google Adwords - Training Courses';									
				if ($specific_source == 'gfbtrm') 	$_SESSION['specific_source'] = 'Google - Facebook Trades Remarket';	
				if ($specific_source == 'gfbntrm') 	$_SESSION['specific_source'] = 'Google - Facebook NonTrades Remarket';		
				if ($specific_source == 'gahosp') 	$_SESSION['specific_source'] = 'Google Adwords - Hospitality';		
				if ($specific_source == 'gaauto') 	$_SESSION['specific_source'] = 'Google Adwords - Automotive';						
				if ($specific_source == 'gahort') 	$_SESSION['specific_source'] = 'Google Adwords - Horticulture';										
				if ($specific_source == 'gabeaut') 	$_SESSION['specific_source'] = 'Google Adwords - Beauty';	
				if ($specific_source == 'gatrans') 	$_SESSION['specific_source'] = 'Google Adwords - Transport Logistics';
				if ($specific_source == 'gatrain') 	$_SESSION['specific_source'] = 'Google Adwords - Training Education';	
				if ($specific_source == 'gasus') 	$_SESSION['specific_source'] = 'Google Adwords - Sustainability';		
				if ($specific_source == 'gasport') 	$_SESSION['specific_source'] = 'Google Adwords - Sport Fitness';	
				if ($specific_source == 'garetail')	$_SESSION['specific_source'] = 'Google Adwords - Retail';			
				if ($specific_source == 'gares')	$_SESSION['specific_source'] = 'Google Adwords - Mining';	
				if ($specific_source == 'gaprop')	$_SESSION['specific_source'] = 'Google Adwords - Property Services';	
				if ($specific_source == 'gamet')	$_SESSION['specific_source'] = 'Google Adwords - Metal Engineering';	
				if ($specific_source == 'gamanu')	$_SESSION['specific_source'] = 'Google Adwords - Manufacturing';	
				if ($specific_source == 'gaheal')	$_SESSION['specific_source'] = 'Google Adwords - Health';		
				if ($specific_source == 'gahair')	$_SESSION['specific_source'] = 'Google Adwords - Hairdressing';			
				if ($specific_source == 'gafurn')	$_SESSION['specific_source'] = 'Google Adwords - Furnishing';
				if ($specific_source == 'gafound')	$_SESSION['specific_source'] = 'Google Adwords - Foundation Skills';
				if ($specific_source == 'gafood')	$_SESSION['specific_source'] = 'Google Adwords - Food Processing';
				if ($specific_source == 'gafin')	$_SESSION['specific_source'] = 'Google Adwords - Financial Services';		
				if ($specific_source == 'gacons')	$_SESSION['specific_source'] = 'Google Adwords - Construction Plumbing';	
				if ($specific_source == 'gacomm')	$_SESSION['specific_source'] = 'Google Adwords - Community Services';	
				if ($specific_source == 'gabus')	$_SESSION['specific_source'] = 'Google Adwords - Business Services';	
				if ($specific_source == 'gaold')	$_SESSION['specific_source'] = 'Google Adwords - Old LP';		
				if ($specific_source == 'gacqf')	$_SESSION['specific_source'] = 'Google Adwords - Competitors QF';	
				if ($specific_source == 'gdnco')	$_SESSION['specific_source'] = 'Google Display - Construction';		
				if ($specific_source == 'gacar')	$_SESSION['specific_source'] = 'Google Adwords - Carpentry';																																																																											
				if ($specific_source == 'gaconsc')	$_SESSION['specific_source'] = 'Twitter - Construction Plumbing - C';
				if ($specific_source == 'gaskrec')	$_SESSION['specific_source'] = 'Google Adwords - Australia Skills Recognition';
				if ($specific_source == 'gagqaus')	$_SESSION['specific_source'] = 'Google Adwords - GQ Australia';																		
				if ($specific_source == 'gamin')	$_SESSION['specific_source'] = 'Google Adwords - Mining';
				if ($specific_source == 'gaqds')	$_SESSION['specific_source'] = 'Google Adwords - Qual Diploma Skills';																										
				if ($specific_source == 'gaqme')	$_SESSION['specific_source'] = 'Google Adwords - Qualify Me';	
				if ($specific_source == 'gabcg')	$_SESSION['specific_source'] = 'Google Adwords - Building Construction - G';
				if ($specific_source == 'gaengs')	$_SESSION['specific_source'] = 'Google Adwords - Engineering - S';
				if ($specific_source == 'gacertiii')	$_SESSION['specific_source'] = 'Google Adwords - Cert III';		
				if ($specific_source == 'gacertiv')	$_SESSION['specific_source'] = 'Google Adwords - Cert IV';		
				if ($specific_source == 'gaskillsa')$_SESSION['specific_source'] = 'Google Adwords - Skills Assessment';	
				if ($specific_source == 'gabuild')	$_SESSION['specific_source'] = 'Google Adwords - Building';	
				if ($specific_source == 'gaavia')	$_SESSION['specific_source'] = 'Google Adwords - AVI';	
				if ($specific_source == 'gabusma')	$_SESSION['specific_source'] = 'Google Adwords - Business Management';		
				if ($specific_source == 'gacommse')	$_SESSION['specific_source'] = 'Google Adwords - Community Service';	
				if ($specific_source == 'gaglag')	$_SESSION['specific_source'] = 'Google Adwords - Glass Glazing';
				if ($specific_source == 'gainft')	$_SESSION['specific_source'] = 'Google Adwords - Information Technology';
				if ($specific_source == 'gapubsa')	$_SESSION['specific_source'] = 'Google Adwords - Public Safety';	
				if ($specific_source == 'gapubsec')	$_SESSION['specific_source'] = 'Google Adwords - Public Sector';		
				if ($specific_source == 'gatexclo')	$_SESSION['specific_source'] = 'Google Adwords - Textiles Clothing';	
				if ($specific_source == 'gagencer')	$_SESSION['specific_source'] = 'Google Adwords - Generic Cert';	
				if ($specific_source == 'gamba')	$_SESSION['specific_source'] = 'Google Adwords - MBA';	
				if ($specific_source == 'ebvrm1')	$_SESSION['specific_source'] = 'Ebook Video Remarketing 1';		
				if ($specific_source == 'gmail')	$_SESSION['specific_source'] = 'Google Gmail';									
				if ($specific_source == 'gagenemp')	$_SESSION['specific_source'] = 'Google - EMP';		
				if ($specific_source == 'gajrdec')	$_SESSION['specific_source'] = 'Google - JR';
				if ($specific_source == 'gacarau')	$_SESSION['specific_source'] = 'Google - CARAU';																																																																																																								
				if ($specific_source == 'gajrdecmel')	$_SESSION['specific_source'] = 'Google - JR -MEL';
				
				// 2016 AdWords
				// ## RPL SPECIFIC ##
				if ($specific_source == '16garple')	$_SESSION['specific_source'] = 'Google S - RPL - E';	
				if ($specific_source == '16garplp')	$_SESSION['specific_source'] = 'Google S - RPL - P';	
				if ($specific_source == '16garplb')	$_SESSION['specific_source'] = 'Google S - RPL - B';																					
				// ## BUSINESS SERVICES ##
				if ($specific_source == '16sqsbse')	$_SESSION['specific_source'] = 'Google S - QS BSTP - E';
				if ($specific_source == '16sqsbsb')	$_SESSION['specific_source'] = 'Google S - QS BSTP - B';
				if ($specific_source == '16sqsbsp')	$_SESSION['specific_source'] = 'Google S - QS BSTP - P';
				// ## BUILDING & CONSTRUCTION ##
				if ($specific_source == '16sqsbce')	$_SESSION['specific_source'] = 'Google S - QS BCTP - E';												
				if ($specific_source == '16sqsbcp')	$_SESSION['specific_source'] = 'Google S - QS BCTP - P';																
				if ($specific_source == '16sqsbcb')	$_SESSION['specific_source'] = 'Google S - QS BCTP - B';	
				
				// ## BUILDING & CONSTRUCTION ##
				if ($specific_source == '16sjrbce')	$_SESSION['specific_source'] = 'Google S - JR BCTP - E';												
				if ($specific_source == '16sjrbcp')	$_SESSION['specific_source'] = 'Google S - JR BCTP - P';																
				if ($specific_source == '16sjrbcb')	$_SESSION['specific_source'] = 'Google S - JR BCTP - B';
				
				// ## RESOURCE & INFRASTRUCTURE ##
				if ($specific_source == '16sqsrese')$_SESSION['specific_source'] = 'Google S - QS RESTP - E';	
				if ($specific_source == '16sqsresp')$_SESSION['specific_source'] = 'Google S - QS RESTP - P';	
				if ($specific_source == '16sqsresb')$_SESSION['specific_source'] = 'Google S - QS RESTP - B';	
				// ## COMMUNITY SERVICES ##
				if ($specific_source == '16sqscome')$_SESSION['specific_source'] = 'Google S - QS COMTP - E';	
				if ($specific_source == '16sqscomb')$_SESSION['specific_source'] = 'Google S - QS COMTP - B';	
				if ($specific_source == '16sqscomp')$_SESSION['specific_source'] = 'Google S - QS COMTP - P';									
				// ## ENGINEERING ##
				if ($specific_source == '16sqsenge')$_SESSION['specific_source'] = 'Google S - QS ENGTP - E';	
				if ($specific_source == '16sqsengb')$_SESSION['specific_source'] = 'Google S - QS ENGTP - B';	
				if ($specific_source == '16sqsengp')$_SESSION['specific_source'] = 'Google S - QS ENGTP - P';
				// ## HOSPITALITY ##
				if ($specific_source == '16sqshose')$_SESSION['specific_source'] = 'Google S - QS HOSTP - E';	
				if ($specific_source == '16sqshosb')$_SESSION['specific_source'] = 'Google S - QS HOSTP - B';	
				if ($specific_source == '16sqshosp')$_SESSION['specific_source'] = 'Google S - QS HOSTP - P';				
				// ## AUTOMOTIVE ##
				if ($specific_source == '16sqsaute')$_SESSION['specific_source'] = 'Google S - QS AUTTP - E';	
				if ($specific_source == '16sqsautb')$_SESSION['specific_source'] = 'Google S - QS AUTTP - B';	
				if ($specific_source == '16sqsautp')$_SESSION['specific_source'] = 'Google S - QS AUTTP - P';	
				// ## PUBLIC SERVICE ##
				if ($specific_source == '16sqspube')$_SESSION['specific_source'] = 'Google S - QS PUBTP - E';	
				if ($specific_source == '16sqspubb')$_SESSION['specific_source'] = 'Google S - QS PUBTP - B';	
				if ($specific_source == '16sqspubp')$_SESSION['specific_source'] = 'Google S - QS PUBTP - P';					
				// ## FINANCE ##
				if ($specific_source == '16sqsfine')$_SESSION['specific_source'] = 'Google S - QS FINTP - E';	
				if ($specific_source == '16sqsfinb')$_SESSION['specific_source'] = 'Google S - QS FINTP - B';	
				if ($specific_source == '16sqsfinp')$_SESSION['specific_source'] = 'Google S - QS FINTP - P';	
				
				// ## ELECTRO ##
				if ($specific_source == '16sqselee')$_SESSION['specific_source'] = 'Google S - QS ELETP - E';	
				if ($specific_source == '16sqseleb')$_SESSION['specific_source'] = 'Google S - QS ELETP - B';	
				if ($specific_source == '16sqselep')$_SESSION['specific_source'] = 'Google S - QS ELETP - P';					
				
				// ## MBA DISPLAY ##
				if ($specific_source == '16drmba')$_SESSION['specific_source'] = 'Google D - R - MBA';			
				if ($specific_source == '16dfmba')$_SESSION['specific_source'] = 'Google D - F - MBA';		
				if ($specific_source == '16drmmba')$_SESSION['specific_source'] = 'Google D - RM - MBA';	
				if ($specific_source == '16gspmba')$_SESSION['specific_source'] = 'Google GSP - MBA';									

				// ## SPORT & FITNESS ##
				if ($specific_source == '16sqsspoe')$_SESSION['specific_source'] = 'Google S - QS SPOTP - E';	
				if ($specific_source == '16sqsspob')$_SESSION['specific_source'] = 'Google S - QS SPOTP - B';	
				if ($specific_source == '16sqsspop')$_SESSION['specific_source'] = 'Google S - QS SPOTP - P';					
	
				// ## TRANSPORT & LOGISTICS ##
				if ($specific_source == '16sqstrae')$_SESSION['specific_source'] = 'Google S - QS TRATP - E';	
				if ($specific_source == '16sqstrab')$_SESSION['specific_source'] = 'Google S - QS TRATP - B';	
				if ($specific_source == '16sqstrap')$_SESSION['specific_source'] = 'Google S - QS TRATP - P';													

				// ## AGRICULTURE & HORTICULTURE ##
				if ($specific_source == '16sqsagre')$_SESSION['specific_source'] = 'Google S - QS AGRTP - E';	
				if ($specific_source == '16sqsagrb')$_SESSION['specific_source'] = 'Google S - QS AGRTP - B';	
				if ($specific_source == '16sqsagrp')$_SESSION['specific_source'] = 'Google S - QS AGRTP - P';	
				
				// ## HEALTH ##
				if ($specific_source == '16sqsheae')$_SESSION['specific_source'] = 'Google S - QS HEATP - E';	
				if ($specific_source == '16sqsheab')$_SESSION['specific_source'] = 'Google S - QS HEATP - B';	
				if ($specific_source == '16sqsheap')$_SESSION['specific_source'] = 'Google S - QS HEATP - P';	
								
				if ($specific_source == '16ssl')$_SESSION['specific_source'] = 'Google S - Sitelink';				
																							
				// Bing
				if ($specific_source == 'be') 	  	$_SESSION['specific_source'] = 'Bing - Electro';	
				if ($specific_source == 'bco') 	  	$_SESSION['specific_source'] = 'Bing - Competitors';
				if ($specific_source == 'bge') 	  	$_SESSION['specific_source'] = 'Bing - General';
				if ($specific_source == 'btl') 	  	$_SESSION['specific_source'] = 'Bing - Trade Licence';
				if ($specific_source == 'bqual')  	$_SESSION['specific_source'] = 'Bing - Qualifications';				
				if ($specific_source == 'brpl')  	$_SESSION['specific_source'] = 'Bing - RPL';	
				if ($specific_source == 'btra')  	$_SESSION['specific_source'] = 'Bing - Trades';								
				if ($specific_source == 'bhosp')  	$_SESSION['specific_source'] = 'Bing - Hospitality';	
				if ($specific_source == 'btrli')  	$_SESSION['specific_source'] = 'Bing - Trades Licence';										
				if ($specific_source == 'bires')	$_SESSION['specific_source'] = 'Bing - Resource Infrastructure';	
				if ($specific_source == 'bitraic')	$_SESSION['specific_source'] = 'Bing - Training Courses';
				if ($specific_source == 'biauto')	$_SESSION['specific_source'] = 'Bing - Automotive';			
				if ($specific_source == 'bibeaut')	$_SESSION['specific_source'] = 'Bing - Beauty';									
				if ($specific_source == 'bibus')	$_SESSION['specific_source'] = 'Bing - Business';		
				if ($specific_source == 'bifin')	$_SESSION['specific_source'] = 'Bing - Financial';																	
				if ($specific_source == 'bifood')	$_SESSION['specific_source'] = 'Bing - Food';																					
				if ($specific_source == 'bifound')	$_SESSION['specific_source'] = 'Bing - Foundation Skills';	
				if ($specific_source == 'bihair')	$_SESSION['specific_source'] = 'Bing - Hairdressing';			
				if ($specific_source == 'biheal')	$_SESSION['specific_source'] = 'Bing - Health';		
				if ($specific_source == 'bimet')	$_SESSION['specific_source'] = 'Bing - Metal Engineering';	
				if ($specific_source == 'biprop')	$_SESSION['specific_source'] = 'Bing - Property Services';		
				if ($specific_source == 'bispor')	$_SESSION['specific_source'] = 'Bing - Sports Fitness';		
				if ($specific_source == 'bisust')	$_SESSION['specific_source'] = 'Bing - Sustainability';		
				if ($specific_source == 'bicomm')	$_SESSION['specific_source'] = 'Bing - Community Services';																																																					
				if ($specific_source == 'biconst')	$_SESSION['specific_source'] = 'Bing - Construction Plumbing';																																																									
				if ($specific_source == 'bihort')	$_SESSION['specific_source'] = 'Bing - Agriculture Horticulture';	
				if ($specific_source == 'bihosp')	$_SESSION['specific_source'] = 'Bing - Hospitality';				
				if ($specific_source == 'bifurn')	$_SESSION['specific_source'] = 'Bing - Furnishing';																																																																					
				if ($specific_source == 'bimanu')	$_SESSION['specific_source'] = 'Bing - Manufacturing';																																																																					
				if ($specific_source == 'bireta')	$_SESSION['specific_source'] = 'Bing - Retail';		
				if ($specific_source == 'bitrain')	$_SESSION['specific_source'] = 'Bing - Training Education';	
				if ($specific_source == 'bitrans')	$_SESSION['specific_source'] = 'Bing - Transport Logistics';
				if ($specific_source == 'biqds')	$_SESSION['specific_source'] = 'Bing - Qual Diploma Skills';	
				if ($specific_source == 'bigqaus')	$_SESSION['specific_source'] = 'Bing - GQ Australia';		
				if ($specific_source == 'biauskr')	$_SESSION['specific_source'] = 'Bing - Australia Skills Recognition';	
				if ($specific_source == 'biqumau')	$_SESSION['specific_source'] = 'Bing - Qualify Me';		
				if ($specific_source == 'bijrdec')	$_SESSION['specific_source'] = 'Bing - JR';																																																																																																	
												
				// Other
				if ($specific_source == 'hc') 		$_SESSION['specific_source'] = 'HotCourses';				
				if ($specific_source == 'b') 	  	$_SESSION['specific_source'] = 'Bing Ads';				
				if ($specific_source == 'mx') 	  	$_SESSION['specific_source'] = 'MX News Sydney';
				if ($specific_source == 'ar') 		$_SESSION['specific_source'] = 'AdRoll';
				if ($specific_source == 'mmm') 		$_SESSION['specific_source'] = '3MMM Banners';
				if ($specific_source == 'yh') 		$_SESSION['specific_source'] = 'Yahoo7';
				if ($specific_source == 'yhau') 	$_SESSION['specific_source'] = 'Yahoo7 Audience';				
				if ($specific_source == 'ff') 		$_SESSION['specific_source'] = 'Fairfax Banners';
				if ($specific_source == 'trv') 		$_SESSION['specific_source'] = 'Travel Emails';
				if ($specific_source == 'rmd') 		$_SESSION['specific_source'] = 'RMD EDM';
				if ($specific_source == 'rmds') 	$_SESSION['specific_source'] = 'RMD Site';
				if ($specific_source == 'om') 		$_SESSION['specific_source'] = 'Oz Mining';
				if ($specific_source == 'ahrip')	$_SESSION['specific_source'] = 'AHRI Print';
 				if ($specific_source == 'tb')		$_SESSION['specific_source'] = 'The Brag';
 				if ($specific_source == 'lbtr')		$_SESSION['specific_source'] = 'Letterbox AAM - Trades';	
 				if ($specific_source == 'lbsyd')	$_SESSION['specific_source'] = 'Letterbox Sydney - Trades';			
 				if ($specific_source == 'aaoa')		$_SESSION['specific_source'] = 'AAoA';
 				if ($specific_source == 'adbo')		$_SESSION['specific_source'] = 'Adblade';		
 				if ($specific_source == 's8')		$_SESSION['specific_source'] = 'Survey Email';	
				if ($specific_source == 'cafaq')	$_SESSION['specific_source'] = 'Career FAQ';																				
				if ($specific_source == 'apdnt')	$_SESSION['specific_source'] = 'APD NT';			
				if ($specific_source == 'insta')	$_SESSION['specific_source'] = 'Instagram';		
				if ($specific_source == 'qt')		$_SESSION['specific_source'] = 'QT';	
				if ($specific_source == 'nlpvv')	$_SESSION['specific_source'] = 'NLP Video View';	
				if ($specific_source == 'ifsem')	$_SESSION['specific_source'] = 'Lead Nurturing';			
				if ($specific_source == 'yhbus')	$_SESSION['specific_source'] = 'Yahoo Business';	
				if ($specific_source == 'revpro')	$_SESSION['specific_source'] = 'Rev Content Professional';																													
				if ($specific_source == 'stadpro')	$_SESSION['specific_source'] = 'StAd Professional';
				if ($specific_source == 'exomba')	$_SESSION['specific_source'] = 'EXO MBA';			
				if ($specific_source == 'getinmba')	$_SESSION['specific_source'] = 'GETIN MBA';
				if ($specific_source == 'ssmba')	$_SESSION['specific_source'] = 'SS MBA';
				if ($specific_source == 'pomamba')	$_SESSION['specific_source'] = 'POMA MBA';	
				if ($specific_source == 'westin')	$_SESSION['specific_source'] = 'Westin Hotel';		
				if ($specific_source == 'newcrest')	$_SESSION['specific_source'] = 'Newcrest Mining';	
				if ($specific_source == 'webmba')	$_SESSION['specific_source'] = 'Website - MBA';	
				if ($specific_source == 'yhmba')	$_SESSION['specific_source'] = 'Yahoo MBA';	
				if ($specific_source == 'atc')		$_SESSION['specific_source'] = 'ATC EDM';																												
								
				// Facebook
				if ($specific_source == 'fbm') 		$_SESSION['specific_source'] = 'Facebook Trades CPM';
				if ($specific_source == 'fb') 		$_SESSION['specific_source'] = 'Facebook Trades';
				if ($specific_source == 'fbg') 		$_SESSION['specific_source'] = 'Facebook Non-Trades';
				if ($specific_source == 'fbtn') 	$_SESSION['specific_source'] = 'Facebook Trades New';
				if ($specific_source == 'fbgm') 	$_SESSION['specific_source'] = 'Facebook Micro Target';
				if ($specific_source == 'fbtmt') 	$_SESSION['specific_source'] = 'Facebook Trades Micro Target';
				if ($specific_source == 'fbc') 		$_SESSION['specific_source'] = 'Facebook Target C';
				if ($specific_source == 'fbbt') 	$_SESSION['specific_source'] = 'Facebook Business Target';
				if ($specific_source == 'fbct') 	$_SESSION['specific_source'] = 'Facebook Cookery Target';
				if ($specific_source == 'fbv') 		$_SESSION['specific_source'] = 'Facebook Video';
				if ($specific_source == 'fbctt') 	$_SESSION['specific_source'] = 'Facebook Conversion Test';
				if ($specific_source == 'fbtd') 	$_SESSION['specific_source'] = 'Facebook Trades Demo';
				if ($specific_source == 'fbd') 		$_SESSION['specific_source'] = 'Facebook Day';
				if ($specific_source == 'fbb') 		$_SESSION['specific_source'] = 'Facebook Broad';
				if ($specific_source == 'fbsr') 	$_SESSION['specific_source'] = 'Facebook Sales Retail';
				if ($specific_source == 'fbs') 	  	$_SESSION['specific_source'] = 'Facebook Seek';
				if ($specific_source == 'fboc') 	$_SESSION['specific_source'] = 'Facebook Open Colleges';
				if ($specific_source == 'fbt') 		$_SESSION['specific_source'] = 'Facebook Tafe';
				if ($specific_source == 'fbtcpm') 	$_SESSION['specific_source'] = 'Facebook Tafe CPM';
				if ($specific_source == 'fboctt') 	$_SESSION['specific_source'] = 'Facebook Oct Trades';
				if ($specific_source == 'fboct') 	$_SESSION['specific_source'] = 'Facebook Oct';
				if ($specific_source == 'fbmin') 	$_SESSION['specific_source'] = 'Facebook Mining';	
				if ($specific_source == 'fbtocpm')	$_SESSION['specific_source'] = 'Facebook Trades Broad oCPM';			
				if ($specific_source == 'fbcmt')	$_SESSION['specific_source'] = 'Facebook Community';	
				if ($specific_source == 'lv')		$_SESSION['specific_source'] = 'Facebook Lookalike Visitors';											
				if ($specific_source == 'll')		$_SESSION['specific_source'] = 'Facebook Lookalike Leads';		
				if ($specific_source == 'fb17')		$_SESSION['specific_source'] = 'Facebook 17';			
				if ($specific_source == 'fbnynt')	$_SESSION['specific_source'] = 'Facebook New Years NonTrades';	
				if ($specific_source == 'fbnyt')	$_SESSION['specific_source'] = 'Facebook New Years Trades';	
				if ($specific_source == 'fbr')		$_SESSION['specific_source'] = 'Facebook R Test';															
				if ($specific_source == 'fbmm')		$_SESSION['specific_source'] = 'Facebook M';		
				if ($specific_source == 'fbtll')	$_SESSION['specific_source'] = 'Facebook Trades Lookalike';						
				if ($specific_source == 'fbnl')		$_SESSION['specific_source'] = 'Facebook 14 NL';		
				if ($specific_source == 'fbq')		$_SESSION['specific_source'] = 'Facebook Queensland';		
				if ($specific_source == 'fbe')		$_SESSION['specific_source'] = 'Facebook Electro';		
				if ($specific_source == 'fbhosp')	$_SESSION['specific_source'] = 'Facebook Hospitality';		
				if ($specific_source == 'fbhort')	$_SESSION['specific_source'] = 'Facebook Horticulture';																							
				if ($specific_source == 'fbauto')	$_SESSION['specific_source'] = 'Facebook Automotive';																											
				if ($specific_source == 'fbbea')	$_SESSION['specific_source'] = 'Facebook Beauty';																											
				if ($specific_source == 'fbbus')	$_SESSION['specific_source'] = 'Facebook Business';	
				if ($specific_source == 'fbcomm')	$_SESSION['specific_source'] = 'Facebook Community Service';								
				if ($specific_source == 'fbconst')	$_SESSION['specific_source'] = 'Facebook Construction Plumbing';	
				if ($specific_source == 'fbconst')	$_SESSION['specific_source'] = 'Facebook Construction Plumbing';																
				if ($specific_source == 'fbfin')	$_SESSION['specific_source'] = 'Facebook Financial';																				
				if ($specific_source == 'fbfoo')	$_SESSION['specific_source'] = 'Facebook Food Processing';																								
				if ($specific_source == 'fbfound')	$_SESSION['specific_source'] = 'Facebook Foundation Skills';																												
				if ($specific_source == 'fbfurn')	$_SESSION['specific_source'] = 'Facebook Furninshing';
				if ($specific_source == 'fbhair')	$_SESSION['specific_source'] = 'Facebook Hairdressing';				
				if ($specific_source == 'fbhea')	$_SESSION['specific_source'] = 'Facebook Health';								
				if ($specific_source == 'fbmanu')	$_SESSION['specific_source'] = 'Facebook Manufacturing';																																												
				if ($specific_source == 'fbmet')	$_SESSION['specific_source'] = 'Facebook Metal Engineering';																																																
				if ($specific_source == 'fbprop')	$_SESSION['specific_source'] = 'Facebook Property Services';																																																				
				if ($specific_source == 'fbres')	$_SESSION['specific_source'] = 'Facebook Resource Infrastructure';																																																				
				if ($specific_source == 'fbret')	$_SESSION['specific_source'] = 'Facebook Retail Services';																																																								
				if ($specific_source == 'fbsport')	$_SESSION['specific_source'] = 'Facebook Sports Fitness';																																																												
				if ($specific_source == 'fbsus')	$_SESSION['specific_source'] = 'Facebook Sustainability';																																																																
				if ($specific_source == 'fbtrain')	$_SESSION['specific_source'] = 'Facebook Training Education';																																																																					
				if ($specific_source == 'fbtrans')	$_SESSION['specific_source'] = 'Facebook Transport Logistics';		
				if ($specific_source == 'fbcarp')	$_SESSION['specific_source'] = 'Facebook Carpentry';	
				if ($specific_source == 'fbplum')	$_SESSION['specific_source'] = 'Facebook Plumbing';																																																																													
				if ($specific_source == 'fbbucon')	$_SESSION['specific_source'] = 'Facebook Building Construction';
				if ($specific_source == 'fbmin')	$_SESSION['specific_source'] = 'Facebook Mining';		
				if ($specific_source == 'fbvrm')	$_SESSION['specific_source'] = 'Facebook Vid Rem';		
				if ($specific_source == 'fbenot')	$_SESSION['specific_source'] = 'Facebook Engineering 2 - Oth';	
				if ($specific_source == 'fbenen')	$_SESSION['specific_source'] = 'Facebook Engineering 2 - Eng';						
				if ($specific_source == 'fbenau')	$_SESSION['specific_source'] = 'Facebook Engineering 2 - Auto';										
				if ($specific_source == 'fbenho')	$_SESSION['specific_source'] = 'Facebook Engineering 2 - Hort';	
				if ($specific_source == 'fbbrbu')	$_SESSION['specific_source'] = 'Facebook N - BC';																		
				if ($specific_source == 'fbbrau')	$_SESSION['specific_source'] = 'Facebook N - Auto';																						
				if ($specific_source == 'fbbren')	$_SESSION['specific_source'] = 'Facebook N - Eng';																										
				if ($specific_source == 'fbbrcar')	$_SESSION['specific_source'] = 'Facebook N - Carpentry';		
				if ($specific_source == 'fbbrplu')	$_SESSION['specific_source'] = 'Facebook N - Plumbing';
				if ($specific_source == 'fbbroth')	$_SESSION['specific_source'] = 'Facebook N - Other';	
				if ($specific_source == 'fbpbo')	$_SESSION['specific_source'] = 'Facebook PreBook - Other';	
				if ($specific_source == 'fbpaut')	$_SESSION['specific_source'] = 'Facebook PreBook - Auto';		
				if ($specific_source == 'fbpbbc')	$_SESSION['specific_source'] = 'Facebook PreBook - BC';		
				if ($specific_source == 'fbpbeng')	$_SESSION['specific_source'] = 'Facebook PreBook - Eng';		
				if ($specific_source == 'fbpbcar')	$_SESSION['specific_source'] = 'Facebook PreBook - Carpentry';	
				if ($specific_source == 'fbpbplu')	$_SESSION['specific_source'] = 'Facebook PreBook - Plumbing';		
				if ($specific_source == 'fbwsll')	$_SESSION['specific_source'] = 'Facebook - Website Lookalike';																																													
				if ($specific_source == 'ntll')	$_SESSION['specific_source'] = 'Facebook Non Trades Lookalike';	
				if ($specific_source == 'fbbrick')	$_SESSION['specific_source'] = 'Facebook Bricklaying';			
				if ($specific_source == 'fbtrades')	$_SESSION['specific_source'] = 'Facebook Trades';	
				if ($specific_source == 'fbtravou')	$_SESSION['specific_source'] = 'Facebook 250 Trades Discount';	
				if ($specific_source == 'fbtravou10')	$_SESSION['specific_source'] = 'Facebook 10 Percent Trades Discount';	
				if ($specific_source == 'fbef')		$_SESSION['specific_source'] = 'Facebook Enquire Form';		
				if ($specific_source == 'fbbn')		$_SESSION['specific_source'] = 'Facebook BN';	
				if ($specific_source == 'fbsps')		$_SESSION['specific_source'] = 'Facebook SPS';	
				if ($specific_source == 'fbotl')		$_SESSION['specific_source'] = 'Facebook OTL';		
				if ($specific_source == 'fblgcon')		$_SESSION['specific_source'] = 'Facebook LGCON';	
				if ($specific_source == 'fbexp')		$_SESSION['specific_source'] = 'Facebook EXP';	
				if ($specific_source == 'fblife')		$_SESSION['specific_source'] = 'Facebook Life';		
				if ($specific_source == 'fbcuoc')		$_SESSION['specific_source'] = 'Facebook CUO';	
				if ($specific_source == 'fbmt')			$_SESSION['specific_source'] = 'Facebook MT';		
				if ($specific_source == 'ntcll')	$_SESSION['specific_source'] = 'Facebook NT Cust Lookalike';											
				if ($specific_source == 'fbtue')	$_SESSION['specific_source'] = 'Facebook TUE';
				if ($specific_source == 'fbvpro')	$_SESSION['specific_source'] = 'Facebook vPro';
				if ($specific_source == 'fbfsr')	$_SESSION['specific_source'] = 'Facebook FSR TW';								
				if ($specific_source == 'fbp1')		$_SESSION['specific_source'] = 'Facebook Phase 1 M';	
				if ($specific_source == 'fbp2')		$_SESSION['specific_source'] = 'Facebook Phase 2 M';	
				if ($specific_source == 'fbpd1')		$_SESSION['specific_source'] = 'Facebook Phase 1 D';	
				if ($specific_source == 'fbpd2')		$_SESSION['specific_source'] = 'Facebook Phase 2 D';
				if ($specific_source == 'fbjsd')		$_SESSION['specific_source'] = 'Facebook JS - D';									
				if ($specific_source == 'fbjsm')		$_SESSION['specific_source'] = 'Facebook JS - M';	
				if ($specific_source == 'fbtlad')		$_SESSION['specific_source'] = 'Facebook TLA - D';	
				if ($specific_source == 'fbtlam')		$_SESSION['specific_source'] = 'Facebook TLA - M';																	
				if ($specific_source == 'fb90cc')		$_SESSION['specific_source'] = 'Facebook 90 - CC';	
				if ($specific_source == 'fbaudco')		$_SESSION['specific_source'] = 'Facebook Aud Co';
				if ($specific_source == 'fbdecpro')		$_SESSION['specific_source'] = 'Facebook RM December Promo';
				if ($specific_source == 'fbdecpro1')	$_SESSION['specific_source'] = 'Facebook December Promo';		
				if ($specific_source == 'fbmanage')		$_SESSION['specific_source'] = 'Facebook Management';		
				if ($specific_source == 'fbmba')		$_SESSION['specific_source'] = 'MBA - Facebook';
				if ($specific_source == 'fbmbarm')		$_SESSION['specific_source'] = 'Facebook - MBA - RM';		
				if ($specific_source == 'fb15febt')		$_SESSION['specific_source'] = 'Facebook - 15 Trades';
				if ($specific_source == 'fbflife')		$_SESSION['specific_source'] = 'Facebook - F - Life';		
				if ($specific_source == 'fbsrrm')		$_SESSION['specific_source'] = 'Facebook - SR - RM';	
				if ($specific_source == 'fbaged')		$_SESSION['specific_source'] = 'Facebook - Aged';		
				if ($specific_source == 'fb16trt')		$_SESSION['specific_source'] = 'Facebook - 2016 Trades Test';	
				if ($specific_source == 'fb16agri')		$_SESSION['specific_source'] = 'Facebook - 2016 Agriculture';	
				if ($specific_source == 'fbmbav')		$_SESSION['specific_source'] = 'Facebook - MBA Video';																						
																					
				// Facebook																																																																																																																																				
				if ($specific_source == 'e0')		$_SESSION['specific_source'] = 'eBook 0';	
				if ($specific_source == 'e1')		$_SESSION['specific_source'] = 'eBook 1';					
												
				if ($specific_source == 'ref') {
					$_SESSION['specific_source'] = 'Affiliate Program'; 
					$_SESSION['referral_code'] = $_REQUEST['r'];
					file_get_contents ('http://www.gqaustralia.com.au/referral/api/?op=track_click&auth_token=202cb962ac59075b964b07152d234b70&id='.$_REQUEST['r']);				
					}

				}else {
				$_SESSION['specific_source'] = 	$this->network_desc[$this->network];
				$_SESSION['in_depth_Source'] = 	$this->creative_info['campaign'] . ' / '. $this->creative_info['ad_group'];
				$place = ($this->network == 'd') ? ' - '.$this->placement . ' / '. $this->target : '';
				$key = ($this->network == 'g' || $this->network == 'g') ? ' - Used Keyword: '.$this->keyword : '';
				$_SESSION['source_description'] = 'Skills Review Form ' .$place . $key;
				}
			
			}
		private function populate_click_info(){
			$this->creative = !empty($_REQUEST['c'])? $_REQUEST['c'] : NULL;
			$this->matchtype = !empty($_REQUEST['mt'])? $_REQUEST['mt'] : NULL;
			$this->network = !empty($_REQUEST['n'])? $_REQUEST['n'] : NULL;
			$this->device = !empty($_REQUEST['d'])? $_REQUEST['d'] : NULL;
			$this->devicemodel = !empty($_REQUEST['dm'])? $_REQUEST['dm'] : NULL;
			$this->keyword = !empty($_REQUEST['k'])? $_REQUEST['k'] : NULL;
			$this->placement = !empty($_REQUEST['p'])? $_REQUEST['p'] : NULL;
			$this->target = !empty($_REQUEST['t'])? $_REQUEST['t'] : NULL;
			$this->user_ip = $this->getIP();
				
			
			}		
		
		private function getIP() {
			$ip = '';
			if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
			else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
			else
			$ip = "UNKNOWN";
			return $ip;
		}
		
		private function register_click(){
			$matchtype_desc = $this->matchtype_desc;
			$matchtype = $this->matchtype;
			$mt = $matchtype_desc[$matchtype];
			
			$network_desc = $this->network_desc;
			$network = $this->network;
			$n = $network_desc[$network];
			
			$device_desc = $this->device_desc;
			$device = $this->device;
			$d = $device_desc[$device];
			
			$this->connect();
			$sql = "INSERT INTO Adwords_clicks (matchtype, network, device, devicemodel, creative, keyword, placement, target, aceid, c_date, user_ip)
								VALUES (";
			$sql .= "'".(empty($this->matchtype)? 'NA' : $this->matchtype) ."', ";
			$sql .= "'".(empty($this->network)? 'NA' : $this->network) ."', ";
			$sql .= "'".(empty($this->device)? 'NA' : $this->device) ."', ";
			$sql .= "'".(empty($this->devicemodel)? 'NA' : $this->devicemodel) ."', ";
			$sql .= "'".$this->creative ."', ";
			$sql .= "'".(empty($this->keyword)? 'NA' : $this->keyword) ."', ";
			$sql .= "'".(empty($this->placement)? 'NA' : $this->placement) ."', ";
			$sql .= "'".(empty($this->target)? 'NA' : $this->target) ."', ";
			//$sql .= "'".$this->aceid ."', ";
			$sql .= "'NA', ";
			$sql .= 'NOW(), ';
			$sql .= "'".$this->user_ip ."') ";
			$qry = mysql_query ($sql) or die ('ERROR registering click information: '.mysql_error());
			if($qry)return true; else return false;
			
			}
}// end of class



?>