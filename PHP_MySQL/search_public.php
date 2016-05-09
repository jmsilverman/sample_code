<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en-us">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">

<title>Public SN Database Search Results</title>

<style type='text/css'>
table.sortable th:hover { color : red; }
table.sortable th.sorttable_nosort:hover { color : black; }
</style>


<script type="text/javascript" src="sorttable.js"></script>
<script type="text/javascript" src="overlib.js"></script>
<script>
function checkAll(checkname, exby) {
  for (i = 0; i < checkname.length; i++)
 {
  checkname[i].checked = exby.checked? true:false
 }
}
</script>

</head>

<body>
<a name='top'/>
<h1 align='center'>Public SN Database Search Results</h1>
<p align='center'>:::::<a href='index.html'>SNDB Home Page</a>:::::</p>
<p><a href='searchform_public.html'>Back</a> to SN Database Search page.</p>

<form name='tarandplot' action='tarandplot_public.php' method='post'>

<?php

include ("searchfunction.php");


// define list of SNe Ia with host info in Niell, et al. 2009, ApJ, 707, 1449
$niell_list = array("SN 1980N", "SN 1981B", "SN 1981D", "SN 1990N", "SN 1991T", "SN 1991U", "SN 1991ag", "SN 1992A", "SN 1992P", "SN 1992ag", "SN 1992bc", "SN 1992bl", "SN 1992bo", "SN 1993H", "SN 1993ae", "SN 1994M", "SN 1994Q", "SN 1994S", "SN 1994ae", "SN 1995D", "SN 1995E", "SN 1995al", "SN 1995bd", "SN 1996C", "SN 1996X", "SN 1996Z", "SN 1996ai", "SN 1996bo", "SN 1996bv", "SN 1997E", "SN 1997Y", "SN 1997bp", "SN 1997bq", "SN 1997br", "SN 1997cw", "SN 1997do", "SN 1998V", "SN 1998ab", "SN 1998aq", "SN 1998bu", "SN 1998de", "SN 1998dh", "SN 1998dm", "SN 1998dx", "SN 1998ec", "SN 1998eg", "SN 1998es", "SN 1999X", "SN 1999aa", "SN 1999ac", "SN 1999cc", "SN 1999cl", "SN 1999cp", "SN 1999da", "SN 1999dk", "SN 1999dq", "SN 1999ee", "SN 1999gd", "SN 2000E", "SN 2000ca", "SN 2000ce", "SN 2000cx", "SN 2000dk", "SN 2000fa", "SN 2001N", "SN 2001V", "SN 2001ay", "SN 2001az", "SN 2001ba", "SN 2001da", "SN 2001el", "SN 2001en", "SN 2001ep", "SN 2001fe", "SN 2001ie", "SN 2002bf", "SN 2002bo", "SN 2002cd", "SN 2002ck", "SN 2002cr", "SN 2002de", "SN 2002dj", "SN 2002dp", "SN 2002er", "SN 2002es", "SN 2002fk", "SN 2002ha", "SN 2002he", "SN 2002hu", "SN 2002hw", "SN 2002jy", "SN 2003U", "SN 2003W", "SN 2003cg", "SN 2003du", "SN 2003fa", "SN 2003hu", "SN 2003hx", "SN 2003ic", "SN 2003kc", "SN 2003kf", "SN 2004L", "SN 2004as", "SN 2004eo", "SN 2004fu", "SN 2005am", "SN 2005eq", "SN 2005hc", "SN 2005hk", "SN 2005iq", "SN 2005ir", "SN 2005kc", "SN 2005ke", "SN 2005ki", "SN 2005ls", "SN 2005mc", "SN 2005ms", "SN 2005mz", "SN 2006N", "SN 2006S", "SN 2006X", "SN 2006ac", "SN 2006ak", "SN 2006al", "SN 2006ar", "SN 2006ax", "SN 2006az", "SN 2006bk", "SN 2006bq", "SN 2006br", "SN 2006bt", "SN 2006bw", "SN 2006cc", "SN 2006cj", "SN 2006cm", "SN 2006cp", "SN 2006ef", "SN 2006ej", "SN 2006en", "SN 2006gj", "SN 2006gz", "SN 2006hb", "SN 2006kf", "SN 2006le", "SN 2006mo", "SN 2006nz", "SN 2006ob", "SN 2006on", "SN 2006os", "SN 2006sr", "SN 2006te", "SN 2007F", "SN 2007O", "SN 2007R", "SN 2007S", "SN 2007ae", "SN 2007af", "SN 2007ap", "SN 2007au", "SN 2007bc", "SN 2007bd", "SN 2007bm", "SN 2007bz", "SN 2007cg", "SN 2007ci", "SN 2007sr", "SN 2008af", "SN 2008bf");


// Create the MySQL Query

// Seperate the data into inputs and outputs:
$data = GetChecked($_POST);

// Separate the inputs and outputs
$objrow=1;
$input = $data[0];
$output = $data[1];
$output2["Object"] = "ObjNameObjOut";
$LCinput = $data[2];
$LCoutput = $data[3];

// Check to see if output form is empty:
if (empty($output)&&empty($LCoutput)) {
	 echo "<p align='center'><b>You Forgot to Choose an Output.</b></p>\n";
} 
else {

   // Make sure that ObjName is one of the options selected.
   if (!isset($output["Object"])) {
      $objrow=0;
      if (!empty($output)) {
	$output = $output2 + $output;
      }	else {
	$output = $output2;
      }
   }

   // Check to see if we have to deal with LC plotting
   $LCplot = 0;
   if (!empty($LCinput)) $LCplot = 1;
   if (!empty($LCoutput)) $LCplot = 1;


   // Define inputs and their types 
   // List of text type inputs:
   $text = array("ObjName","DiscBy","HostName","HostType","Filename","MLCS_fit_filename","MLCS_fit_filename_MLCS17","MLCS_fit_filename_MLCS31","BestMatch_SNID","Reference","LCReference");
   // List of range type inputs:
   $ranges = array("RA","Decl","DiscDate","Redshift_Gal","Redshift_SN","Reddening","UT_Date","Min","Max","Blue_Resolution","Red_Resolution","Airmass","Seeing","SNR","Exposure","Parallactic_Angle","Position_Angle","DaysRelMax","JD_max","mu","A_V","Delta","R_V","m_V","Red_Chi_Sqr","m_B_SALT2","x_1_SALT2","c_SALT2","mu_SALT2","Red_Chi_Sqr_SALT2","m_B_SALT","s_SALT","c_SALT","mu_SALT","Red_Chi_Sqr_SALT","m_B_SALT","s_SALT","c_SALT","mu_SALT","Red_Chi_Sqr_SALT","JD","Num_Spec","Num_Phot","Num_B","Num_V","Num_R","Num_I","Num_unfiltered","MLCS_Lum","dM15","max_mag","bv","z_SNID","DaysRelMax_SNID","rlap_SNID","JD_max_MLCS31","mu_MLCS31","A_V_MLCS31","Delta_MLCS31","m_V_MLCS31","Red_Chi_Sqr_MLCS31","JD_max_MLCS17","mu_MLCS17","A_V_MLCS17","Delta_MLCS17","m_V_MLCS17","Red_Chi_Sqr_MLCS17","JD","Value","DaysRelMaxLC");
   // List of checkbox type inputs:
   $cbox = array("Type","InstrumentID","TelescopeID","FilterID","Type_SNID","Subtype_SNID","Subtype_SNID_obj","Specphot_Correction");


   // Define inputs and their tables
   // List of object inputs:
   $objectlist = array("ObjName","RA","Decl","Type","DiscDate","DiscBy","HostName","HostType","Redshift_Gal","Redshift_SN","Reddening","MLCS_fit_filename","MLCS_Lum","dM15","max_mag","bv","JD_max","mu","A_V","Delta","R_V","m_V","Red_Chi_Sqr","m_B_SALT2","x_1_SALT2","c_SALT2","mu_SALT2","Red_Chi_Sqr_SALT2","m_B_SALT","s_SALT","c_SALT","mu_SALT","Red_Chi_Sqr_SALT","Num_Spec","Num_Phot","Num_B","Num_V","Num_R","Num_I","Num_unfiltered","Subtype_SNID_obj","LCReference","MLCS_fit_filename_MLCS31","JD_max_MLCS31","mu_MLCS31","A_V_MLCS31","Delta_MLCS31","m_V_MLCS31","Red_Chi_Sqr_MLCS31","MLCS_fit_filename_MLCS17","JD_max_MLCS17","mu_MLCS17","A_V_MLCS17","Delta_MLCS17","m_V_MLCS17","Red_Chi_Sqr_MLCS17");      
   // List of spectra inputs:
   $speclist = array("UT_Date","Filename","Min","Max","Blue_Resolution","Red_Resolution","Exposure","Parallactic_Angle","Position_Angle","Airmass","Seeing","SNR","DaysRelMax","InstrumentID","z_SNID","DaysRelMax_SNID","rlap_SNID","Type_SNID","Subtype_SNID","BestMatch_SNID","Reference","Specphot_Correction");
   // List of photometry inputs:
   $photolist = array("TelescopeID","FilterID","JD","Value","DaysRelMaxLC");

 
   // Specify which outputs to retrieve:
   $query = "SELECT ";
   $query2 = "SELECT ";

   //set flag variables to 0 so when a search is run it doesnt query all tables
   $objecttrue=0;
   $spectrue=0;
   $phototrue=0;
   $filtertrue=0;
   $teletrue=0;
   $instrumtrue=0;
   $specruntrue=0;

   //check all inputs to make sure we query the correct tables
   if (($input!=='empty')&&(!empty($input))) {
     foreach ($input as $k=>$v)
       {
	 $tempthing = explode("__",$k);
	 if(in_array($tempthing[0],$objectlist))    $objecttrue=1;
	 if(in_array($tempthing[0],$speclist))      $spectrue=1;
       }
   }
   if (($LCplot==1)&&(!empty($LCinput))) {
     foreach ($LCinput as $k=>$v)
       {
	 $tempthing = explode("__",$k);
	 if(in_array($tempthing[0],$photolist))     $phototrue=1;
       }
   }

   foreach ($output as $k=>$v) {
     // Add to the end of $query based on whether its an object or spectra element
     $posObj = strpos($v, "ObjOut");
     $posSpec = strpos($v, "SpecOut");
     $posTele= strpos($v, "TeleOut");
     $posInstrum= strpos($v, "InstrumOut");
     $posSpecrun= strpos($v, "SpecrunOut");
     if ($posObj) {
       $objecttrue=1;
       $name = substr($v, 0, -6);
       $query = $query . "t1." . $name . ", "; // objects is aliased to t1
       $output[$k] = $name;
       unset($name);}
     if ($posSpec){
       $spectrue=1;
       $name = substr($v, 0, -7);
       $query = $query . "t2." . $name . ", "; // spectra is aliased to t2
       $output[$k] = $name;
       unset($name);}
     if ($posTele){
       $teletrue=1;
       $phototrue=1;
       $name = substr($v, 0, -7);
       $query = $query . "t5." . $name . ", "; // telescope is aliased to t5
       $output[$k] = $name;
       unset($name);}
     if ($posInstrum){
       $instrumtrue=1;
       $spectrue=1;
       $name = substr($v, 0, -10);
       $query = $query . "t6." . $name . ", "; // instrument is aliased to t6
       $output[$k] = $name;
       unset($name);}
     if ($posSpecrun){
       $specruntrue=1;
       $spectrue=1;
       $name = substr($v, 0, -10);
       $query = $query . "t7." . $name . ", "; // spectralruns is aliased to t7
       $output[$k] = $name;
       unset($name);}
   }

   if ($LCplot==1) {
     // Add to the end of $query2 based on whether its an object or spectra elemen
     $query2 = $query2 . "t1.ObjName, t1.ObjID, t3.JD, t3.Value, t3.Error, t3.DaysRelMax, t4.Filter, t5.Name";
   }
 
   // If filename was not selected, add it in for use when tarring and plotting:
   if (!in_array('Filename',$output) && $spectrue) {
     $query = $query . "t2.Filename, ";
   }

   // Remove the last comma, and add the FROM statement
   $query = substr($query, 0, -2);

   $query = $query . " FROM ";
   //if statement to say what mysql query to send
   if ($objecttrue) {
     $query = $query . "objects_public as t1, ";} // FIXME for public vs. private
   if ($spectrue) {
    $query = $query . "spectra_public as t2, ";} // FIXME for public vs. private
   if ($teletrue) {
    $query = $query . "telescopes as t5, ";}
   if ($instrumtrue) {
    $query = $query . "instruments as t6, ";}
   if ($specruntrue) {
    $query = $query . "spectralruns as t7, ";}

   if ($LCplot==1) {
     // FIXME for public vs. private
     $query2 = $query2 . " FROM objects_public as t1, photometry_public as t3, filters as t4, telescopes as t5";
   }

   // Remove the last comma from the list of tables
   $query = substr($query, 0, -2);

   // Add the WHERE statement:
   $query = $query . " WHERE (";
   //if statement for the WHERE part of mysql query
   if ($objecttrue AND $spectrue) {
   	$query = $query . "t1.ObjID = t2.ObjID AND ";
                    }
   if ($spectrue AND $instrumtrue) {
   	$query = $query . "t2.InstrumentID = t6.InstrumentID AND ";
                    }
   if ($spectrue AND $specruntrue) {
   	$query = $query . "t2.RunID = t7.RunID AND ";
                    }

   if ($LCplot==1) {
     $query2 = $query2 . " WHERE (t1.ObjID = t3.ObjID AND t3.FilterID = t4.FilterID AND t3.TelescopeID = t5.TelescopeID AND ";
   }

   // Make sure at least one input was checked:
   if ( ($input!=="empty")&&(!empty($input)) ) { 
     
     $input = combrange($input, $ranges);
     $input = combcheck($input, $cbox);

     foreach ($input as $key => $val) {
       
       if (in_array ($key, $text)) {
	 $new = textquery($key, $val, $objectlist, $speclist, $photolist);
	 $query = $query . $new; 
       }
       elseif (in_array ($key, $ranges)) {
	 $new = rangequery($key, $val, $objectlist, $speclist, $photolist);
	 $query = $query . $new;
       }
       elseif (in_array ($key, $cbox)) {
	 $new = cboxquery($key, $val, $objectlist, $speclist, $photolist);
	 $query = $query . $new;
       }
     }
     $query = substr($query, 0, -5);
     $query = $query . ")";
   }
   else {
     // If there were no inputs selected, show all:
     $query = $query . "1)";
   }

   // Define the default ORDER:
   $query = $query . " ORDER BY ObjName;";

   // Make sure at least one input was checked:
   if ($LCplot==1) {
     if (($LCinput!=="empty")&&(!empty($LCinput))) { 
     
       $LCinput = combrange($LCinput, $ranges);
       $LCinput = combcheck($LCinput, $cbox);

       foreach ($LCinput as $key => $val) {
       
	 if (in_array ($key, $text)) {
	   $new = textquery($key, $val, $objectlist, $speclist, $photolist);
	   $query2 = $query2 . $new; 
	 }
	 elseif (in_array ($key, $ranges)) {
	   $new = rangequery($key, $val, $objectlist, $speclist, $photolist);
	   $query2 = $query2 . $new;
	 }
	 elseif (in_array ($key, $cbox)) {
	   $new = cboxquery($key, $val, $objectlist, $speclist, $photolist);
	   $query2 = $query2 . $new;
	 }
       }
       // $query2 needs to have the ObjName added to the WHERE clause
     }
     else {
       // If there were no inputs selected show all:
       $query2 = $query2. "1 AND ";
     }
   }

// define username, password, and database for MySQL
$username="anon";
$password="anon";
$database="sndb";

// connect to database and choose SN database
@mysql_connect("localhost",$username,$password) or die("<br><h2 align='center'>Incorrect password, go <a href='javascript:history.go(-1)'>back</a> and try again!</h2>");
mysql_select_db($database);

// query the sn database
$results = mysql_query($query);

// if you chose no outputs
if (empty($results)) {
   $Num=0; }
else{

  // save number of results returned
  $Num = mysql_numrows($results);
}

// initialize whether or not we want the specphot corrected spectra
$specphot = 0;

// print object query code
echo "<p>Your MySQL query is (ObjName ";
if($spectrue)	echo "and Filename are ";
else		echo "is ";
echo "automatically added as output): <code>" . $query . "</code></p>\n";

// if there was info returned
if($Num)
  {

    // Start spectra counter
    $NumSpec = 0;

    // initialize list of objects
    $sn_list = array();
    $sn_list_LC = array();

    // initialize printed LC query?
    $printedLC = 0;

    // Go through each line returned
    for($i=0;$i<$Num;$i++) {

      // get SN name
      $sn_name = trim(mysql_result($results,$i,'ObjName'));

      // assume good LC
      $goodLC = 1;

      // if we need to deal with the LCs
      if ( ($LCplot==1)&&(!in_array($sn_name, $sn_list_LC))) {

	// do LC query
	$query3 = $query2 . "ObjName='" . $sn_name . "') ORDER BY t3.FilterID, t3.TelescopeID, t3.JD";
	$results3 = mysql_query($query3);
	// print LC query code
	if ($printedLC==0) {
	  echo "<p>One of your MySQL photometry queries is (ObjName is automatically added as output): <code>" . $query3 . "</code></p>\n";
	  $printedLC = 1;
	}

	// if no LC points match search
	$LCnumrows = mysql_numrows($results3);
	if ($LCnumrows===0) {
	  $goodLC = 0;
	} else {
	  
	  // get SN name
	  $objname = split(' ',mysql_result($results,$i,'ObjName'));
	  $objname = strtolower($objname[0]) . strtolower($objname[1]);
	    
	  // save current object
	  $sn_list_LC[] = $sn_name;

	  // initialize list of LCs
	  $LClist = '';

	  // initialize last filter and telescope
	  $filter_prev = '0';
	  $telescope_prev = '0';
	  $first = 1;
	    
	  // go through each phot point returned
	  for ($j=0;$j<$LCnumrows;$j++)
	    {
	      // get filter & telescope
	      $filter = mysql_result($results3,$j,'t4.Filter');
	      if($filter=='none') $filter = 'Unf';
	      $telescope = mysql_result($results3,$j,'t5.Name');
	      $telescope = str_replace(",","",$telescope);
	      $telescope = str_replace(" ","-",$telescope);
	      
	      // if different filter or telescope
	      if ( ($filter!=$filter_prev)||($telescope!=$telescope_prev) ) {
		
		// save new last filter and telescope
		$filter_prev = $filter;
		$telescope_prev = $telescope;
		
		// close previous file
		if ($first!=1) fclose($handle);
		$first = 0;
		
		// make LC filename
		$LCfilename = $objname . '-' . $telescope . '-' . $filter . '.dat';
		
		// add it to the list of LC files
		$LClist = $LClist . $LCfilename . ' ';
		
		// check for object folder and make it if it's not there
		if(!is_dir("data/public/photometry/".$objname)) mkdir("data/public/photometry/".$objname);

		// open the file for writing and erase contents
		$handle = fopen("data/public/photometry/".$objname."/".$LCfilename,'w');
	      }
	      
	      // write current line of numbers
	      $dat = mysql_result($results3,$j,'t3.JD');
	      $buf = str_repeat(' ',14-strlen($dat));
	      fwrite($handle,'    '.$dat.$buf);
	      $dat = mysql_result($results3,$j,'t3.Value');
	      $buf = str_repeat(' ',12-strlen($dat));
	      fwrite($handle,$dat.$buf);
	      $dat = mysql_result($results3,$j,'t3.Error');
	      fwrite($handle,$dat."\n");
	    }
	  
	  // close last file
	  fclose($handle);
	  
	  // save ASCII files
	  if (!isset($LCfiles)) $LCfiles[] = $LClist;
	  if (!in_array($LClist,$LCfiles)) $LCfiles[] = $LClist;
	}
      }
      
      // Get a list of all the good Object Names that ignores repeats:
      // To do this, loop through $results and pick out all ObjName and put them into an array:
      if ($goodLC) {
	if (!in_array($sn_name, $sn_list)) {$sn_list[] = $sn_name;}
	$NumSpec++;
      }
    }
    $NumObj = count($sn_list);

    // print number of results
    echo "<p>Your query returned " . $NumObj . " object";
    if($NumObj>1) echo "s";
    if($spectrue) {
      echo " and " . $NumSpec . " spectr";
      if($NumSpec>1) echo "a";
      else echo "um";
    }
    echo ".</p>\n";

    echo "<p>Click on Column Headers to re-sort the table in either ascending or descending order based on your chosen column.</p>\n";
    echo "<p>To see a text version of the table, <a href='data/public/searchresults.txt'>click here</a>.<br>\n";
    echo "To download the file, right click on the link and click <code>Save Link as...</code><br>\n";
    echo "Note that the online data (for plotting and downloading) get updated every Sunday so it won't be available immediately after the data has been reduced.</p>\n";

    // Make the table
    echo "<table id='SearchResults' class='sortable' border='1' cellpadding='3%' align='center'>\n";

    // Begin the text version of the table
    $filename = "data/public/searchresults.txt";
    $texttable = fopen($filename, 'w');

    // get number of outputs
    $NumOutputs = count($output);
    if (!empty($LCoutput)) $NumOutputs++;

    // Put table headers and commands into text version of table
    fwrite($texttable,"\begin{table}\n");
    $begin_table = '\begin{tabular}{l';
    if (!$objrow) $NumOutputs--;
    for($iii=1;$iii<$NumOutputs;$iii++)   $begin_table = $begin_table . 'c';
    if (!$objrow) $NumOutputs++;
    $begin_table = $begin_table . "}\n";
    fwrite($texttable,$begin_table);
    fwrite($texttable,"\hline\n");

    // Headers
    echo "<tr>\n";

    if ($spectrue) {
      echo "<th class='sorttable_nosort'><input type=submit name='submit' value='Plot Spectrum'><br><a href='javascript:void(0);' onmouseover='return overlib(\"Only the first 5 spectra will be plotted.\");' onmouseout='return nd();'>NOTE</a></th>\n";
      echo "<th class='sorttable_nosort'><input type=submit name='submit' value='Tar Spectra'><br><input type='checkbox' name='all' onClick='checkAll(document.getElementsByName(\"tarFile[]\"),this)'>Select/Unselect All<br><a href='javascript:void(0);' onmouseover='return overlib(\"Only tar &lt;~70 spectra. If you get a page with a <code>Fatal error</code> message, try reloading that page a few times.\");' onmouseout='return nd();'>NOTE</a></th>\n";
    }
    if ($LCplot==1) {
      echo "<th class='sorttable_nosort'><input type=submit name='submit' value='Plot Photometry'><br><a href='javascript:void(0);' onmouseover='return overlib(\"Photometry for only the first 2 objects will be plotted.\");' onmouseout='return nd();'>NOTE</a></th>\n";
      echo "<th class='sorttable_nosort'><input type=submit name='submit' value='Tar Photometry'><br><input type='checkbox' name='all' onClick='checkAll(document.getElementsByName(\"tarPhot[]\"),this)'>Select/Unselect All<br><a href='javascript:void(0);' onmouseover='return overlib(\"Only tar &lt;~70 objects. If you get a page with a <code>Fatal error</code> message, try reloading that page a few times.\");' onmouseout='return nd();'>NOTE</a></th>\n";
     }

    // initialize output counter
    $OutputCounter = 0;
    if(!$objrow) {
      $OutputCounter++;
      if(!empty($LCoutput)) {
	$OutputCounter++;		      // increment output counter
	$header = "Phot Data";
        echo "<th>" . $header . "</th>\n";
	if($OutputCounter != $NumOutputs)
	        $fileheader = $header . "\t & ";
	else
		$fileheader = $header . "\t \\\\\n";
        fwrite($texttable, $fileheader);
      }
    }
    foreach ($output as $k=>$v) {
      if (($objrow && $k=="Object") || $k!="Object"){ 
        $OutputCounter++;		      // increment output counter
        $header = str_replace("_"," ",$k);    // Replace "_" with " "
	// deal special with Specphot. Correction
	if ($header == 'Flux Correction') {
	  echo "<th><a href=\"javascript:void(0);\" onmouseover=\"return overlib('0: No correction.<br>1: Not parallactic, but scaled.<br>2: Not parallactic, but galaxy subtracted with no contamination.<br>3: Not parallactic, but galaxy subtracted with contamination.<br>4: Parallactic and scaled.<br>5: Parallactic and galaxy subtracted with no contamination.<br>6: Parallactic and galaxy subtracted with contamination.<br>Negative values indicate that >5% of the corrected flux is negative. See <b>Silverman, et al. 2012, <i>MNRAS</i>, <b>425</b>, 1789</b> for more info regarding the spectrophotometric corrections.');\" onmouseout=\"return nd();\">Specphot.<br>Correction</a></th>\n";
	  $specphot = 1;
	}
	else {
	  echo "<th>" . $header . "</th>\n";
	}
	$header = str_replace("&","\&",$header);
	$header = str_replace("#","\#",$header);
	if($OutputCounter != $NumOutputs)
	        $fileheader = $header . "\t & ";
	else
		$fileheader = $header . "\t \\\\\n";
        fwrite($texttable, $fileheader);

	if ( (!empty($LCoutput)) && ($k=="Object") ) {
	  $OutputCounter++;		      // increment output counter
	  $header = "Phot Data";
	  echo "<th>" . $header . "</th>\n";
	  if($OutputCounter != $NumOutputs)
	    $fileheader = $header . "\t & ";
	  else
	    $fileheader = $header . "\t \\\\\n";
	  fwrite($texttable, $fileheader);
	}
       }
    }
   
    echo "</tr>\n";
    echo "</form>\n";
    
    fwrite($texttable,"\hline\n");

    // Data

    // Loop through each piece of data returned:
    for($i=0;$i<$Num;$i++) {

      // Make sure it's a good object (from LC cuts)
      $sn_name = trim(mysql_result($results,$i,'ObjName'));
      if(in_array($sn_name, $sn_list)) {

	echo "<tr>\n";

	// Add the checkbox for tar and plotting spectra
	if ($spectrue) {
	  $filename = mysql_result($results,$i,"Filename");
	  $objname = split(' ',mysql_result($results,$i,'ObjName'));
	  $objname = strtolower($objname[0]) . strtolower($objname[1]);
	  echo "<td><input type=checkbox name='plotFile[]' value='data/public/" . $objname . "/" . $filename . "'></td>\n";
	  // Deal with specphot correction
	  if (($specphot==0) || (mysql_result($results,$i,'Specphot_Correction')==0)) {
	    echo "<td><input type=checkbox name='tarFile[]' value='data/public/" . $objname . "/" . $filename . "'></td>\n";
	  }
	  else {
	    $filename2 = substr($filename,0,-4).'-corrected.flm';
	    echo "<td><input type=checkbox name='tarFile[]' value='data/public/" . $objname . "/" . $filename . ";data/public/" . $objname . "/" . $filename2 . "'></td>\n";
	  }
	}  
	
	// Add the checkbox for tar and plotting LCs
	if ($LCplot==1) {
	  // FIXME make sure the pathnames are good here and go to ALL current LCs for the current SN
	  $j = array_search($sn_name,$sn_list);
	  $objname = split(' ',mysql_result($results,$i,'ObjName'));
	  $objname = strtolower($objname[0]) . strtolower($objname[1]);

	  // go through each LC
	  $LCpieces = explode(" ",$LCfiles[$j]);
	  $LCstring = '';
	  for($jj=0;$jj<count($LCpieces)-1;$jj++) {
	    $LCstring = $LCstring . 'data/public/photometry/' . $objname . '/' . $LCpieces[$jj] . ';';
	  }
	  $LCstring = substr($LCstring,0,-1);
	  echo "<td><input type=checkbox name='plotPhot[]' value='" . $LCstring . "'></td>\n";
	  echo "<td><input type=checkbox name='tarPhot[]' value='". $LCstring . "'></td>\n";
	}

	// initialize output counter
	$OutputCounter = 0;
	if(!$objrow) {
	  $OutputCounter++;
	  if(!empty($LCoutput)) {
	    $OutputCounter++;
	    echo "<td>";
	    // go through each LC
	    $LCpieces = explode(" ",$LCfiles[$j]);
	    $filedat = '';
	    for($jj=0;$jj<count($LCpieces)-2;$jj++) {
	      echo "<a href='data/public/photometry/" . $objname . "/" . $LCpieces[$jj] . "'>" . $LCpieces[$jj] . "</a><br>\n";
	      $filedat = $filedat . $LCpieces[$jj] . '   ';
	    }
	    echo "<a href='data/public/photometry/" . $objname . "/" . $LCpieces[$jj] . "'>" . $LCpieces[$jj] . "</a>\n";
	    $filedat = $filedat . $LCpieces[$jj];
	    echo "</td>\n";
	    if($OutputCounter != $NumOutputs)
	      $filedat = $filedat . "\t & ";
	    else
	      $filedat = $filedat . "\t \\\\\n";
	    fwrite($texttable, $filedat);
	  }
	}

	// Loop through each output
	foreach($output as $k=>$v) {
	  if (($objrow && $v=="ObjName") || $v!="ObjName"){
	    $OutputCounter++;		      		// increment output counter
	  
	    // deal with Instrument Name versus Telescope Name
	    if ($k=='Telescope_(photometry)')
	      $dat = trim(mysql_result($results,$i,"t5.".$v));
	    elseif ($k=='Instrument_(spectra)')
	      $dat = trim(mysql_result($results,$i,"t6.".$v));
	    else
	      $dat = trim(mysql_result($results,$i,$v));
	  
	    if ($dat!='') { 

	      // Special handling of Hostname, DiscReference, TypeReference, Filename, MLCS fit filename, LC reference, and spectral reference:
	      if ($v=='HostName' && $dat!='Anon.') {
		
		$newdat = str_replace('+','%2B',$dat);
		$hostquery = 'SELECT RA,Decl FROM objects_public WHERE ObjName="'.$sn_name.'"';
		$hostresults = mysql_query($hostquery);
		echo "<td><a href='http://nedwww.ipac.caltech.edu/cgi-bin/nph-objsearch?objname=" . $newdat . "&extend=no' target=_blank>" . $dat . "</a> (<a href='http://cas.sdss.org/dr7/en/tools/chart/navi.asp?opt=PGS&ra=".mysql_result($hostresults,0,'RA')."&dec=".mysql_result($hostresults,0,'Decl')."&scale=0.7' target=_blank>SDSS</a>) (<a href='http://irsa.ipac.caltech.edu/cgi-bin/2MASS/IM/nph-im_pos?POS=".$newdat."' target=_blank>2MASS</a>)";
		if (in_array($sn_name,$niell_list))
		  echo " (<a href='http://adsabs.harvard.edu/abs/2009ApJ...707.1449N' target=_blank>more host info</a>)";
		echo "</td>\n";
	      }
	      
	      elseif ($v=='DiscReference' || $v=='TypeReference') {
		
		// CBET
		if(substr($dat,0,1)=='C') {
		  if (strlen($dat)==4)
		    $folder = '0' . substr($dat,1,1);
		  elseif (strlen($dat)<4)
		    $folder = '00';
		  else
		    $folder = substr($dat,1,2);
		  if (strlen($dat)==2)	$dat = 'CBET 0' . substr($dat,1,1);
		  else			$dat = 'CBET ' . substr($dat,1);
		  echo "<td><a href='http://www.cbat.eps.harvard.edu/iau/cbet/00" . $folder . "00/CBET00" . $folder . substr($dat,-2) . ".txt' target=_blank>" . $dat . "</a></td>\n";
		}
		
		// ATEL
		elseif(substr($dat,0,1)=='A') {
		  $dat = 'ATEL ' . substr($dat,1);
		  echo "<td><a href='http://www.astronomerstelegram.org/?read=" . substr($dat,5) . "' target=_blank>" . $dat . "</a></td>\n";
		}
		
		// IAUC
		elseif(is_numeric(substr($dat,0,4))) {
		  if (strlen($dat)==3)
		    $folder = '0' . substr($dat,0,1);
		  elseif (strlen($dat)<3)
		    $folder = '00';
		  else
		    $folder = substr($dat,0,2);
		  $dat = 'IAUC ' . $dat;
		  echo "<td><a href='http://www.cbat.eps.harvard.edu/iauc/0" . $folder . "00/0" . $folder . substr($dat,-2) . ".html' target=_blank>" . $dat . "</a></td>\n";
		}
		else echo "<td>".$dat."</td>\n";
	      }
	      
	      elseif ($v=='Filename') {
		echo "<td><a href='data/public/" . $objname . "/" . $dat . "'>" . $dat . "</a></td>\n";
	      }

	      elseif ($v=='Specphot_Correction') {
		if ($dat==0)
		  echo "<td>" . $dat . "</td>\n";
		else
		  echo "<td><a href='data/private/" . $objname . "/" . $filename2 . "'>" . $dat . "</a></td>\n";
	      }

	      elseif (($v=='MLCS_fit_filename') || ($v=='MLCS_fit_filename_MLCS17') || ($v=='MLCS_fit_filename_MLCS31')) {
		echo "<td><a href='data/public/mlcs_fits/" . $dat . "'>" . $dat . "</a></td>\n";
	      }
	      
	      elseif (($v=='Reference') && (strpos($dat,'in prep')===FALSE) && (strpos($dat,'unpub')===FALSE) && (strpos($dat,'Dordrecht: Kluwer')===FALSE)) {
		if (strpos($dat,'arXiv')===FALSE) {
		  $temppieces = explode(', ',$dat);
		  $count = count($temppieces);
		  $yearpiece = $temppieces[$count-4];
		  $fix = substr($temppieces[$count-2],-1);
		  if (is_numeric(substr($temppieces[$count-2],-1)))
		    $fix = '.';
		  else
		    $temppieces[$count-2] = substr($temppieces[$count-2],0,-1);
		  $temppieces[$count-3] = substr($temppieces[$count-3],0,5);
		  $site = 'http://adsabs.harvard.edu/abs/'.substr($yearpiece,-4).$temppieces[$count-3].str_repeat('.',5-strlen($temppieces[$count-3])).str_repeat('.',4-strlen($temppieces[$count-2])).$temppieces[$count-2] . $fix . str_repeat('.',4-strlen($temppieces[$count-1])).$temppieces[$count-1].substr($temppieces[0],0,1);
		  echo "<td><a href='" . $site . "' target=_blank>" . $dat . "</a></td>\n";
		}
		else
		  echo "<td><a href='http://adsabs.harvard.edu/abs/" . $dat . "'target=_blank>" . $dat . "</a></td>\n";
	      }
	      
	      elseif ($v=='LCReference') {
		$newdat = explode('; ',$dat);
		echo "<td>";
		for($ii=0;$ii<count($newdat);$ii++)
		  {
		    if ( (strpos($newdat[$ii],'in prep')===FALSE) && (strpos($newdat[$ii],'unsub')===FALSE) )
		      {
			$newdat2 = explode(': ',$newdat[$ii]);
			echo $newdat2[0].': ';
			$temppieces = explode(', ',$newdat2[+1]);
			$count = count($temppieces);
			$yearpiece = $temppieces[$count-4];
			$fix = substr($temppieces[$count-2],-1);
			if (is_numeric(substr($temppieces[$count-2],-1)))
			  $fix = '.';
			else
			  $temppieces[$count-2] = substr($temppieces[$count-2],0,-1);
			$temppieces[$count-3] = substr($temppieces[$count-3],0,5);
			$site = 'http://adsabs.harvard.edu/abs/'.substr($yearpiece,-4).$temppieces[$count-3].str_repeat('.',5-strlen($temppieces[$count-3])).str_repeat('.',4-strlen($temppieces[$count-2])).$temppieces[$count-2] . $fix . str_repeat('.',4-strlen($temppieces[$count-1])).$temppieces[$count-1].substr($temppieces[0],0,1);
			echo "<a href='" . $site . "' target=_blank>" . $newdat2[1] . "</a>";
		      }
		    else
		      echo $newdat[$ii];
		    if ($ii+1<count($newdat)) echo "; ";
		  }
		echo "</td>\n";
	      }
	      
	      // Normal handling of the rest of the results:
	      else {
		echo "<td>" . $dat . "</td>\n";
	      }
	      $dat = str_replace("&","\&",$dat);
	      if($OutputCounter != $NumOutputs)
		$filedat = $dat . "\t & ";
	      else
		$filedat = $dat . "\t \\\\\n";
	      fwrite($texttable, $filedat);
	    }
	    else {
	      if(($v=='Subtype_SNID') || ($v=='Type_SNID') || ($v=='HostType') || ($v=='Type') || ($v=='TypeReference') || ($v=='Subtype_SNID_obj') || ($v=='DiscBy') || ($v=='DiscReference') || ($v=='Notes') || ($v=='HostType') || ($v=='Filter') || ($v=='LCReference') || ($v=='MLCS_fit_filename') || ($v=='Observer') || ($v=='RunCode') || ($v=='ObservingComments') || ($v=='Reducer') || ($v=='Instrument') || ($v=='Blue_Flux_Star') || ($v=='Red_Flux_Star') || ($v=='BestMatch_SNID') || ($v=='Reference'))
		echo "<td sorttable_customkey='zzzzzzz'>&nbsp;</td>\n";
	      else
		echo "<td sorttable_customkey='9999999'>&nbsp;</td>\n";
	      if($OutputCounter != $NumOutputs)
		$filedat = "$\\cdots$\t & ";
	      else
		$filedat = "$\\cdots$\t \\\\\n";
	      fwrite($texttable, $filedat);
	    }

	    if ( (!empty($LCoutput)) && ($v=="ObjName") ) {
	      $OutputCounter++;
	      echo "<td>";
	      // go through each LC
	      $LCpieces = explode(" ",$LCfiles[$j]);
	      $filedat = '';
	      for($jj=0;$jj<count($LCpieces)-2;$jj++) {
		echo "<a href='data/public/photometry/" . $objname . "/" .$LCpieces[$jj] . "'>" . $LCpieces[$jj] . "</a><br>\n";
		$filedat = $filedat . $LCpieces[$jj] . '   ';
	      }
	      echo "<a href='data/public/photometry/" . $objname . "/" .$LCpieces[$jj] . "'>" . $LCpieces[$jj] . "</a>\n";
	      $filedat = $filedat . $LCpieces[$jj];
	      echo "</td>\n";
	      if($OutputCounter != $NumOutputs)
		$filedat = $filedat . "\t & ";
	      else
		$filedat = $filedat . "\t \\\\\n";
	      fwrite($texttable, $filedat);
	    }
	  }
	}
	
	echo "</tr>\n";
      }
    }
    echo "</table>\n";
    fwrite($texttable,"\hline\n");
    fwrite($texttable,"\end{tabular}\n");
    fwrite($texttable,"\end{table}\n");
    fclose($texttable);
}
    
else {
    echo "<p align='center'><b>Query returned no results.</b></p>\n";
}

// close database
mysql_close();    

} // endif output empty

?>

<p><a href='#top'>Top of Page</a></p>
<p align='center'>:::::<a href='index.html'>SNDB Home Page</a>:::::</p>
<p align='right'><small>&copy;2010 CGI :: Authored by Jeffrey M. Silverman, Nicholas Lee, and Christopher V. Griffith :: Advised by A. V. Filippenko</small></p>

</body>
</html>
