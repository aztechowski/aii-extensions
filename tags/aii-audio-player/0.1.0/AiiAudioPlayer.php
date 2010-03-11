<?php
	/**
	 * This Widget is using Audio Player Wordpress plugin from 1 pixel out
	 * {@link http://www.1pixelout.net/code/audio-player-wordpress-plugin/}
	 * This widget concerns using aforementioned player for non-Wordpress projects
	 * 
	 * To see more information about using aforementioned player for non-Wordpress project, 
	 * please see {@link http://www.macloo.com/examples/audio_player/}
	 * 
	 * To see more inormation about options of Audio Player Wordpress plugin
	 * read tutorial "Customizing Audio Player" 
	 * {@link http://www.macloo.com/examples/audio_player/options.html}
	 * 
	 * @author Tomasz Suchanek <tomasz.suchanek@gmail.com>
	 * @copyright Copyright &copy; 2008-2010 Tomasz "Aztech" Suchanek
	 * @license http://www.yiiframework.com/license/
	 * @package aii.extensions
	 * @version 0.1.0
	 */
  class AiiAudioPlayer extends CWidget
  {
  	
  	/**
  	 * @var string - Player Id (needed when using multiple players on one site)
	 * default to 'audioplayer'
  	 */
  	public $playerID ='audioplayer';
  	
  	/**
  	 * 
  	 * @var string - hex value - Background color string e.g. 
  	 */
  	public $bg;
  	
  	/**
  	 * 
  	 * @var string - hex value - Left background
  	 */
  	public $leftbg;
  	/**
  	 * 
  	 * @var string - hex value - Left icon 
  	 */
  	public $lefticon;
  	
  	/**
  	 * 
  	 * @var string - hex value - The color the right background will change to on mouseover
  	 */
  	public $rightbg;
  	
  	/**
  	 * 
  	 * @var string - hex value -The color the right background will change to on mouseover
  	 */
  	public $rightbghover;
  	
  	/**
  	 * 
  	 * @var string - hex value - Right icon
  	 */
  	public $righticon;
  	
    /**
     * 
     * @var string - hex value - The color the right icon will change to on mouseover
     */  	
  	public $righticonhover;

  	/**
  	 * 
  	 * @var string - hex value - The color of text 
  	 */
  	public $text;
  	
  	/**
  	 * 
  	 * @var string - hex value - The color of slider
  	 */
  	public $slider;
  	
  	/**
  	 * 
  	 * @var string - hex value - unknown_type
  	 */
  	public $track;
  	
  	/**
  	 * 
  	 * @var string - hex value - This is the line surrounding the loader bar
  	 */
  	public $border;
  	
  	
    /**
     * 
     * @var string - hex vlaue - This is color of loader
     */
  	public $loader;

  	/**
  	 * 
  	 * @var boolean - Should mp3 looping all the time?
	 * Default to false
  	 */
  	public $loop = false;
  	
  	/**
  	 * 
  	 * @var boolean - Should mp3 start just after loading?
	 * Default to false
  	 */
  	public $autostart = false;

  	/**
  	 * 
  	 * @var string - Mp3 file name (including extension) from folder {@link mp3Folder}
  	 */
    public $mp3;  	
	
  	/**
  	 * 
  	 * @var integer - object height, default to 24
	 * Please change this value, when using own CSS, 
	 * where players height differs from default
  	 */	
	public $height = 24;
	
  	/**
  	 * 
  	 * @var integer - object width, default to 480
	 * Please change this value, when using own CSS, 
	 * where players width differs from default
  	 */		
	public $width = 480;
  	
    /**
     * 
     * @var string - JS File
     */
  	private $playerJSFile = 'audio-player.js';
  	
  	/**
  	 * 
  	 * @var string - SWF player file
  	 */
  	private $playerSWFFile = 'player.swf';
  	
  	/**
  	 * 
  	 * @var string - Publised folder with mp3 files
	 * Default to null, which means that standard 'mp3' folder under
	 * extension directory will be published
  	 */
  	protected $mp3Folder = null;
  	
  	/**
  	 * 
  	 * @var string - Published folder with assets 
	 * Default to null, which means that standard 'assets' folder under
	 * extension directory will be published
  	 */
  	protected $assetsFolder = null;
  	
  	
  	/**
  	 * If param value is not set param name points also to class variable.
  	 * @param $name
  	 * @param $value - optional
  	 * @return string options
  	 */
  	private function buildOption($name,$amp=true,$value=null)
  	{
  		$optionValue = ($value !== null) ? $value : $this->{$name};
  		$ampStr = ($amp === true) ? '&amp;' : '';
  		return empty($optionValue) ? '' : $ampStr.$name.'='.$optionValue;
  	}
  	
  	/**
  	 * (non-PHPdoc)
  	 * @see web/widgets/CWidget#init()
  	 */
    public function init()
    {
		$basePath=dirname(__FILE__);
    	
    	#publish assets folder
		if ( $this->assetsFolder === null )
		{
			$assets = $basePath.DIRECTORY_SEPARATOR.'assets';
			$this->assetsFolder = Yii::app()->getAssetManager()->publish( $assets );
			
			#check if assets are published
			if ( $this->getPublishedPath( $assets ) === false )
				throw new CException( Yii::t( 'azt-apwp' , 'Assets folder "{folder}" doesn\'t exist. Please update component!' );		
		}
        
		#publish mp3 folder
		if ( $this->mp3Folder === null )
		{
			$mp3Folder = $basePath.DIRECTORY_SEPARATOR.'mp3';
			$this->mp3Folder = Yii::app()->getAssetManager()->publish( $mp3Folder );
			
			#check if mp3 is published
			if ( $this->getPublishedPath( $mp3Folder ) === false )
				throw new CException( Yii::t( 'azt-apwp' , 'Mp3 folder "{folder}" doesn\'t exist. Please create it!' );
		}
      
    	#register JS File
		$this->playerJSFile=CHtml::asset( $assets.DIRECTORY_SEPARATOR.$this->playerJSFile );
		$cs = Yii::app()->clientScript; 
		if($cs->isScriptRegistered( $this->playerJSFile ) === false )     
			$cs->registerScriptFile( $this->playerJSFile );
        
      parent::init();
    }
  	
  	/**
  	 * (non-PHPdoc)
  	 * @see web/widgets/CWidget#run()
  	 */
  	public function run()
  	{
  		#first prepare flash variables basing on options
  		# - required flash variables
  		$flashVars = $this->buildOption( 'playerID' , false );
		
  		# - optional flash variables
  		$flashVars .= $this->buildOption( 'bg' );
  		$flashVars .= $this->buildOption( 'leftbg' );
  		$flashVars .= $this->buildOption( 'lefticon' );
  		$flashVars .= $this->buildOption( 'rightbg' );
  		$flashVars .= $this->buildOption( 'rightbghover' );
		$flashVars .= $this->buildOption( 'righticon' );
  		$flashVars .= $this->buildOption( 'righticonhover' );
  		$flashVars .= $this->buildOption( 'text' );
  		$flashVars .= $this->buildOption( 'slider' );
  		$flashVars .= $this->buildOption( 'track' );
  		$flashVars .= $this->buildOption( 'border' );
  		$flashVars .= $this->buildOption( 'loader' );
  		$flashVars .= $this->buildOption( 'loop' , $this->loop ? 'yes' : 'no' );
  		$flashVars .= $this->buildOption( 'autostart' , $this->autostart ? 'yes' : 'no' );
  		#mp3 file name
		$flashVars .= $this->buildOption( 'soundFile' , true , $this->mp3Folder.'/'.$this->mp3 ); 
  		#render
		$this->renderContent( $flashVars );
		$this->render(
			'_mp3',
			array(
				'playerJSFile' => $this->playerJSFile,
				'playerSWFFile' => $this->assetsFolder.'/'.$this->playerSWFFile,
				'playerId' => $this->playerID,
				'flashVars' => $flashVars,
				'measures' => array( 'height' => $this->height , 'width' => $this->width }
			)
  	   );
  	}
	
	private function renderParam( $name , $value )
	{
		#note that param tags are not closed if embeded in object	
		return CHtml::openTag( 'param' , array( 'name' => $name, 'value' => $value ) );
	}
	
	protected function rendercontent( $flashVars )
	{
		if ( empty( $this->height ) )
			throw new CException( Yii::t( 'azt-apwp' , 'Height can\'t be empty' ) );
			
		if ( empty( $this->width ) )
			throw new CException( Yii::t( 'azt-apwp' , 'Width can\'t be empty' ) );
			
		echo CHtml::openTag( 'object' , array( 
			'type' => 'application/x-shockwave-flash',
			'id' => $this->playerId,
			'data' => $this->playerSWFFile,
			'height' => $this->height,
			'width' => $this->width
		) );
		echo $this->renderParam( 'movie' , $this->playerSWFFile );
		echo $this->renderParam( 'FlashVars' , $flashVars );
		echo $this->renderParam( 'quality' , 'high' );
		echo $this->renderParam( 'menu' , 'false' );
		echo $this->renderParam( 'wmode' , 'transparent' );
		echo CHtml::closeTag( 'object' );
	}
  }
?>