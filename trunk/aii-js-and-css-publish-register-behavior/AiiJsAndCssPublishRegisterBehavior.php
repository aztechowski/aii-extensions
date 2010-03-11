<?php

/**
 * This behavior implements logic supporting publishing JS and CSS assets
 * Note that if under {@link assetsPath}
 * 
 * @author Tomasz Suchanek <tomasz.suchanek@gmail.com>
 * @copyright Copyright &copy; 2010 Tomasz "Aztech" Suchanek
 * @link http://code.google.com/p/aii-extensions
 * @license http://www.yiiframework.com/license/
 * @package aii.extensions
 * @version 0.1.0
 **/
class AiiJsAndCssPublishRegisterBehavior extends CBehavior
{	
	
	/**
	 * @var message category
	 */
	const MSG_CAT = 'aii-js-and-css-publish-register-behavior';	
	
	/**
	 * @var CClientScript client script
	 */
	private $_cs;
	
	/**
	 * @var CAssetManager asset manager
	 */
	private $_am;
	
	/**
	 * @var string equals to initial value of {@link jsPath} if {assets} reference were used
	 */
	private $_jsPathTemplate;

	/**
	 * @var string equals to initial value of {@link cssPath} if {assets} reference were used
	 */
	private $_cssPathTemplate;
	
	/**
	 * @var string folder path to assets
	 * You can use here {basePath} placeholder
	 * Set false if content shouldn't be published
	 * Default to null, meaning path '{basePath}{/}assets' will be used
	 */
	public $assetsPath = null;
	
	/**
	 * @var string folder path to css
	 * You can use here {assets} and {basePath} placeholders
	 * Set false if no css should be published
	 * Default to null, meaning path '{assets}{/}css' will be used
	 */
	public $cssPath = null;
	
	/**
	 * @var string folder path to js
	 * You can use here {assets} and {basePath} placeholders
	 * Set false if no JS should be published
	 * Default to null, meaning path '{assets}{/}js' will be used
	 */
	public $jsPath = null;
	
	/**
	 * @var array css files under {@link cssPath} to register
	 */
	public $cssToRegister = array( );
	
	/**
	 * @var array js files under {@link jsPath} to register
	 */
	public $jsToRegiser = array( );
	
	/**
	 * @var array core scripts to register
	 */
	public $coreScriptsToRegister = array( );
	
	/**
	 * @var boolean if set to false {@link assetsPath}, {@link cssPath} and {@link jsPath}
	 * are already published paths 
	 * Set to true, if you want to pass already published paths
	 */
	public $pathsPublished = false;
	
	/**
	 * @var integer, the position of the JavaScript code, default  1, meaning HEAD
	 */
	public $jsDefaultPos = CClientScript::POS_HEAD;
	
	/**
	 * 
	 * @var string default media type, defualt empty meaning all media types
	 */
	public $defaultMedia = '';
	
	/**
	 * @var boolean, set to true if assets should be shared among other extensions
	 */
	public $share = false;
	
	/**
	 * This methid publish all needed assets for specified CSS and JS fiels.
	 * First core scripts are registered later CSS and JS files
	 * @return boolean, false if nothing was published
	 */
	public function publishAndRegister( )
	{
		$this->_cs = Yii::app()->clientScript;
		$this->_am  = Yii::app()->getAssetsMaanger();
		$this->buildPaths( );
		
		return $this->registerCoreScripts( ) || $this->registerFromAssets( );
	}
	
	/**
	 * Used internal, build paths, by replacing placeholders {@link assetsPath}, {@link cssPath} and {@link jsPath}
	 */
	protected function buildPaths( )
	{
		if ( $this->pathsPublished === false )
		{
			#set defualts value if needed
			if ( $this->assetsPath === null )
				$this->assetsPath = '{basePath}{/}assets';
			if ( $this->cssPath === null )
				$this->cssPath = '{assets}{/}css';
			if ( $this->jsPath === null )
				$this->jsPath = '{assets}{/}js';
			
			#create real paths when placeholders used
			$tr['{basePath}'] = dirname( get_class ( $this->owner ) );
			$this->assetsPath = strtr( $this->assetsPath, $tr );
			
			$tr['{assets}'] = $this->assetsPath;
			$cssPath = strtr( $this->cssPath , $tr );
			$this->_csPathTemplate = ( $cssPath === $this->cssPath ) ? $this->cssPath : null;
			$this->cssPath = $cssPath;
			$jsPath = strtr( $this->jsPath , $tr );
			$this->_jsPathTemplate = ( $jsPath === $this->jsPath ) ? $this->jsPath : null;
			$this->jsPath = $jsPath;
		}
		else
			Yii::trace( Yii::t( self::MSG_CAT , 'Assets already published under: "{path}"' , array( '{path}' => $this->assetsPath ) ) );		
	}
	
	/**
	 * used internally for registering core scripts
	 * @return boolean, false if nothing were registered
	 */
	protected function registerCoreScripts( )
	{
		if ( empty( $this->coreScriptsToRegister ) )
			Yii::trace( Yii::t( self::MSG_CAT , 'No core scripts to register' ) );
		else		
		{
			foreach ( $this->coreScriptsToRegister as $coreScript )
			{
				$this->_cs->registerCoreScript( $coreScript );
				Yii::trace( Yii::t( self::MSG_CAT , 'Core script  "{script}" registered.' , array( '{script}' => $coreScript ) ) );
			}
			return true;
		}	
		return false;
	}
	
	/**
	 * used internally for registering CSS and JS scripts
	 * @return boolean, false if there is nothing to register
	 */
	protected function registerFromAssets( )
	{
		if ( $this->cssPath === null && $this->jsPath === null )
		{
			Yii::trace( Yii::t( self::MSG_CAT , 'There is nothing to publish' ) );
			return false;
		}		
		return $this->registerCss( ) || $this->registerJs( );
	}
	
	/**
	 * All css files form {@link cssToRegister} are registered
	 * If {@link cssPath} is not published it is published here
	 * If {@link cssPath} is a subfolder of {@link assetsPath} later is published
	 * If {@link pathsPublished} it means that {@link cssPath} is already published
	 * @return boolean, false if there is nothing to register
	 */
	protected function registerCss( )
	{
		if ( !empty( $this->cssToRegister ) )
		{
			if ( $this->pathsPublised === false  )
			{
				#is it subfolder of published asset folder?
				if ( $this->_cssPathTemplate )
				{
					$pubAssets = CHtml::asset( $this->assetsPath , $this->share );
					$cssPub = strtr( $this->_cssPathTemplate , '{assets}' , $pubAssets );				
				}
				else
					$cssPub = CHtml::asset( $this->cssPath , $this->share );
			}
			else
				$cssPub = $this->cssPath;
				
			#register all CSS files
			foreach ( $this->cssToRegister  as $cssFile )
			{
				#css file name
				$cssFileName = is_string( $cssFile ) ? $cssFile : $cssFile['name'];
				#position where it should ne registered
				$media = isset( $cssFile['media'] ) ? $cssFile['media'] : $this->defaultMedia;
				#published resource 
				$cssPubFile = $cssPub.DIRECTORY_SEPARATOR.$cssFileName;
				if ( !$this->_cs->isCssFileRegistered( $cssPubFile , $media) )
				{
					$this->_cs->registerScript( $jcssPubFile , $media );
					Yii::trace( 
						Yii::t( self::MSG_CAT , 
							'Css file  "{css}" was registered as {regisered}.' , 
							array( '{css}' => $cssFileName , '{registered}' => $cssPubFile, 
					) ) );					
				}
			}
			return true;	
		}
		else
			return false;
	}
	
	/**
	 * All css files form {@link cssToRegister} are registered
	 * If {@link cssPath} is not published it is published here
	 * If {@link cssPath} is a subfolder of {@link assetsPath} later is published
	 * If {@link pathsPublished} it means that {@link cssPath} is already published
	 * @return boolean, false if nothing were registered
	 */	
	protected function registerJs( )
	{
		if ( !empty( $this->jsToRegister ) )
		{
			if ( $this->pathsPublished === false )
			{
				#is it subfolder of published asset folder?
				if ( $this->_jsPathTemplate )
				{
					$pubAssets = CHtml::asset( $this->assetsPath , $this->share );
					$jsPub = strtr( $this->_jsPathTemplate , '{assets}' , $pubAssets );				
				}
				else
					$jsPub = CHtml::asset( $this->jsPath , $this->share );
			}
			else
				$jsPub = $this->jsPath;	
				
			#register all JS files
			foreach ( $this->jsToRegister  as $jsFile )
			{
				#js file name
				$jsFileName = is_string( $jsFile ) ? $jsFile : $jsFile['name'];
				#position where it should ne registered
				$jsPos = isset( $jsFile['pos'] ) ? $jsFile['pos'] : $this->jsDefaultPos;
				#published resource 
				$jsPubFile = $jsPub.DIRECTORY_SEPARATOR.$jsFileName;
				if ( !$this->_cs->isScriptRegistered( $jsPubFile , $jsPos ) )
				{
					$this->_cs->registerScript( $jsPubFile , $jsPos );
					Yii::trace( 
						Yii::t( self::MSG_CAT , 
							'JS file  "{js}" was registered as {regisered}.' , 
							array( '{js}' => $jsFileName , '{registered}' => $jsPubFile, 
					) ) );					
				}
			}
			return true;
		}
		
		return false;
	}
}
?>