<?php
/**
 * @version		$Id$
 * @author		NooTheme
 * @author		modified by JoomDev (Formerly GraphicAholic)
 * @package		Joomla.Site
 *
 * @copyright	Copyright (C) 2013 NooTheme. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt, see LICENSE.php
 */
if (isset($_REQUEST['gaaction'])){
	if (!defined('_JEXEC')) {
    define('_JEXEC', 1);}
	$path = dirname(dirname(dirname(dirname(__FILE__))));
    if (!defined('JPATH_BASE'))
    	define('JPATH_BASE', $path);
    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';
    // Mark afterLoad in the profiler.
	JDEBUG ? $_PROFILER->mark('afterLoad') : null;
	// Instantiate the application.
	$app = JFactory::getApplication('site');
	// Initialise the application.
	$app->initialise();
	$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : false;
	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');
	jimport('joomla.application.module.helper');
	class gaImageFolderAction {
		var $moduleName = '';
		function __construct(){
			$this->moduleName = basename(dirname(__DIR__));
		}
		private function basePath(){
			if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
			{
				// PHP-CGI on Apache with "cgi.fix_pathinfo = 0"
				// We shouldn't have user-supplied PATH_INFO in PHP_SELF in this case
				// because PHP will not work with PATH_INFO at all.
				$script_name = $_SERVER['PHP_SELF'];
			}
			else
			{
				// Others
				$script_name = $_SERVER['SCRIPT_NAME'];
			}
			return rtrim(dirname(dirname(dirname(dirname($script_name)))), '/\\');
		}
		private function getModule($mid){
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params');
			$query->from('#__modules AS m');
			$query->where('m.id = '.$mid);
			$db->setQuery($query);
			$module = $db->loadObject ();
			return $module;
		}
	function getListImage(){
			$input = JFactory::getApplication()->input;
			$folder = $input->getString('folder');
			$mid = $input->getInt('mid');
			$fieldname = $input->getString('fieldname');
			$imageList = array();
			$success = false;
			$path = JPath::clean(JPATH_ROOT . '/' . $folder);
			$module = $this->getModule($mid);
			$params = new JRegistry();
			$paramString = isset($module->params) ? $module->params : '';
			$params->loadString($paramString);
			$imagesCurr = json_decode($params->get($fieldname.'.images'),true);
			$folderCurr = $params->get($fieldname.'.folder');															
			if (JFolder::exists($path)) {
				$files = JFolder::files($path);
				$i = 0;
				foreach ($files as $file) {
					if (is_file($path.'/'.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html'){
	//					$i++;
	//					if($i > $params->get('count', 5)) break;
						$ext = JFile::getExt($file);
						switch ($ext) {
							// Image
							case 'jpg':
							case 'png':
							case 'gif':
							case 'xcf':
							case 'odg':
							case 'bmp':
							case 'jpeg':
							case 'JPG':
							case 'JPEG':
							case 'ico':
								$image = $folder . '/' . $file;
								$tmp = array();
								$nameArr = explode('.',$file);
								$name = $nameArr[0];
								$tmp['image'] = $file;
								$tmp['description'] = '';
								$tmp['title'] = '';
								$tmp['alttag'] = '';
								$tmp['Imagelink'] = '';
								$tmp['gatarget'] = '';
								$tmp['link'] = '';
								$tmp['tag'] = '';
								$tmp['gagray'] = '';
								$tmp['imagesrc'] = $image;
								$imageList[$name] = $tmp;
								break;
						}
					}
				$i++;
				}
			}
			$html = '';
			if (count($imageList)){
				$success = true;
				//$html .='<div id="gaSort" class="gridly">';
				$imgArr = array();
				$flag = false;
				if (($folderCurr == $folder) && is_array($imagesCurr)){
					$flag = true;
					foreach ($imagesCurr as $k=>$v){
						$v['key'] = $k;
						if (isset($v['position'])){
							$imgArr[$v['position']] = $v;
						}
					}
				}			
				ksort($imgArr);
				$i=0;
				//var_dump($imageList);				
				foreach ($imgArr as $k=>$img){
					if (JFile::exists(JPATH_ROOT.'/'.$folder.'/'.$img['image'])){											
						$nameArr = explode('.',$img['image']);
						$key = 
						$html .= '<div class="ga-img brick small">
							<div>
							<img data-image="'.$img['image'].'" data-name="'.$nameArr[0].'" data-description="'.(isset($img['description']) ?  htmlspecialchars($img['description']) :'' ).'" data-title="'.(isset($img['title']) ? $img['title'] :'' ).'" data-tag="'.(isset($img['tag']) ? $img['tag'] :'' ).'" data-gagray="'.(isset($img['gagray']) ? $img['gagray'] :'' ).'" data-alttag="'.(isset($img['alttag']) ? $img['alttag'] :'' ).'" data-gatarget="'.(isset($img['gatarget']) ? $img['gatarget'] :'' ).'" data-imagelink="'.(isset($img['imagelink']) ? $img['imagelink'] :'' ).'" data-link="'.(isset($img['link']) ? $img['link'] :'' ).'"  data-imagesrc="'.$img['imagesrc'].'" style="max-width: 100px; max-height: 90px;" src="'.$this->basePath().'/'.$folder.'/'.$img['image'].'">
							</div>														
							<div class="ga-showtitle" style="font-size: 0.9em;">
							'.$img['title'].'							
							<br>							
							</div>							
							<div class="ga-img-btn">
								<a href="javascript:void(0)" onclick="gaModal('.$i.')" title="Edit"><i class="icon-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" title="Delete" onclick="gaDelete(\''.$img['image'].'\',this)"><i class="icon-delete" ></i>							
							</a>
							</div>
							</div>';
						if (isset($imageList[$img['key']]))
							unset($imageList[$img['key']]);
					}
					$i++;
				}
				foreach ($imageList as $k=>$img){
					if (JFile::exists(JPATH_ROOT.'/'.$folder.'/'.$img['image'])){
						$nameArr = explode('.',$img['image']);
						$html .= '<div class="ga-img brick small">
							<div>
							<img data-image="'.$img['image'].'" data-name="'.$nameArr[0].'" data-description="'.($flag && isset($imagesCurr[$k]['description']) ? htmlspecialchars($imagesCurr[$k]['description']) :'' ).'" data-title="'.($flag && isset($imagesCurr[$k]['title']) ? $imagesCurr[$k]['title'] :'' ).'" data-tag="'.($flag && isset($imagesCurr[$k]['tag']) ? $imagesCurr[$k]['tag'] :'' ).'" data-gagray="'.($flag && isset($imagesCurr[$k]['gagray']) ? $imagesCurr[$k]['gagray'] :'' ).'" data-alttag="'.($flag && isset($imagesCurr[$k]['alttag']) ? $imagesCurr[$k]['alttag'] :'' ).'" data-gatarget="'.($flag && isset($imagesCurr[$k]['gatarget']) ? $imagesCurr[$k]['gatarget'] :'' ).'" data-imagelink="'.($flag && isset($imagesCurr[$k]['imagelink']) ? $imagesCurr[$k]['imagelink'] :'' ).'" data-link="'.($flag && isset($imagesCurr[$k]['link']) ? $imagesCurr[$k]['link'] :'' ).'"  data-imagesrc="'.$img['imagesrc'].'" style="max-width: 100px; max-height: 100px;" src="'.$this->basePath().'/'.$img['imagesrc'].'">
							</div>
							<div class="ga-showtitle" style="font-size: 0.9em;">
							'.$img['title'].'
							<br>
							</div>
							<div class="ga-img-btn">
								<a href="javascript:void(0)" onclick="gaModal('.$i.')" title="Edit"><i class="icon-edit"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" title="Delete" onclick="gaDelete(\''.$img['image'].'\',this)"><i class="icon-delete" ></i></a>
							</div>							
							</div>';
					}
					$i++;
				}
			}
			//var_dump($imagesCurr);
			$return = array('imageHtml'=>$html,'success'=>$success);
			echo json_encode($return);
		}
		function deleteImage(){
			$input = JFactory::getApplication()->input;
			$success = false;
			$folder = $input->getString('folder');
			$image = $input->getString('image');
			$fullPath = JPATH_ROOT.'/'.$folder.'/'.$image;
			if (JFile::exists($fullPath)){
				if (JFile::delete($fullPath))$success = true;
			}
			$return = array('success'=>$success);
			echo json_encode($return);
		}
	}
	if ($task){
		$gaAction = new gaImageFolderAction();	
		$gaAction->$task();
	}
	exit();
}
jimport('joomla.filesystem.folder');
class JFormFieldgaImageFolder extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'gaImageFolder';
	/**
	 * The image config
	 */
	private $config = '';
	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   11.1
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);
	}
	/**
	 * Method add script to document.
	 */
	private function init(){
		$params = new JRegistry();
		$params->loadObject($this->form->getValue('params'));
		$this->config = $params->get($this->fieldname.'.images');
		$uri = str_replace("\\","/", str_replace(JPATH_SITE, JURI::root(true), dirname(__FILE__) ));
		$document = JFactory::getDocument();
		$document->addScript($uri.'/assets/js/jquery.gridly.js');
		$document->addStyleSheet($uri.'/assets/css/jquery.gridly.css');
		$document->addStyleDeclaration('
		#gaSort{
			display: inline-table;
		    list-style: none outside none;
		    margin: 0;
		    padding: 0;
		    position: relative;
		}
		.ga-img{
		 	border: 1px solid #CCCCCC;
		    cursor: move;	
		    margin: 5px;
		    padding: 1px;
		    text-align: center;
		    width: 100px;
		    z-index: 10;
		}
		.ga-img img{
		}
		  .gridly
		  {
		    position: relative;
		    width: 960px;
		  }
		  .brick.small
		  {
		    width: 110px;
		    height: 140px;
		  }
		  .ga-img-btn{}
		');
		$document->addScriptDeclaration('
		var GA_IMAGE_FOLDER_ACTION = "'.$uri.'/gaimagefolder.php?gaaction=folders";
		var GA_IMAGE_ID = "'.JFactory::getApplication()->input->getInt('id').'";
		var GA_IMAGE_FIELDNAME = "'.$this->fieldname.'";
		');
		$document->addScriptDeclaration('
		jQuery(document).ready(function(){
			gaListImages();
			var form = document.adminForm;
			if(!form){
				return false;
			}
			var onsubmit = form.onsubmit;
			form.onsubmit = function(e){
				gaUpdateImages();
				if(jQuery.isFunction(onsubmit)){
					onsubmit();
				}
			};
		});
		function gaListImages(){
			var folder = jQuery("#'. $this->id.'").val();
			if(folder == ""){
				alert("Folder path required");
				return;
			}
			jQuery("#gaListImage #gaSort").html("<img src=\"'.str_replace("\\","/", str_replace(JPATH_SITE, JURI::root(true), dirname(dirname(__FILE__)) )).'/assets/images/loading.gif\" width=\"30\" height=\"30\" />");
			jQuery.post(GA_IMAGE_FOLDER_ACTION,
				{
					task:"getListImage"
					,folder:folder
					,mid:GA_IMAGE_ID
					,fieldname:GA_IMAGE_FIELDNAME
				},function(res){
					if(res.success){
						jQuery("#gaSort").html(res.imageHtml);
						return jQuery( "#gaSort" ).gridly({selector:".ga-img"});
					}else{
						jQuery("#gaListImage #gaSort").html("<strong style=\'color: red\'>Image not found</strong>");
						return ;	
					}
				},"json");
		};
		function gaUpdateImages(){
			var images = jQuery("#gaListImage").find("img");
			var config = {};
			images.each (function(index,element){
				var $this = jQuery(this),
					name = $this.data("name"),
					position = $this.closest(".ga-img").data("position"),
					item = {};
				$this.data("position",position);
				for (var d in $this.data()) {
					item[d] = $this.data(d);
				};
				if (Object.keys(item).length) config[name] = item;
			});
			jQuery("#'.$this->fieldname.'_images'.'").val(JSON.stringify(config));
		}
		function gaDelete(image,element){
			if(confirm("If you want, We\'ll delete the file on the folder. However, if you want to see that file again, you\'ll have to upload that file")){
				var folder = jQuery("#'. $this->id.'").val();
				jQuery.post(GA_IMAGE_FOLDER_ACTION,
				{
					task:"deleteImage"
					,folder:folder
					,image:image
				},function(res){
					if(res.success){
						jQuery(element).closest(".brick").remove();
						return jQuery( "#gaSort" ).gridly({selector:".ga-img"});
					}
				},"json");
			}
		}
		function gaModal(id){
			var images = jQuery("#gaListImage").find("img");
			var image = images.get(id);
			jQuery("#gaTitle").val(jQuery(image).data("title"));
			jQuery("#gaAlttag").val(jQuery(image).data("alttag"));
			jQuery("#gaImagelink").val(jQuery(image).data("imagelink"));
			jQuery("#gaTarget").val(jQuery(image).data("gatarget"));
			jQuery("#gaLink").val(jQuery(image).data("link"));
			jQuery("#gaDescription").val(jQuery(image).data("description"));
			jQuery("#gaGrayscale").val(jQuery(image).data("gagray"));
			jQuery("#gaTag").val(jQuery(image).data("tag"));
			jQuery("#gaModal").data("imageId",id);
			jQuery("#gaModal").modal("show");
		}
		function gaUpdateImgData(){
			var imageId = jQuery("#gaModal").data("imageId");
			var images = jQuery("#gaListImage").find("img");
			var title = jQuery("#gaTitle").val(),
				alttag = jQuery("#gaAlttag").val(),
				imagelink = jQuery("#gaImagelink").val(),
				gatarget = jQuery("#gaTarget").val(),
				link = jQuery("#gaLink").val(),
				description = jQuery("#gaDescription").val();
				gagray = jQuery("#gaGrayscale").val();
				tag = jQuery("#gaTag").val();
			var image = images.get(imageId);
			jQuery(image).data("link",link).data("title",title).data("alttag",alttag).data("gatarget",gatarget).data("imagelink",imagelink).data("description",description).data("gagray",gagray).data("tag",tag);
			jQuery("#gaModal").find("input,textarea").each(function(){
				jQuery(this).val("");
			});
			jQuery("#gaModal").modal("hide");
		}
		');
	}
	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	protected function getInput()
	{
		$this->init();
		$html = array();
		$attr = '';
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';
		// Initialize JavaScript field attributes.
		$attr .= ' onchange="gaListImages();"' ;
		// Get the field options.
		$options = (array) $this->getOptions();
		// Create a regular list.
		$html[] = JHtml::_('select.genericlist', $options, $this->name.'[folder]', trim($attr), 'value', 'text', $this->value, $this->id);
		$html[]	= '<div id="gaListImage" style="margin-top: 18px;"><div id="gaSort" class="gridly"></div></div>';
		$html[]	= '<input id="'.$this->fieldname.'_images'.'" name="'.$this->name.'[images]" type="hidden" value="" />';
		$html[]	= $this->createModal();
		return implode($html);
	}
	private function createModal(){
		$html = '
			<div id="gaModal" class="modal hide fade" style="width:50%;">
				<div class="modal-body" style="margin: 20px;">
					<div class="control-group">
						<label class="control-label" for="gaTitle">Title</label>
						<div class="controls">
							<input type="text" id="gaTitle">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaAlttag">Alt tag</label>
						<div class="controls">
							<input type="text" id="gaAlttag">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaImagelink">Image link</label>
						<div class="controls">
							<input type="text" id="gaImagelink">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaTarget">To link target type,</label>
						<div class="controls">	
						<input type="text" id="gaTarget">&nbsp;&nbsp;<b>self</b> for same window&nbsp;or <b>blank</b> for new window.	
						</div>
					</div>	
					<div class="control-group">
						<label class="control-label" for="gaLink">Rows to span</label>
						<div class="controls">
							<input type="text" id="gaLink">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaDescription">Description</label>
						<div class="controls">
							<textarea id="gaDescription" cols="80" rows="6"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaTag">Bottom tag</label>
						<div class="controls">
							<input type="text" id="gaTag">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="gaGrayscale">To grayscale image type,</label>
						<div class="controls">	
						<input type="text" id="gaGrayscale">&nbsp;&nbsp;<span style="color:#ff0000;font-weight:bold;">grayscale</span>&nbsp;&nbsp;otherwise, leave blank.	
						</div>
					</div>	
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" type="button">Close</button>
					<button class="btn btn-primary" onclick="gaUpdateImgData()" type="button" >Ok</button>
				</div>
			</div>
		';
		return $html;
	}
	/**
	 * Method to get the field options. 
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		$options = array();
		// Initialize some field attributes.
		$filter = (string) $this->element['filter'];
		$exclude = (string) $this->element['exclude'];
		// Get the path in which to search for file options.
		$path = (string) $this->element['directory'];
		if (!is_dir($path))
		{
			$path = JPATH_ROOT . '/' . $path;
		}
		// Get a list of folders in the search path with the given filter.
		//$folders = JFolder::folders($path, $filter);
		$listFolers = self::listFolderTree($path,$filter,100);
		// Build the options list from the list of folders.
		if (is_array($listFolers)){
			$children = array();
			foreach ($listFolers as $k => $folder) {
					if ($exclude)
					{
						if (preg_match(chr(1) . $exclude . chr(1), $folder))
						{
							continue;
						}
					}
					$folder = (object) $folder;
					$folder->title = $folder->name;
					$folder->parent_id = $folder->parent;
					$pt = $folder->parent;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $folder);
					$children[$pt] = $list;
				}
			$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
			$options = array();
			foreach ($list as $item) {
				$item->treename = JString::str_ireplace('&#160;', '- ', JString::str_ireplace('&#160;&#160;', '&#160;', $item->treename));
				$options[] = JHTML::_('select.option',str_replace(DIRECTORY_SEPARATOR,'/',trim($item->relname,DIRECTORY_SEPARATOR)), ' ' . $item->treename);
			}
		}
		return $options;
	}
	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   string   $filter    A filter for folder names.
	 * @param   integer  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   integer  $level     The current level, optional.
	 * @param   integer  $parent    Unique identifier of the parent folder, if any.
	 *
	 * @return  array  Folders in the given folder.
	 *
	 */
	public static function listFolderTree($path, $filter, $maxLevel = 100, $level = 1, $parent = 1)
	{
		$dirs = array();
		if ($level == 1)
		{
			$fullName = JPath::clean($path);
			$dirs[] = array('id' => 1, 'parent' => 0, 'name' => basename($path), 'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName));
			$GLOBALS['_ga_folder_tree_index'] = 1;
		}
		if ($level < $maxLevel)
		{
			$folders =JFolder::folders($path, $filter);
			// First path, index foldernames
			foreach ($folders as $name)
			{
				$id = ++$GLOBALS['_ga_folder_tree_index'];
				$fullName = JPath::clean($path . '/' . $name);
				$dirs[] = array('id' => $id, 'parent' => $parent, 'name' => $name, 'fullname' => $fullName,
					'relname' => str_replace(JPATH_ROOT, '', $fullName));
				$dirs2 = self::listFolderTree($fullName, $filter, $maxLevel, $level + 1, $id);
				$dirs = array_merge($dirs, $dirs2);
			}
		}
		return $dirs;
	}
}
