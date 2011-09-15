<?php
 /* $Id$ */

// The destinations this module provides
// returns a associative arrays with keys 'destination' and 'description'
function ivr_destinations() {
	global $module_page;
	
	//get the list of IVR's
	$results = ivr_get_details();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
			$name = $result['name'] ? $result['name'] : 'IVR ' . $result['id'];
			$extens[] = array('destination' => 'ivr-'.$result['id'].',s,1', 'description' => $name);
		}
	}
	if (isset($extens)) {
		return $extens;
	} else {
		return null;
	}

}

//dialplan generator
function ivr_get_config($engine) {
	global $ext;

	switch($engine) {
		case "asterisk":
			$ddial_contexts = array();
			$ivrlist = ivr_get_details();
			if(!is_array($ivrlist)) {
				break;
			}
			
			//draw a list of ivrs included by any queues
			$queues = queues_list(true);
			foreach ($queues as $q) {
				$thisq = queues_get($q[0]);
				if ($thisq['context'] && strpos($thisq['context'], 'ivr-') === 0) {
					$qivr[] = str_replace('ivr-', '', $thisq['context']);
				}
			}
			
			foreach($ivrlist as $ivr) {
				$c = 'ivr-' . $ivr['id'];
				$ivr = ivr_get_details($ivr['id']);
				$ext->addSectionComment($c, $ivr['name'] ? $ivr['name'] : 'IVR ' . $ivr['id']);
				
				if ($ivr['directdial']) {
					if ($ivr['directdial'] == 'ext-local') {
						$ext->addInclude($c, 'from-did-direct-ivr'); //generated in core module
					} else {
						//generated by directory
						$ext->addInclude($c, 'from-ivr-directory-' . $ivr['directdial']);
						$directdial_contexts[$ivr['directdial']] = $ivr['directdial'];
					}
				}

				//set variables for loops when used
				if ($ivr['timeout_loops'] != 'disabled' && $ivr['timeout_loops'] > 0) {
					$ext->add($c, 's', '', new ext_setvar('TIMEOUT_LOOPCOUNT', 0));
				}
				if ($ivr['invalid_loops'] != 'disabled' && $ivr['invalid_loops'] > 0) {
					$ext->add($c, 's', '', new ext_setvar('INVALID_LOOPCOUNT', 0));
				}
				
				$ext->add($c, 's', '', new ext_setvar('_IVR_CONTEXT_${CONTEXT}', '${IVR_CONTEXT}'));
				$ext->add($c, 's', '', new ext_setvar('_IVR_CONTEXT', '${CONTEXT}'));
				if ($ivr['retvm']) {
					$ext->add($c, 's', '', new ext_setvar('__IVR_RETVM', 'RETURN'));
				} else {
					//TODO: do we need to set anything at all?
					$ext->add($c, 's', '', new ext_setvar('__IVR_RETVM', ''));
				}
				
				$ext->add($c, 's', '', new ext_gotoif('$["${CDR(disposition)}" = "ANSWERED"]','start'));
				$ext->add($c, 's', '', new ext_answer(''));
				$ext->add($c, 's', '', new ext_wait('1'));
				$ext->add($c, 's', 'start', new ext_digittimeout(3));
				//$ext->add($ivr_id, 's', '', new ext_responsetimeout($ivr['timeout_time']));

				$ext->add($c, 's', '', new ext_background(recordings_get_file($ivr['announcement'])));
				$ext->add($c, 's', '', new ext_waitexten($ivr['timeout_time']));
				

				// Actually add the IVR entires now
				$entries = ivr_get_entries($ivr['id']);

				if ($entries) {
					foreach($entries as $e) {
						//dont set a t or i if there already defined above
						if ($e['selection'] == 't' && $ivr['timeout_loops'] != 'disabled') {
						 	continue;
						}
						if ($e['selection'] == 'i' && $ivr['invalid_loops'] != 'disabled') {
						 	continue;
						}
						
						//only display these two lines if the ivr is included in any queues
						if (in_array($ivr['id'], $qivr)) {
							$ext->add($c, $e['selection'],'', new ext_macro('blkvm-clr'));
							$ext->add($c, $e['selection'], '', new ext_setvar('__NODEST', ''));
							
						}

						if ($e['ivr_ret']) {
							$ext->add($c, $e['selection'], '', 
								new ext_gotoif('$["x${IVR_CONTEXT_${CONTEXT}}" = "x"]',
									$e['dest'] . ':${IVR_CONTEXT_${CONTEXT}},return,1'));
						} else {
							$ext->add($c, $e['selection'],'', new ext_goto($e['dest']));
						}
					}
				}
		
				// add invalid destination if required
				if ($ivr['invalid_loops'] != 'disabled') {
					if ($ivr['invalid_loops'] > 0) {
						$ext->add($c, 'i', '', new ext_set('INVALID_LOOPCOUNT', '$[${INVALID_LOOPCOUNT}+1]'));
						$ext->add($c, 'i', '',	new ext_gotoif('$[${INVALID_LOOPCOUNT} > ' . $ivr['invalid_loops'] . ']','final'));
						switch ($ivr['invalid_retry_recording']) {
							case 'default':
								$ext->add($c, 'i', '', new ext_playback('no-valid-responce-pls-try-again'));
								break;
							case '':
								break;
							default:
								$ext->add($c, 'i', '', new ext_playback(recordings_get_file($ivr['invalid_retry_recording'])));
								break;
						}

						$ext->add($c, 'i', '', new ext_goto('s,start'));
					}

					$label = 'final';
					switch ($ivr['invalid_recording']) {
						case 'default':
							$ext->add($c, 'i', $label, new ext_playback('no-valid-responce-transfering'));
							$label ='';
							break;
						case '':
							break;
						default:
							$ext->add($c, 'i', $label, 
								new ext_playback(recordings_get_file($ivr['invalid_recording'])));
							$label = '';
							break;
					}
					$ext->add($c, 'i', $label, new ext_goto($ivr['invalid_destination']));
				}

				// Apply timeout destination if required
				if ($ivr['timeout_loops'] != 'disabled') {
					if ($ivr['timeout_loops'] > 0) {
						$ext->add($c, 't', '', new ext_set('TIMEOUT_LOOPCOUNT', '$[${TIMEOUT_LOOPCOUNT}+1]'));
						$ext->add($c, 't', '', new ext_gotoif('$[${TIMEOUT_LOOPCOUNT} > ' . $ivr['timeout_loops'] . ']','final'));

						switch ($ivr['timeout_retry_recording']) {
							case 'default':
								$ext->add($c, 't', '', new ext_playback('no-valid-responce-pls-try-again'));
								break;
							case '':
								break;
							default:
								$ext->add($c, 't', '', 
									new ext_playback(recordings_get_file($ivr['timeout_retry_recording'])));
								break;
						}

						$ext->add($c, 't', '', new ext_goto('s,start'));
					}
					
					$label = 'final';
					switch ($ivr['timeout_recording']) {
						case 'default':
							$ext->add($c, 't', $label, new ext_playback('no-valid-responce-transfering'));
							$label = '';
							break;
						case '':
							break;
						default:
							$ext->add($c, 't', $label, 
								new ext_playback(recordings_get_file($ivr['timeout_recording'])));
							$label = '';
							break;
					}
					$ext->add($c, 't', $label, new ext_goto($ivr['timeout_destination']));
				}
				
				if ($ivr['retvm']) {
					// these need to be reset or inheritance problems makes them go away in some conditions 
					//and infinite inheritance creates other problems
					$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT', '${CONTEXT}'));
					$ext->add($c, 'return', '', new ext_setvar('_IVR_CONTEXT_${CONTEXT}', '${IVR_CONTEXT_${CONTEXT}}'));
					$ext->add($c, 'return', '', new ext_goto('s,start'));
				}
			
				//h extension
				$ext->add($c, 'h', '', new ext_hangup(''));
				$ext->add($c, 'hang', '', new ext_playback('vm-goodbye'));
				$ext->add($c, 'hang', '', new ext_hangup(''));
			}
			
			
			//generate from-ivr-directory contexts for direct dialing a directory entire
			if (!empty($directdial_contexts)) {
				foreach($directdial_contexts as $dir_id) {
					$c = 'from-ivr-directory-' . $dir_id;
					$entries = function_exists('directory_get_dir_entries') ? directory_get_dir_entries($dir_id) : array();
					foreach ($entries as $dstring) {
						$exten = $dstring['dial'] == '' ? $dstring['foreign_id'] : $dstring['dial'];
						if ($exten == '' || $exten == 'custom') {
							continue;
						}
						$ext->add($c, $exten, '', new ext_macro('blkvm-clr'));
						$ext->add($c, $exten, '', new ext_setvar('__NODEST', ''));
						$ext->add($c, $exten, '', new ext_goto('1', $exten, 'from-internal'));
					}
				}
			}
		break;
	}
}

//replaces ivr_list(), returns all details of any ivr
function ivr_get_details($id = '') {
	global $db;

	$sql = "SELECT * FROM ivr_details";
	if ($id) {
		$sql .= ' where  id = "' . $id . '"';
	}
	$res = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if($db->IsError($res)) {
		die_freepbx($res->getDebugInfo());
	}

	return $id ? $res[0] : $res;
}

//get all ivr entires
function ivr_get_entries($id) {
	global $db;
	
	//+0 to convert string to an integer
	$sql = "SELECT * FROM ivr_entries WHERE ivr_id = ? ORDER BY selection + 0";
	$res = $db->getAll($sql, array($id), DB_FETCHMODE_ASSOC);
	if ($db->IsError($res)) {
		die_freepbx($res->getDebugInfo());
	}
	return $res;
}


//draw ivr options page
function ivr_configpageload() {
	global $currentcomponent, $display;
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$id 	= isset($_REQUEST['id']) ? $_REQUEST['id'] : null;

	if ($action  == 'add') {
		$currentcomponent->addguielem('_top', new gui_pageheading('title', _('Add IVR')), 0);

		$deet = array('id', 'name', 'description', 'announcement', 'directdial', 
					'invalid_loops', 'invalid_retry_recording', 
					'invalid_recording', 'invalid_destination', 
					'timeout_loops', 'timeout_time',
					'timeout_retry_recording', 'timeout_recording', 'timeout_destination',
					'retvm');
     
		//keep vairables set on new ivr's
		foreach ($deet as $d) {
			switch ($d){
				case 'invalid_loops':
				case 'timeout_loops';
					$ivr[$d] = 3;
					break;
				case 'announcement':
					$ivr[$d] = '';
					break;
				case 'invalid_recording':
				case 'invalid_retry_recording':
				case 'timeout_retry_recording':
				case 'timeout_recording':
					$ivr[$d] = 'default';
					break;
				case 'timeout_time':
					$ivr[$d] = 10;
					break;
				default:
				$ivr[$d] = '';
					break;
			}
		}
	} else {
		$ivr = ivr_get_details($id);

		$label = sprintf(_("Edit IVR: %s"), $ivr['name'] ? $ivr['name'] : 'ID '.$ivr['id']);
		$currentcomponent->addguielem('_top', new gui_pageheading('title', $label), 0);
		
		//display usage
		$usage_list			= framework_display_destination_usage(ivr_getdest($ivr['id']));
		if (!empty($usage_list)) {
			$usage_list_text	= isset($usage_list['text']) ? $usage_list['text'] : '';
			$usage_list_tooltip	= isset($usage_list['tooltip']) ? $usage_list['tooltip'] : '';
			$currentcomponent->addguielem('_top', 
				new gui_link_label('usage', $usage_list_text, $usage_list_tooltip), 0);
		}
		
		//display delete link
		$label = sprintf(_("Delete IVR: %s"), $ivr['name'] ? $ivr['name'] : 'ID '.$ivr['id']);
		$del 				= '<span><img width="16" height="16" border="0" title="' 
							. $label . '" alt="" src="images/core_delete.png"/>&nbsp;' . $label . '</span>';
		$currentcomponent->addguielem('_top', 
			new gui_link('del', $del, $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '&action=delete', 
				true, false), 0);
	}
	
	//general options
	$gen_section = _('IVR General Options');
	$currentcomponent->addguielem($gen_section, 
		new gui_textbox('name', stripslashes($ivr['name']), _('IVR Name'), _('Name of this IVR.')));
	$currentcomponent->addguielem($gen_section, 
		new gui_textbox('description', stripslashes($ivr['description']), 
		_('IVR Description'), _('Description of this ivr.')));

	
	//dtmf options
	$section = _('IVR Options (DTMF)');
	
	//build recordings select list
	$currentcomponent->addoptlistitem('recordings', '', _('None'));
	foreach(recordings_list() as $r){
		$currentcomponent->addoptlistitem('recordings', $r['id'], $r['displayname']);
	}
    $currentcomponent->setoptlistopts('recordings', 'sort', false);
	
	//build repeat_loops select list and defualt it to 3
	//while addoptlist is not usually required, declaring this is the only way to prevent sorting on the list
	$currentcomponent->addoptlist('ivr_repeat_loops', false); 
	$currentcomponent->addoptlistitem('ivr_repeat_loops', 'disabled', 'Disabled');
	for($i=0; $i <11; $i++){
		$currentcomponent->addoptlistitem('ivr_repeat_loops', $i, $i);
	}

	//greating to be played on entry to the ivr
	$currentcomponent->addguielem($section, 
		new gui_selectbox('announcement', $currentcomponent->getoptlist('recordings'), 
			$ivr['announcement'], _('Announcement'), _('Greeting to be played on entry to the Ivr.'), false));


	
	//direct dial
	$currentcomponent->addoptlistitem('directdial', '', _('Disabled'));
	$currentcomponent->addoptlistitem('directdial', 'ext-local', _('Extensions'));
	
	$currentcomponent->addgeneralarrayitem('directdial_help', 'disabled', _('Completely disabled'));
	$currentcomponent->addgeneralarrayitem('directdial_help', 'local', _('Enabled for all extensions on a system'));

	$currentcomponent->addguielem($section, 
		new gui_selectbox('directdial', $currentcomponent->getoptlist('directdial'), 
		$ivr['directdial'], _('Direct Dial'), _('Provides options for callers to direct dial an extension. Direct dialing can be:') 
		. ul($currentcomponent->getgeneralarray('directdial_help')), false));
	
	//add default to the recordings list. We dont want this for the general announcement, so we do it here
	$currentcomponent->addoptlistitem('recordings', 'default', _('Default'));
	//$currentcomponent->addguielem($section, new gui_textbox('timeout_time', stripslashes($ivr['timeout_time']), _('Timeout'), _('Amount of time to be concidered a timeout')));
	$currentcomponent->addguielem($section, new guielement('timeout_time',
		'<tr class="IVROptionsDTMF"><td>' . fpbx_label(_('Timeout'), _('Amount of time to be considered a timeout')).'</td><td><input type="number" name="timeout_time" value="' 
					. $ivr['timeout_time'] 
					.'" required></td></tr>'));
	//invalid 
	$currentcomponent->addguielem($section, 
		new gui_selectbox('invalid_loops', $currentcomponent->getoptlist('ivr_repeat_loops'), 
		$ivr['invalid_loops'], _('Invalid Retries'), _('Number of times to retry when receiving an invalid/unmatched response from the caller'), false));
	$currentcomponent->addguielem($section, 
		new gui_selectbox('invalid_retry_recording', $currentcomponent->getoptlist('recordings'), 
		$ivr['invalid_retry_recording'], _('Invalid Retry Recording'), _('Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again'), false));
	$currentcomponent->addguielem($section, 
		new gui_selectbox('invalid_recording', $currentcomponent->getoptlist('recordings'), 
		$ivr['invalid_recording'], _('Invalid Recording'), _('Prompt to be played before sending the caller to an alternate destination due to the caller pressing 0 or receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)'), false));
	$currentcomponent->addguielem($section, 
		new gui_drawselects('invalid_destination', 'invalid', $ivr['invalid_destination'], _('Invalid Destination'),
		 _('Destination to send the call to after Invalid Recording is played.'), false));
	
	//timeout
	$currentcomponent->addguielem($section, 
		new gui_selectbox('timeout_loops', $currentcomponent->getoptlist('ivr_repeat_loops'), 
		$ivr['timeout_loops'], _('Timeout Retries'), _('Number of times to retry when receiving an invalid/unmatched response from the caller'), false));
	$currentcomponent->addguielem($section, 
		new gui_selectbox('timeout_retry_recording', $currentcomponent->getoptlist('recordings'), 
		$ivr['timeout_retry_recording'], _('Timeout Retry Recording'), _('Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again'), false));
	$currentcomponent->addguielem($section, 
		new gui_selectbox('timeout_recording', $currentcomponent->getoptlist('recordings'), 
		$ivr['timeout_recording'], _('Timeout Recording'), _('Prompt to be played before sending the caller to an alternate destination due to the caller pressing 0 or receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries)'), false));
	$currentcomponent->addguielem($section, 
		new gui_drawselects('timeout_destination', 'timeout', 
		$ivr['timeout_destination'], _('Timeout Destination'), _('Destination to send the call to after Invalid Recording is played.'), false));
	
	//return to ivr
	$currentcomponent->addguielem($section, 
		new gui_checkbox('retvm', $ivr['retvm'], _('Return to IVR after VM'), _('If checked, upon exiting voicemail a caller will be returned to this IVR if they got a users voicemail')));
		
	/*$currentcomponent->addguielem($section, 
		new gui_checkbox('say_extension', $dir['say_extension'], _('Announce Extension'), 
		_('When checked, the extension number being transferred to will be announced prior to the transfer'),true));*/
	$currentcomponent->addguielem($section, new gui_hidden('id', $ivr['id']));
	$currentcomponent->addguielem($section, new gui_hidden('action', 'save'));


		
	$section = _('IVR Entries');
	//draw the entries part of the table. A bit hacky perhaps, but hey - it works!
	$currentcomponent->addguielem($section, new guielement('rawhtml', ivr_draw_entries($ivr['id']), ''), 6);
}

function ivr_configpageinit($pagename) {
	global $currentcomponent;
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

	if($pagename == 'ivr'){
		$currentcomponent->addprocessfunc('ivr_configprocess');
		
		//dont show page if there is no action set
		if ($action && $action != 'delete' || $id) {			
			$currentcomponent->addguifunc('ivr_configpageload');
		}
		
    return true;
	}
}

//prosses received arguments
function ivr_configprocess(){
	if (isset($_REQUEST['display']) && $_REQUEST['display'] == 'ivr'){
		global $db;
		//get variables

		$get_var = array('id', 'name', 'description', 'announcement',
						'directdial', 'invalid_loops', 'invalid_retry_recording',
						'invalid_destination', 'invalid_recording',
						'retvm', 'timeout_time', 'timeout_recording',
						'timeout_retry_recording', 'timeout_destination', 'timeout_loops');
		foreach($get_var as $var){
			$vars[$var] = isset($_REQUEST[$var]) 	? $_REQUEST[$var]		: '';
		}

		$action		= isset($_REQUEST['action'])	? $_REQUEST['action']	: '';
		$entries	= isset($_REQUEST['entries'])	? $_REQUEST['entries']	: '';

		switch ($action) {
			case 'save':
			
				//get real dest
				$_REQUEST['id'] = $vars['id'] = ivr_save_details($vars);
				ivr_save_entries($vars['id'], $entries);
				needreload();
				//$_REQUEST['action'] = 'edit';
				redirect_standard_continue('id');
			break;
			case 'delete':
				ivr_delete($vars['id']);
				needreload();
				redirect_standard_continue();
			break;
		}
	}
}

//save ivr settings
function ivr_save_details($vals){
	global $db, $amp_conf;

	foreach($vals as $key => $value) {
		$vals[$key] = $db->escapeSimple($value);
	}

	if ($vals['id']) {
		$sql = 'REPLACE INTO ivr_details (id, name, description, announcement,
				directdial, invalid_loops, invalid_retry_recording,
				invalid_destination, invalid_recording,
				retvm, timeout_time, timeout_recording,
				timeout_retry_recording, timeout_destination, timeout_loops)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$foo = $db->query($sql, $vals);
		if($db->IsError($foo)) {
			die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
		}
	} else {
		unset($vals['id']);
		$sql = 'INSERT INTO ivr_details (name, description, announcement,
				directdial, invalid_loops, invalid_retry_recording,
				invalid_destination,  invalid_recording,
				retvm, timeout_time, timeout_recording,
				timeout_retry_recording, timeout_destination, timeout_loops)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
				
		$foo = $db->query($sql, $vals);
		if($db->IsError($foo)) {
			die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
		}
		$sql = ( ($amp_conf["AMPDBENGINE"]=="sqlite3") ? 'SELECT last_insert_rowid()' : 'SELECT LAST_INSERT_ID()');
		$vals['id'] = $db->getOne($sql);
		if ($db->IsError($foo)){
			die_freepbx($foo->getDebugInfo());
		}
	}

	return $vals['id'];
}

//save ivr entires
function ivr_save_entries($id, $entries){
	global $db;
	$id = $db->escapeSimple($id);
	sql('DELETE FROM ivr_entries WHERE ivr_id = "' . $id . '"');

	if ($entries) {
		for ($i = 0; $i < count($entries['ext']); $i++) {
			//make sure there is an extension & goto set - otherwise SKIP IT
			if ($entries['ext'][$i] && $entries['goto'][$i]) {
				$d[] = array(
							'ivr_id'	=> $id,
							'selection' 	=> $entries['ext'][$i],
							'dest'		=> $entries['goto'][$i],
							'ivr_ret'	=> (isset($entries['ivr_ret'][$i]) ? $entries['ivr_ret'][$i] : '')
						);
			}

		}
		$sql = $db->prepare('INSERT INTO ivr_entries VALUES (?, ?, ?, ?)');
		$res = $db->executeMultiple($sql, $d);
		if ($db->IsError($res)){
			die_freepbx($res->getDebugInfo());
		}
	}
	
	return true;
}

//draw uvr entires table header
function ivr_draw_entries_table_header_ivr() {
	return  array(_('Ext'), _('Destination'), fpbx_label(_('Return'), _('Return to IVR')), _('Delete'));
}

//draw actualy entires 
function ivr_draw_entries($id){
	$headers		= mod_func_iterator('draw_entries_table_header_ivr');
	$ivr_entries	= ivr_get_entries($id);

	if ($ivr_entries) {
		foreach ($ivr_entries as $k => $e) {
			$entries[$k]= $e;
			$array = array('id' => $id, 'ext' => $e['selection']);
			$entries[$k]['hooks'] = mod_func_iterator('draw_entries_ivr', $array);
		}
	}
	
	$entries['blank'] = array('selection' => '', 'dest' => '', 'ivr_ret' => '');
	//assign to a vatriable first so that it can be passed by reference
	$array = array('id' => '', 'ext' => '');
	$entries['blank']['hooks'] = mod_func_iterator('draw_entries_ivr', $array);
	
	return load_view(dirname(__FILE__) . '/views/entries.php', 
				array(
					'headers'	=> $headers, 
					'entries'	=>  $entries
				)
			);

}

//delete an ivr + entires
function ivr_delete($id) {
	global $db;
	sql('DELETE FROM ivr_details WHERE id = "' . $db->escapeSimple($id) . '"');
	sql('DELETE FROM ivr_entries WHERE ivr_id = "' . $db->escapeSimple($id) . '"');
}
//----------------------------------------------------------------------------
// Dynamic Destination Registry and Recordings Registry Functions
function ivr_check_destinations($dest=true) {
	global $active_modules;

	$destlist = array();
	if (is_array($dest) && empty($dest)) {
		return $destlist;
	}
	$sql = "SELECT dest, name, selection, a.id id FROM ivr_details a INNER JOIN ivr_entries d ON a.id = d.ivr_id  ";
	if ($dest !== true) {
		$sql .= "WHERE dest in ('".implode("','",$dest)."')";
	}
	$sql .= "ORDER BY name";
	$results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);

	foreach ($results as $result) {
		$thisdest = $result['dest'];
		$thisid   = $result['id'];
		$name = $result['name'] ? $result['name'] : 'IVR ' . $thisid;
		$destlist[] = array(
			'dest' => $thisdest,
			'description' => sprintf(_("IVR: %s / Option: %s"),$name,$result['selection']),
			'edit_url' => 'config.php?display=ivr&action=edit&id='.urlencode($thisid),
		);
	}
	return $destlist;
}



function ivr_change_destination($old_dest, $new_dest) {
	global $db;
 	$sql = "UPDATE ivr_entires SET dest = '$new_dest' WHERE dest = '$old_dest'";
 	$db->query($sql);

}


function ivr_getdest($exten) {
	return array('ivr-'.$exten.',s,1');
}

function ivr_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,4) == 'ivr-') {
		$exten = explode(',',$dest);
		$exten = substr($exten[0],4);

		$thisexten = ivr_get_details($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			//$type = isset($active_modules['ivr']['type'])?$active_modules['ivr']['type']:'setup';
			return array('description' => sprintf(_("IVR: %s"), ($result['name'] ? $result['name'] : $result['id'])),
			             'edit_url' => 'config.php?display=ivr&action=edit&id='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function ivr_recordings_usage($recording_id) {
	global $active_modules;

	//$results = sql("SELECT `ivr`, `name` FROM `ivr` WHERE `announcement` = '$recording_id' || `timeout_id` = '$recording_id' || `invalid_id` = '$recording_id'","getAll",DB_FETCHMODE_ASSOC);
	$results = sql("SELECT `id`, `name` FROM `ivr_details` WHERE '$recording_id' IN('announcement', 'invalid_retry_recording', 'invalid_recording', 'timeout_recording', 'timeout_retry_recording')", "getAll",DB_FETCHMODE_ASSOC);
	if (empty($results)) {
		return array();
	} else {
		foreach ($results as $result) {
			$usage_arr[] = array(
				'url_query' => 'config.php?display=ivr&action=edit&id='.urlencode($result['id']),
				'description' => sprintf(_("IVR: %s"), ($result['name'] ? $result['name'] : $result['id'])),
			);
		}
		return $usage_arr;
	}
}

?>
