<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/Plugins/JoomColorbox/trunk/joomcolorbox.php $
// $Id: joomcolorbox.php 4098 2013-02-15 13:11:01Z erftralle $
/******************************************************************************\
**   JoomGallery Plugin 'Integrate Colorbox'                                  **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2013 JoomGallery::ProjectTeam                              **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html                            **
\******************************************************************************/

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_joomgallery/helpers/openimageplugin.php';

/**
 * JoomGallery Plugin 'Integrate Colorbox'
 *
 * With this plugin JoomGallery is able to use the Colorbox Javascript library
 * (http://www.jacklmoore.com/colorbox) for displaying images.
 *
 * NOTE: Please remember that Colorbox is licensed under the terms of the
 * MIT license: http://opensource.org/licenses/mit-license.php
 *
 * @package     Joomla
 * @subpackage  JoomGallery
 * @since       3.0
 */
class plgJoomGalleryJoomColorbox extends JoomOpenImagePlugin
{
  /**
   * Joomgallery configuration object
   *
   * @var   object
   * @since 3.0
   */
  private $_jg_config = null;

  /**
   * Name of this popup box
   *
   * @var   string
   * @since 3.0
   */
  protected $title = 'Colorbox';

  /**
   * Initializes the box by adding all necessary iamge group independent JavaScript and CSS files.
   * This is done only once per page load.
   *
   * @return  void
   * @since   3.0
   */
  protected function init()
  {
    JHtml::_('jquery.framework');

    $doc = JFactory::getDocument();

    // Add Colorbox CSS and JS
    $colorboxstyle = (int) $this->params->get('cfg_style', '1');
    $doc->addStyleSheet(JURI::root().'media/plg_joomcolorbox/style'.$colorboxstyle.'/colorbox.css');
    $doc->addScript(JURI::root().'media/plg_joomcolorbox/jquery.colorbox-min.js');

    // Get JoomGallery configuration
    $this->_jg_config = JoomConfig::getInstance();

    $this->loadLanguage();

    $script   = array();
    $script[] = "    var joomcolorbox_onkeydownsave = null;";
    $script[] = "    var style                      = ".$colorboxstyle.";";
    $script[] = "    var joomcolorbox_resizeJsImage = ".$this->_jg_config->get('jg_resize_js_image').";";
    $script[] = "    var joomcolorbox_image         = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_IMAGE', true)."';";
    $script[] = "    var joomcolorbox_of            = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_OF', true)."';";
    $script[] = "    var joomcolorbox_close         = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_CLOSE', true)."';";
    $script[] = "    var joomcolorbox_prev          = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_PREVIOUS', true)."';";
    $script[] = "    var joomcolorbox_next          = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_NEXT', true)."';";
    $script[] = "    var joomcolorbox_startsld      = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_STARTSLD', true)."';";
    $script[] = "    var joomcolorbox_stopsld       = '".JText::_('PLG_JOOMGALLERY_JOOMCOLORBOX_POPUP_STOPSLD', true)."';";
    $script[] = "    jQuery(document).bind('cbox_complete', function(){";
    $script[] = "      if(jQuery('#cboxTitle').height() > 0 && (style == 1 || style == 4 || style == 5)){";
    $script[] = "        jQuery('#cboxTitle').hide();";
    $script[] = "        jQuery('#cboxTitleLong').remove();";
    $script[] = "        jQuery('<div id=\"cboxTitleLong\">' + jQuery('#cboxTitle').html() + '</div>').insertAfter('.cboxPhoto');";
    $script[] = "        jQuery.fn.colorbox.resize();";
    $script[] = "      }";
    $script[] = "    });";
    $script[] = "    jQuery(document).bind('cbox_open', function(){";
    $script[] = "      joomcolorbox_onkeydownsave = document.onkeydown;";
    $script[] = "      document.onkeydown         = null;";
    $script[] = "    });";
    $script[] = "    jQuery(document).bind('cbox_closed', function(){";
    $script[] = "      document.onkeydown = joomcolorbox_onkeydownsave;";
    $script[] = "    });";
    $script[] = "    jQuery(document).ready(function(){";
    $script[] = "      var sstr = 'colorbox';";
    $script[] = "      jQuery('a[rel^=' + sstr + ']').each(function(){";
    $script[] = "        this.rel = this.rel.substr(sstr.length + 1 );";
    $script[] = "        jQuery(this).addClass(sstr + '-' + this.rel);";
    $script[] = "      });";
    $script[] = "    });";

    $doc->addScriptDeclaration(implode("\n", $script));
  }

  /**
   * This method sets an associative array of attributes for the 'a'-Tag (key/value pairs)
   * which opens the image and adds some image group specific JavaScript code fot the Colorbox.
   *
   * @param   array   $attribs  Associative array of HTML attributes which you have to fill
   * @param   object  $image    An object holding all the relevant data about the image to open
   * @param   string  $img_url  The URL to the image which shall be openend
   * @param   string  $group    The name of an image group, most popup boxes are able to group the images with that
   * @param   string  $type     'orig' for original image, 'img' for detail image or 'thumb' for thumbnail
   * @return  void
   * @since   3.0
   */
  protected function getLinkAttributes(&$attribs, $image, $img_url, $group, $type)
  {
    static $initGroup = array();

    if(!isset($initGroup[$group]))
    {
      $doc = JFactory::getDocument();

      $script   = array();
      $script[] = "    jQuery(document).ready(function(){";
      $script[] = "      opacity = ".$this->params->get('cfg_opacity', 0.9).";";
      $script[] = "      jQuery('.colorbox-' + '".$group."').colorbox({ photo:true,";
      $script[] = "                                                     transition: '".$this->params->get('cfg_transition', 'elastic')."',";
      $script[] = "                                                     speed: ".$this->params->get('cfg_transitionspeed', 300).",";
      $script[] = "                                                     opacity: (opacity >= 0 && opacity <= 1) ? opacity : 0.9,";
      $script[] = "                                                     initialWidth: '160',";
      $script[] = "                                                     initialHeight: '120',";
      $script[] = "                                                     scrolling: false,";
      $script[] = "                                                     preloading: ".($this->params->get('cfg_preloading', 0) ? 'true' : 'false').",";
      $script[] = "                                                     loop: ".($this->params->get('cfg_loop', 0) ? 'true' : 'false').",";
      $script[] = "                                                     slideshow: ".($this->params->get('cfg_slideenable', 0) ? 'true' : 'false').",";
      $script[] = "                                                     slideshowSpeed: ".$this->params->get('cfg_slideinterval', 4000).",";
      $script[] = "                                                     slideshowAuto: ".($this->params->get('cfg_slideautostart', 0) ? 'true' : 'false').",";
      $script[] = "                                                     slideshowStart: joomcolorbox_startsld,";
      $script[] = "                                                     slideshowStop: joomcolorbox_stopsld,";
      $script[] = "                                                     maxHeight: '90%',";
      $script[] = "                                                     scalePhotos: joomcolorbox_resizeJsImage ? true : false,";
      $script[] = "                                                     current: joomcolorbox_image + ' {current} ' + joomcolorbox_of + ' {total}',";
      $script[] = "                                                     previous: joomcolorbox_prev,";
      $script[] = "                                                     next: joomcolorbox_next,";
      $script[] = "                                                     close: joomcolorbox_close,";
      $script[] = "                                                     rel:'".$group."'";
      $script[] = "                                                   });";
      $script[] = "    });";

      $doc->addScriptDeclaration(implode("\n", $script));

      $initGroup[$group] = true;
    }

    $attribs['rel'] = 'colorbox.'.$group;
  }
}