<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Staff/staff_manage_edit.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print __($guid, "You do not have access to this action.") ;
	print "</div>" ;
}
else {
	//Get action with highest precendence
	$highestAction=getHighestGroupedAction($guid, $_GET["q"], $connection2) ;
	if ($highestAction==FALSE) {
		print "<div class='error'>" ;
		print __($guid, "The highest grouped action cannot be determined.") ;
		print "</div>" ;
	}
	else {
		//Proceed!
		print "<div class='trail'>" ;
		print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . __($guid, "Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . __($guid, getModuleName($_GET["q"])) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Staff/staff_manage.php'>" . __($guid, 'Manage Staff') . "</a> > </div><div class='trailEnd'>" . __($guid, 'Edit Staff') . "</div>" ;
		print "</div>" ;
		
		$allStaff="" ;
		if (isset($_GET["allStaff"])) {
			$allStaff=$_GET["allStaff"] ;
		}
		$search="" ;
		if (isset($_GET["search"])) {
			$search=$_GET["search"] ;
		}
	
		if (isset($_GET["updateReturn"])) { $updateReturn=$_GET["updateReturn"] ; } else { $updateReturn="" ; }
		$updateReturnMessage="" ;
		$class="error" ;
		if (!($updateReturn=="")) {
			if ($updateReturn=="fail0") {
				$updateReturnMessage=__($guid, "Your request failed because you do not have access to this action.") ;	
			}
			else if ($updateReturn=="fail1") {
				$updateReturnMessage=__($guid, "Your request failed because your inputs were invalid.") ;	
			}
			else if ($updateReturn=="fail2") {
				$updateReturnMessage=__($guid, "Your request failed due to a database error.") ;	
			}
			else if ($updateReturn=="fail3") {
				$updateReturnMessage=__($guid, "Your request failed because your inputs were invalid.") ;	
			}
			else if ($updateReturn=="fail4") {
				$updateReturnMessage=__($guid, "Your request failed because some inputs did not meet a requirement for uniqueness.") ;	
			}
			else if ($updateReturn=="success0") {
				$updateReturnMessage=__($guid, "Your request was completed successfully.") ;	
				$class="success" ;
			}
			print "<div class='$class'>" ;
				print $updateReturnMessage;
			print "</div>" ;
		} 
	
		//Check if school year specified
		$gibbonStaffID=$_GET["gibbonStaffID"] ;
		if ($gibbonStaffID=="") {
			print "<div class='error'>" ;
				print __($guid, "You have not specified one or more required parameters.") ;
			print "</div>" ;
		}
		else {
			try {
				$data=array("gibbonStaffID"=>$gibbonStaffID); 
				$sql="SELECT gibbonStaff.*, surname, preferredName, initials, dateStart, dateEnd FROM gibbonStaff JOIN gibbonPerson ON (gibbonStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonStaffID=:gibbonStaffID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				print "<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
		
			if ($result->rowCount()!=1) {
				print "<div class='error'>" ;
					print __($guid, "The specified record cannot be found.") ;
				print "</div>" ;
			}
			else {
				//Let's go!
				$row=$result->fetch() ;
			
				if ($search!="" OR $allStaff!="") {
					print "<div class='linkTop'>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Staff/staff_manage.php&search=$search&allStaff=$allStaff'>" . __($guid, 'Back to Search Results') . "</a>" ;
					print "</div>" ;
				}
				print "<h3>" . __($guid, 'General Information') . "</h3>" ;
				?>
				<form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/staff_manage_editProcess.php?gibbonStaffID=" . $row["gibbonStaffID"] . "&search=$search&allStaff=$allStaff" ?>">
					<table class='smallIntBorder fullWidth' cellspacing='0'>	
						<tr class='break'>
							<td colspan=2> 
								<h3><?php print __($guid, 'Basic Information') ?></h3>
							</td>
						</tr>
						<tr>
							<td style='width: 275px'> 
								<b><?php print __($guid, 'Person') ?> *</b><br/>
								<span class="emphasis small"><?php print __($guid, 'This value cannot be changed.') ?></span>
							</td>
							<td class="right">
								<input readonly name="person" id="person" maxlength=255 value="<?php print formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Staff", false, true) ?>" type="text" class="standardWidth">
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Initials') ?></b><br/>
								<span class="emphasis small"><?php print __($guid, 'Must be unique if set.') ?></span>
							</td>
							<td class="right">
								<input name="initials" id="initials" maxlength=4 value="<?php print $row["initials"] ?>" type="text" class="standardWidth">
								<?php
								$idList="" ;
								try {
									$dataSelect=array("initials"=>$row["initials"]); 
									$sqlSelect="SELECT initials FROM gibbonStaff WHERE NOT initials=:initials ORDER BY initials" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { }
								while ($rowSelect=$resultSelect->fetch()) {
									$idList.="'" . $rowSelect["initials"]  . "'," ;
								}
								?>
								<script type="text/javascript">
									var initials=new LiveValidation('initials');
									initials.add( Validate.Exclusion, { within: [<?php print $idList ;?>], failureMessage: "Initials already in use!", partialMatch: false, caseSensitive: false } );
								</script>
							</td>
						</tr>
					
						<tr>
							<td> 
								<b><?php print __($guid, 'Type') ?> *</b><br/>
							</td>
							<td class="right">
								<select name="type" id="type" class="standardWidth">
									<?php
									print "<option value=\"Please select...\">" . __($guid, 'Please select...') . "</option>" ;
									print "<optgroup label='--" . __($guid, 'Basic') . "--'>" ;
										$selected="" ;
										if ($row["type"]=="Teaching") {
											$selected="selected" ;
										}
										print "<option $selected value=\"Teaching\">" . __($guid, 'Teaching') . "</option>" ;
										$selected="" ;
										if ($row["type"]=="Support") {
											$selected="selected" ;
										}
										print "<option $selected value=\"Support\">" . __($guid, 'Support') . "</option>" ;
									print "</optgroup>" ;
									print "<optgroup label='--" . __($guid, 'System Roles') . "--'>" ;
										try {
											$dataSelect=array(); 
											$sqlSelect="SELECT * FROM gibbonRole WHERE category='Staff' ORDER BY name" ;
											$resultSelect=$connection2->prepare($sqlSelect);
											$resultSelect->execute($dataSelect);
										}
										catch(PDOException $e) { }
										while ($rowSelect=$resultSelect->fetch()) {
											$selected="" ;
											if ($rowSelect["name"]==$row["type"]) {
												$selected="selected" ;
											}
											print "<option $selected value=\"" . $rowSelect["name"] . "\">" . __($guid, $rowSelect["name"]) . "</option>" ;
										}
									print "</optgroup>" ;
									?>
								</select>
								<script type="text/javascript">
									var type=new LiveValidation('type');
									type.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "<?php print __($guid, 'Select something!') ?>"});
								</script>
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Job Title') ?></b><br/>
							</td>
							<td class="right">
								<input name="jobTitle" id="jobTitle" maxlength=100 value="<?php print htmlPrep($row["jobTitle"]) ?>" type="text" class="standardWidth">
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Start Date') ?></b><br/>
								<span class="emphasis small"><?php print __($guid, 'Users\'s first day at school.') ?><br/> <?php print __($guid, "Format:") . " " ; if ($_SESSION[$guid]["i18n"]["dateFormat"]=="") { print "dd/mm/yyyy" ; } else { print $_SESSION[$guid]["i18n"]["dateFormat"] ; } ?></span>
							</td>
							<td class="right">
								<input name="dateStart" id="dateStart" maxlength=10 value="<?php print dateConvertBack($guid, $row["dateStart"]) ?>" type="text" class="standardWidth">
								<script type="text/javascript">
									var dateStart=new LiveValidation('dateStart');
									dateStart.add( Validate.Format, {pattern: <?php if ($_SESSION[$guid]["i18n"]["dateFormatRegEx"]=="") {  print "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i" ; } else { print $_SESSION[$guid]["i18n"]["dateFormatRegEx"] ; } ?>, failureMessage: "Use <?php if ($_SESSION[$guid]["i18n"]["dateFormat"]=="") { print "dd/mm/yyyy" ; } else { print $_SESSION[$guid]["i18n"]["dateFormat"] ; }?>." } ); 
								</script>
								 <script type="text/javascript">
									$(function() {
										$( "#dateStart" ).datepicker();
									});
								</script>
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'End Date') ?></b><br/>
								<span class="emphasis small"><?php print __($guid, 'Users\'s last day at school.') ?><br/> <?php print __($guid, "Format:") . " " ; if ($_SESSION[$guid]["i18n"]["dateFormat"]=="") { print "dd/mm/yyyy" ; } else { print $_SESSION[$guid]["i18n"]["dateFormat"] ; } ?></span>
							</td>
							<td class="right">
								<input name="dateEnd" id="dateEnd" maxlength=10 value="<?php print dateConvertBack($guid, $row["dateEnd"]) ?>" type="text" class="standardWidth">
								<script type="text/javascript">
									var dateEnd=new LiveValidation('dateEnd');
									dateEnd.add( Validate.Format, {pattern: <?php if ($_SESSION[$guid]["i18n"]["dateFormatRegEx"]=="") {  print "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i" ; } else { print $_SESSION[$guid]["i18n"]["dateFormatRegEx"] ; } ?>, failureMessage: "Use <?php if ($_SESSION[$guid]["i18n"]["dateFormat"]=="") { print "dd/mm/yyyy" ; } else { print $_SESSION[$guid]["i18n"]["dateFormat"] ; }?>." } ); 
								</script>
								 <script type="text/javascript">
									$(function() {
										$( "#dateEnd" ).datepicker();
									});
								</script>
							</td>
						</tr>
					
						<tr class='break'>
							<td colspan=2> 
								<h3><?php print __($guid, 'First Aid') ?></h3>
							</td>
						</tr>
						<!-- FIELDS & CONTROLS FOR TYPE -->
						<script type="text/javascript">
							$(document).ready(function(){
								$("#firstAidQualified").change(function(){
									if ($('select.firstAidQualified option:selected').val()=="Y" ) {
										$("#firstAidExpiryRow").slideDown("fast", $("#firstAidExpiryRow").css("display","table-row")); 
									} else {
										$("#firstAidExpiryRow").css("display","none");
									} 
								 });
							});
						</script>
						<tr>
							<td> 
								<b><?php print __($guid, 'First Aid Qualified?') ?></b><br/>
								<span class="emphasis small"></span>
							</td>
							<td class="right">
								<select class="standardWidth" name="firstAidQualified" id="firstAidQualified" class="firstAidQualified">
									<option <?php if ($row["firstAidQualified"]=="") { print "selected" ; } ?> value=""></option>
									<option <?php if ($row["firstAidQualified"]=="Y") { print "selected" ; } ?> value="Y"><?php print __($guid, 'Yes') ?></option>
									<option <?php if ($row["firstAidQualified"]=="N") { print "selected" ; } ?> value="N"><?php print __($guid, 'No') ?></option>
								</select>
							</td>
						</tr>
						<tr id='firstAidExpiryRow' <?php if ($row["firstAidQualified"]!="Y") { print "style='display: none'" ; } ?>>
							<td> 
								<b><?php print __($guid, 'First Aid Expiry') ?></b><br/>
								<span class="emphasis small"><?php print __($guid, "Format:") . " " ; if ($_SESSION[$guid]["i18n"]["dateFormat"]=="") { print "dd/mm/yyyy" ; } else { print $_SESSION[$guid]["i18n"]["dateFormat"] ; } ?></span>
							</td>
							<td class="right">
								<input name="firstAidExpiry" id="firstAidExpiry" maxlength=10 value="<?php print dateConvertBack($guid, $row["firstAidExpiry"]) ?>" type="text" class="standardWidth">
								<script type="text/javascript">
									$(function() {
										$( "#firstAidExpiry" ).datepicker();
									});
								</script>
							</td>
						</tr>
					
						<tr class='break'>
							<td colspan=2> 
								<h3><?php print __($guid, 'Biography') ?></h3>
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Country Of Origin') ?></b><br/>
							</td>
							<td class="right">
								<select name="countryOfOrigin" id="countryOfOrigin" class="standardWidth">
									<?php
									print "<option value=''></option>" ;
									try {
										$dataSelect=array(); 
										$sqlSelect="SELECT printable_name FROM gibbonCountry ORDER BY printable_name" ;
										$resultSelect=$connection2->prepare($sqlSelect);
										$resultSelect->execute($dataSelect);
									}
									catch(PDOException $e) { }
									while ($rowSelect=$resultSelect->fetch()) {
										$selected="" ;
										if ($rowSelect["printable_name"]==$row["countryOfOrigin"]) {
											$selected="selected" ;
										}
										print "<option $selected value='" . $rowSelect["printable_name"] . "'>" . htmlPrep(__($guid, $rowSelect["printable_name"])) . "</option>" ;
									}
									?>				
								</select>
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Qualifications') ?></b><br/>
							</td>
							<td class="right">
								<input name="qualifications" id="qualifications" maxlength=100 value="<?php print htmlPrep($row["qualifications"]) ?>" type="text" class="standardWidth">
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Grouping') ?></b><br/>
								<span class="emphasis small"><?php print __($guid, 'Used to group staff when creating a staff directory.') ?></span>
							</td>
							<td class="right">
								<input name="biographicalGrouping" id="biographicalGrouping" maxlength=100 value="<?php print htmlPrep($row["biographicalGrouping"]) ?>" type="text" class="standardWidth">
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Grouping Priority') ?></b><br/>
								<span style="font-size: 90%"><?php print __($guid, '<i>Higher numbers move teachers up the order within their grouping.') ?></span>
							</td>
							<td class="right">
								<input name="biographicalGroupingPriority" id="biographicalGroupingPriority" maxlength=4 value="<?php print htmlPrep($row["biographicalGroupingPriority"]) ?>" type="text" class="standardWidth">
								<script type="text/javascript">
									var biographicalGroupingPriority=new LiveValidation('biographicalGroupingPriority');
									biographicalGroupingPriority.add(Validate.Numericality);
								</script>
							</td>
						</tr>
						<tr>
							<td> 
								<b><?php print __($guid, 'Biography') ?></b><br/>
							</td>
							<td class="right">
								<textarea name='biography' id='biography' rows=10 style='width: 300px'><?php print htmlPrep($row["biography"]) ?></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<span class="emphasis small">* <?php print __($guid, "denotes a required field") ; ?></span>
							</td>
							<td class="right">
								<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
								<input type="submit" value="<?php print __($guid, "Submit") ; ?>">
							</td>
						</tr>
					</table>
				</form>
				<?php
				if ($highestAction=="Manage Staff_confidential") {
					print "<h3>" . __($guid, 'Contracts') . "</h3>" ;
					try {
						$data=array("gibbonStaffID"=>$gibbonStaffID); 
						$sql="SELECT * FROM gibbonStaffContract WHERE gibbonStaffID=:gibbonStaffID ORDER BY dateStart DESC" ; 
						$result=$connection2->prepare($sql);
						$result->execute($data);
					}
					catch(PDOException $e) { 
						print "<div class='error'>" . $e->getMessage() . "</div>" ; 
					}
		
					print "<div class='linkTop'>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/staff_manage_edit_contract_add.php&gibbonStaffID=$gibbonStaffID&search=$search'>" .  __($guid, 'Add') . "<img style='margin-left: 5px' title='" . __($guid, 'Add') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new.png'/></a>" ;
					print "</div>" ;
	
					if ($result->rowCount()<1) {
						print "<div class='error'>" ;
						print __($guid, "There are no records to display.") ;
						print "</div>" ;
					}
					else {
						print "<table cellspacing='0' style='width: 100%'>" ;
							print "<tr class='head'>" ;
								print "<th>" ;
									print __($guid, "Title") ;
								print "</th>" ;
								print "<th>" ;
									print __($guid, "Status") . "<br/>" ;
								print "</th>" ;
								print "<th>" ;
									print __($guid, "Dates") ;
								print "</th>" ;
								print "<th>" ;
									print __($guid, "Actions") ;
								print "</th>" ;
							print "</tr>" ;
				
							$count=0;
							$rowNum="odd" ;
							while ($row=$result->fetch()) {
								if ($count%2==0) {
									$rowNum="even" ;
								}
								else {
									$rowNum="odd" ;
								}
								$count++ ;
					
								print "<tr class=$rowNum>" ;
									print "<td>" ;
										print $row["title"] ;
									print "</td>" ;
									print "<td>" ;
										print $row["status"] ;
									print "</td>" ;
									print "<td>" ;
										if ($row["dateEnd"]=="") {
											print dateConvertBack($guid, $row["dateStart"]) ;
										}
										else {
											print dateConvertBack($guid, $row["dateStart"]) . " - " . dateConvertBack($guid, $row["dateEnd"]) ;
										}
									print "</td>" ;
									print "<td>" ;
										print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/staff_manage_edit_contract_edit.php&gibbonStaffContractID=" . $row["gibbonStaffContractID"] . "&gibbonStaffID=$gibbonStaffID&search=$search'><img title='" . __($guid, 'Edit') . "' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
									print "</td>" ;
								print "</tr>" ;
							}
						print "</table>" ;
					}
					
					
					print "<h3>" . __($guid, 'Document Storage') . "</h3>" ;
				
				}
			}
		}
	}
}
?>