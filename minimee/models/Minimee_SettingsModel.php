<?php namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @author     	John D Wells <http://johndwells.com>
 * @package    	Minimee
 * @since		Craft 1.3
 * @copyright 	Copyright (c) 2014, John D Wells
 * @license 	http://opensource.org/licenses/mit-license.php MIT License
 * @link       	http://github.com/johndwells/Minimee-Craft
 */

/**
 *
 */
class Minimee_SettingsModel extends BaseModel implements Minimee_ISettingsModel
{
	/**
	 * @return string
	 */
	public function __toString()
	{
		return ($this->enabled) ? '1' : '0';
	}

	public function prepSettings($settings)
	{
		// cast any booleans
		$settings['enabled'] = (bool)$settings['enabled'];
		$settings['combineCssEnabled'] = (bool)$settings['combineCssEnabled'];
		$settings['combineJsEnabled'] = (bool)$settings['combineJsEnabled'];
		$settings['minifyCssEnabled'] = (bool)$settings['minifyCssEnabled'];
		$settings['minifyJsEnabled'] = (bool)$settings['minifyJsEnabled'];
		$settings['cssPrependUrlEnabled'] = (bool)$settings['cssPrependUrlEnabled'];

		return $settings;
	}

	/**
	 * Add custom validation rules to routine.
	 *
	 * @param Array $attributes
	 * @param Bool $clearErrors
	 * @return Bool
	 */
	public function validate($attributes = null, $clearErrors = true)
	{
		if ($clearErrors)
		{
			$this->clearErrors();
		}

		$this->validateCachePathAndUrl();

		return parent::validate($attributes, false);
	}

	/**
	 * Validate that cachePath and cacheUrl are both empty or non-empty.
	 *
	 * @return boolean|null
	 */
	public function validateCachePathAndUrl()
	{
		$cachePath = $this->getCachePath();
		$cacheUrl = $this->getCacheUrl();

		$cachePathEmpty = empty($cachePath);
		$cacheUrlEmpty = empty($cacheUrl);

		if ($cachePathEmpty != $cacheUrlEmpty)
		{
			$this->addError('cachePath', Craft::t('Minimee\'s cachePath and cacheUrl must both either be empty or non-empty.'));
			$this->addError('cacheUrl', Craft::t('Minimee\'s cachePath and cacheUrl must both either be empty or non-empty.'));
		}
	}

	/**
	 * @return Array
	 */
	public function defineAttributes()
	{
		return array(
			'cachePath'       	    => AttributeType::String,
			'cacheUrl'       	    => AttributeType::String,
			'enabled'               => array(AttributeType::Bool, 'default' => true),
			'filesystemPath'        => AttributeType::String,
			'baseUrl'               => AttributeType::String,
			'combineCssEnabled'	    => array(AttributeType::Bool, 'default' => true),
			'combineJsEnabled' 	    => array(AttributeType::Bool, 'default' => true),
			'minifyCssEnabled'	    => array(AttributeType::Bool, 'default' => true),
			'minifyJsEnabled'	    => array(AttributeType::Bool, 'default' => true),
			'cssReturnTemplate'     => array(AttributeType::String, 'default' => '<link rel="stylesheet" href="%s">'),
			'jsReturnTemplate' 	    => array(AttributeType::String, 'default' => '<script src="%s"></script>'),
			'returnType'		    => array(AttributeType::String, 'default' => 'url'),
			'cssPrependUrlEnabled'	=> array(AttributeType::Bool, 'default' => true),
			'cssPrependUrl'         => array(AttributeType::String, 'default' => '')
		);
	}

	/**
	 * @param String $string
	 * @return String
	 */
	public function forceTrailingSlash($string)
	{
		return rtrim($string, '/') . '/';
	}

	/**
	 * @return String
	 */
	public function getFilesystemPath()
	{
		$value = parent::getAttribute('filesystemPath');

		if ($value)
		{
			$filesystemPath = craft()->config->parseEnvironmentString($value);
		}
		else
		{
			$filesystemPath = $_SERVER['DOCUMENT_ROOT'];
		}

		return $this->forceTrailingSlash($filesystemPath);
	}

	/**
	 * @return false|string
	 */
	public function getCachePath()
	{
		$value = parent::getAttribute('cachePath');

		if (!$value)
		{
			return false;
		}

		$cachePath = craft()->config->parseEnvironmentString($value);

		return $this->forceTrailingSlash($cachePath);
	}

	/**
	 * @return false|string
	 */
	public function getCacheUrl()
	{
		$value = parent::getAttribute('cacheUrl');

		if (!$value)
		{
			return false;
		}

		$cacheUrl = craft()->config->parseEnvironmentString($value);

		return $this->forceTrailingSlash($cacheUrl);
	}

	/**
	 * @return false|string
	 */
	public function getCssPrependUrl()
	{
		$value = parent::getAttribute('cssPrependUrl');

		if (!$value)
		{
			return false;
		}

		$cssPrependUrl = craft()->config->parseEnvironmentString($value);

		return $this->forceTrailingSlash($cssPrependUrl);
	}

	/**
	 * @return Bool
	 */
	public function useResourceCache()
	{
		$cachePath = $this->getCachePath();
		$cacheUrl = $this->getCacheUrl();

		$cachePathEmpty = empty($cachePath);
		$cacheUrlEmpty = empty($cacheUrl);

		return ($cachePathEmpty && $cacheUrlEmpty);
	}

	/**
	 * @return String
	 */
	public function getBaseUrl()
	{
		$value = parent::getAttribute('baseUrl');

		if ($value)
		{
			$baseUrl = craft()->config->parseEnvironmentString($value);
		}
		else
		{
			$baseUrl = craft()->getSiteUrl();
		}

		return $this->forceTrailingSlash($baseUrl);
	}

	public function getCssReturnTemplate()
	{
		$value = parent::getAttribute('cssReturnTemplate');

		if (!$value)
		{
			$attributes = $this->defineAttributes();
			return $attributes['cssReturnTemplate']['default'];
		}

		return $value;
	}

	public function getJsReturnTemplate()
	{
		$value = parent::getAttribute('jsReturnTemplate');

		if (!$value)
		{
			$attributes = $this->defineAttributes();
			return $attributes['jsReturnTemplate']['default'];
		}

		return $value;
	}

	public function getReturnType()
	{
		$value = parent::getAttribute('returnType');

		if (!$value)
		{
			$attributes = $this->defineAttributes();
			return $attributes['returnType']['default'];
		}

		return $value;
	}

	/**
	 * Inject our model attribute accessors.
	 *
	 * @param String $name
	 * @return String|Bool
	 */
	public function getAttribute($name)
	{
		switch ($name) :

			case('baseUrl') :
				return $this->getBaseUrl();

			case('cachePath') :
				return $this->getCachePath();

			case('cacheUrl') :
				return $this->getCacheUrl();

			case('cssReturnTemplate') :
				return $this->getCssReturnTemplate();

			case('cssPrependUrl') :
				return $this->getCssPrependUrl();

			case('filesystemPath') :
				return $this->getFilesystemPath();

			case('jsReturnTemplate') :
				return $this->getJsReturnTemplate();

			case('returnType') :
				return $this->getReturnType();

			default :
				return parent::getAttribute($name);

		endswitch;
	}
}
