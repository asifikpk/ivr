<?php

global $db;

// Now, we need to check for upgrades. 
// V1.0, old IVR. You shouldn't see this, but check for it anyway, and assume that it's 2.0
// V2.0, Original Release
// V2.1, added 'directorycontext' to the schema
// v2.2, announcement changed to support filenames instead of ID's from recordings table
// 

$ivr_modcurrentvers = modules_getversion('ivr');

if (version_compare($ivr_modcurrentvers, "2.1", "<")) {
	// Add the col
	$sql = "SELECT dircontext FROM ivr";
	$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($check)) {
		// add new field
	    $sql = 'ALTER TABLE ivr ADD COLUMN dircontext VARCHAR ( 50 ) DEFAULT "default"';
	    $result = $db->query($sql);
	    if(DB::IsError($result)) {
	            die($result->getDebugInfo());
	    }
	}
}

if (version_compare($ivr_modcurrentvers, "2.2", "<")) {
	//echo "<p>Start 2.2 upgrade</p>";
	$sql = "ALTER TABLE ivr CHANGE COLUMN announcement announcement VARCHAR ( 255 )";
    $result = $db->query($sql);
    if(DB::IsError($result)) {
            die($result->getDebugInfo());
    } else {
    	// Change existing records
    	//echo "<p>Updating existing records</p>";
    	$existing = sql("SELECT DISTINCT announcement FROM ivr WHERE displayname <> '__install_done' AND announcement IS NOT NULL", "getAll");
    	foreach ($existing as $item) {
    		$recid = $item[0];
    		//echo "<p>processing '$recid'</p>";
    		$sql = "SELECT filename FROM recordings WHERE id = '$recid' AND displayname <> '__invalid'";
    		$recordings = sql($sql, "getRow");
    		if (is_array($recordings)) {
    			$filename = (isset($recordings[0]) ? $recordings[0] : '');
    			//echo "<p>filename: $filename";
    			if ($filename != '') {
    				$sql = "UPDATE ivr SET announcement = '".str_replace("'", "''", $filename)."' WHERE announcement = '$recid'";
				    $upcheck = $db->query($sql);
				    if(DB::IsError($upcheck))
				            die($upcheck->getDebugInfo());    				
    			}
    		}
    	}
    }
}

// bump the version number
modules_setversion('ivr', '2.2');
?>
