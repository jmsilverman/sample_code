<?php
// All the functions used in search_public.php 
// Used in conjunction with searchform_public.html

//**************************************************************************

// Function Name: 
// GetChecked

// Inputs: 
// $input is an array, most likely the $_POST array from the html form

// Outputs: 
// An array that contains 4 arrays as its items, the first one is an array of the keys and values of the checked inputs, and the second is an array of the values of the checked outputs, the 3rd and 4th are the same but ONLY for the LC plotting stuff

function GetChecked($input) {

  // Make an array that contains a list of all options that were checked ($checkboxes)
  // Make an array that contains a list of all entries w/o checkbox entries.
  foreach ($input as $k => $v) {
    // Check if the key contains "CB" for checkbox
    $posCB = strpos($k, "CB");
    $posout = strpos($k, "Out");
    if ($posCB) {
      $checkboxes[] = $v; 
    } elseif ($posout) {
      $newk = substr($k, 0, -4);
      if ($v != 'PhotDataPhotOut') {
	$outputs[$newk] = $v;
      } else {
	$LCoutputs[$newk] = $v;
      }
    } else {
      $notcheck[$k] = $v;
    }
  }	

  // Check to make sure at least one thing was checked
  if (!isset($checkboxes)) {
    $inputs = "empty";
  }
  else {
 
    // Make an array that only contains the values of options that were checked
    // Go through each value of the $checkboxes array and write out all values of $notcheck that match
    foreach ($checkboxes as $k => $v) {
      foreach ($notcheck as $nk => $nv) {
	// See if the key of notcheck contains the value in checkboxes.
	$pieces = explode('__',$nk);

	if ($pieces[0] == $v) {
	  if ( ($pieces[0]=='FilterID')||($pieces[0]=='TelescopeID')||($pieces[0]=='JD')||($pieces[0]=='Value')||($pieces[0]=='DaysRelMaxLC') ) {
	    $LCinputs[$nk] = $nv;
	  } else {
	    $inputs[$nk] = $nv;
	  }
	}
      }
    }
  }
  
  if ((!isset($outputs))&&(!isset($LCoutputs))) {
    $outputs = "empty";
  }
  else {
    $results = array($inputs, $outputs, $LCinputs, $LCoutputs);
  }
  
  return ($results);
}

//**************************************************************************



// Function Name: 
// combrange

// Inputs:
// Input array and array of what is defined to be in the range class

// Outputs:
// Input array, but with the inputs that are ranges combined into 1 entry
// The keys for the combined range field are just the base word without "_low" or "_high"

function combrange($input, $ranges) {

  foreach ($ranges as $key => $val) {
    $rangesall[] = array($val . "__low", $val . "__high", $val);
    // This creates an array whose entries are all 3 element arrays themselves.
    // It creates one 3 element array for each entry in the $ranges array
    // The first element is the label for the low entry, e.g. Seeing__low
    // The second element is the label for the high entry, e.g. Seeing__high
    // The third element is the general label without suffixes, e.g. Seeing
}
  
  foreach ($rangesall as $k => $v) {
    $low = $v[0];
    $high = $v[1];
    if (isset($input[$high], $input[$low])) { // Check to see if these have been set
      // Create a new key to be entered
      $nkey = $v[2];
      // Create a new value to be entered
      // This new value is a 2 element array, containing the values of the low and high inputs
      $nval = array($input[$low], $input[$high]);
      // Add this entry into the input array and remove the low and high entries
      $input[$nkey] = $nval;
      unset($input[$high]);
      unset($input[$low]);
    }
  }
  return($input);
}



//**************************************************************************

// Function Name: 
// combcheck

// Inputs:
// Input array and array of what is defined to be in the checkbox class

// Outputs:
// Input array, but with the inputs that are checkboxes combined into one entry
// The key for this "combined" entry is just the root word, e.g. Telescope

function combcheck($input, $checks) {
  
  foreach ($checks as $k => $v) {
    
    unset($newval);
    // Run through all of the possible values of the array checks
    
    if (!empty($input)) {
      foreach ($input as $ik => $iv) {
	// Run through each member of $input to find any that have matching roots
      
	// See if this particular entry matches.
	$ex = explode("__", $ik);
	$root = $ex[0];
	$newkey = $ex[1];
	if ($root == $v) { // If the root of the entry matches the $checks key...
	  $newval[$newkey] = $iv;
	  // Remove all entries of input that have just been found
	  unset($input[$ik]);
	}
      }
    }

    // Add on one more entry that is the $newval array, which contains all the values
    if (isset($newval)) {
      $input[$v] = $newval;
    }
  }
  return($input);
}
    


//**************************************************************************

// Function Name: 
// textquery

// Inputs:
// A key and a value to be interpreted and arrays of inputs for each table
// Input key can be a comma separated list of inputs

// Outputs:
// A MySQL query segment to be appended to the end of the existing query


function textquery($k, $v, $objectlist, $speclist, $photolist) {

  // Add the appropriate prefix for each table
  // object is t1, spectra is t2, photometry is t3
  if (in_array ($k, $objectlist)) {
    $k = "t1." . $k;}
  elseif (in_array($k, $speclist)) {
    $k = "t2." . $k;}
  elseif (in_array($k, $photolist)) {
    $k = "t3." . $k;}

  
  // Check to see if the value was empty
  if (trim($v) == '') {
    $segment = $k . " IS NULL AND ";
  } else {
    
    // Break up by commas unless it's a reference field
    if ($k == "t1.LCReference" || $k == "t2.Reference") {
      // For everything else,
      $segment = "(" . $k . " LIKE '" . $v . "') AND ";
    } else {

      $segment = "(";
      $pieces = explode(',',$v);
      // For each item
      for($i=0;$i<count($pieces);$i++) {
	
	// Strip off any whitespace between items
	$v = trim($pieces[$i]);

	// If the person has a host name of "Anon" instead of "Anon."
	if ($k == "t1.HostName" && $v == 'Anon' OR $v== 'anon') {
	  $v = 'Anon.';
	}

	$segment = $segment . "(" . $k . " LIKE '" . $v . "') OR ";
      }
    
      // Strip off last 'OR'
      $segment = substr($segment, 0, -4) . ") AND ";   
    }
  }

  return($segment);
}




//**************************************************************************

// Function Name: 
// rangequery

// Inputs:
// A key and a value to be interpreted

// Outputs:
// A MySqL query segment to be appended to the end of the existing query

function rangequery($k, $v, $objectlist, $speclist, $photolist) {
  
  // Add the appropriate prefix for each table
  // object is t1, spectra is t2, photometry is t3
  if (in_array ($k, $objectlist)) {
    $name = "t1." . $k;}
  elseif (in_array($k, $speclist)) {
    $name = "t2." . $k;}
  elseif (in_array($k, $photolist)) {
    if ($k=='DaysRelMaxLC')  $k = 'DaysRelMax';
    $name = "t3." . $k;}

  //grabs the low and high inputs entered into html form
  $low= $v[0];
  $high= $v[1];
  //takes off any blank spaces in input
  $low=trim($low);
  $high=trim($high); 
  //check for blanks
  if (($low == '')&&($high == '')) {
    $segment = $name . " IS NULL AND ";
  }
  else {
    list($yy,$mm,$dd)= explode("-", $low);
    list($yy2,$mm2,$dd2)= explode("-", $high);

    //Checks if input is a number or a UT date, if not uses 0 as value
    if (is_numeric($low))
      {
	//Check if it's a JD or a UT with no '-'
	if ($low < 2400000)
	  {  $low = $low*0.9999;} // stupid MySQL float rounding issue
      }
    elseif (checkdate($mm,$dd,$yy))
      $low = "'".$low."'";
    else
      $low = 0;
    
    if (is_numeric($high))
      {
	//Check if it's a JD or a UT with no '-'
	if ($high < 2400000)
	  {  $high = $high*1.0001;} // stupid MySQL float rounding issue
      }
    elseif (checkdate($mm2,$dd2,$yy2))
      $high = "'".$high."'";
    else 
      $high = 0;

    //returns the mysql statement
    $segment = "(" . $name . " >= " . $low . " AND " . $name . " <= " . $high . ") AND ";
  }

  return ($segment);
}
  
//**************************************************************************

// Function Name: 
// cboxquery

// Inputs:
// $key = Name of the column to be queried
// $value = array containing the values of the selected boxes

// Outputs:
// A MySqL query segment to be appended to the end of the existing query

function cboxquery($key, $value, $objectlist, $speclist, $photolist) {

  // Add the appropriate prefix for each table
  // object is t1, spectra is t2, photometry is t3
  if (in_array ($key, $objectlist)) {
    $name = "t1." . $key;}
  elseif (in_array($key, $speclist)) {
    $name = "t2." . $key;}
  elseif (in_array($key, $photolist)) {
    $name = "t3." . $key;}

  // Start the query segment:
  $segment = '(';

  if ($key == 'InstrumentID') {
    
    foreach ($value as $k=>$id) {
      if ($id == 'other') {
	$segment = $segment . "((" . $name . " != 5) AND (" . $name . " != 13) AND (" . $name . " != 1) AND (" . $name . " != 6)) OR ";
	}
      else {
	$segment = $segment . $name . " = " . $id . " OR ";    
      }
    }
  }

  // Now handle if the key is type:
  
  elseif ($key == 'Type') {
    foreach ($value as $k=>$id) {
      
      // if type is unknown change it to NULL
      if ($id == 'unknown') {
	$segment = $segment . $name . " is NULL OR ";
      }

      else {
	$segment = $segment . $name . " = '" . $id . "' OR " . $name . " = '" . $id . "?' OR ";
      }
    }
  }

  elseif (($key == 'Type_SNID') || ($key == 'Subtype_SNID') || ($key == 'Subtype_SNID_obj')) {
    foreach ($value as $k=>$id) {
      
      // if type is unknown change it to NULL
      if (($id == 'SNIDTypeunknown') || ($id == 'SNIDSubtypeunknown') || ($id == 'SNIDSubtypeobjunknown')) {
	$segment = $segment . $name . " is NULL OR ";
      }

      else {
	$segment = $segment . $name . " = '" . $id . "' OR ";
      }
    }
  }

  elseif ($key == 'TelescopeID') {
    
     foreach ($value as $k=>$id) {
      if ($id == 'other') {
	$segment = $segment . "((" . $name . " != 5) AND (" . $name . " != 4)) OR ";
	}
      else {
	$segment = $segment . $name . " = " . $id . " OR ";    
      }
    }
  }

  elseif ($key == 'FilterID') {
    
    foreach ($value as $k=>$id) {
      if ($id == 'other') {
	$segment = $segment . "((" . $name . " != 9) AND (" . $name . " != 1) AND (" . $name . " != 2) AND (" . $name . " != 3) AND (" . $name . " != 4) AND (" . $name . " != 5) AND (" . $name . " != 6) AND (" . $name . " != 7) AND (" . $name . " != 8)) OR ";
	}
      else {
	$segment = $segment . $name . " = " . $id . " OR ";    
      }
    }
  }
  
  else {
    
     foreach ($value as $k=>$id) {
       $segment = $segment . $name . " = " . $id . " OR ";    
     }
  }

  $segment = substr($segment,0,-4) . ") AND ";
  return ($segment);
}
  
  
  



