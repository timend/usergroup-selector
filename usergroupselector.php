<?php
/**
 * @package 	Plugin Usergroupselector for Joomla! 3.X
 * @version 	0.0.1
 * @author 		Function90.com
 * @copyright 	C) 2013- Function90.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
**/

defined('_JEXEC') or die;

class plgSystemUsergroupselector extends JPlugin
{
	protected $autoloadLanguage = true;
		
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$allowed_groups = $this->params->get('allowed_groups');
		if($isnew && $success){
			$input = JFactory::getApplication()->input;
			$requestData  = $input->post->get('jform', array(), 'array');
			if(isset($requestData['usergroupselector']) && in_array($requestData['usergroupselector'], $allowed_groups)){				
				$juser = JFactory::getUser($user['id']);
				$juser->groups = array($requestData['usergroupselector']);
				unset($requestData['usergroupselector']);
				$input->set('jform', $requestData, 'array');
				$juser->save();
			}
		}
	}
	public function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			return true;
		}
		
		if($form->getName() != 'com_users.registration'){
			return true;
		}
		
		$groups = $this->getJoomlaUserGroups();
		
		$allowed_groups = $this->params->get('allowed_groups');
		
		$xml = '
				<fieldset name="usergroupselector">
					<field 
						type="list"
						name="usergroupselector"
						label="'.JText::_($this->params->get('label')).'"
						description="'.JText::_($this->params->get('desc')).'">';
						
		foreach($allowed_groups as $group){
			if(isset($groups[$group])){
				$xml .= '<option value="'.$group.'">'.$groups[$group]->title.'</option>';
			}
		}
						
		$xml .=	'</field>
				</fieldset>
				';
		
		$form->setField(new SimpleXMLElement($xml));				
	}
	
	public function getJoomlaUserGroups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__usergroups'));
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}
}

