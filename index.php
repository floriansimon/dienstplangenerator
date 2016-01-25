<?php
	setlocale(LC_ALL, 'de_AT.utf8');

	/*
	V;Chirurgie Vormittag;08:00-12:30
	N;Chirurgie Nachmittag;13:30-19:00
	B;Chirurgie Nacht;19:00-08:00
	P;Gyn Nachmittag;13:00-16:00
	Q;Gyn Nachmittag Lang;13:00-18:00

	*/

	if(isset($_GET['d'])){
		$startDateString = $_GET['d'].'-01';
	}
	else{
		$currentDate = strtotime(date('Y-m').'-01');
		$startDateString = date('Y-m-d', strtotime('+1 month', $currentDate));
	}
	$startDate = DateTime::createFromFormat('Y-m-d', $startDateString);	///Formatertes Start-Datum
	$numberOfDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $startDate->format('m'), $startDate->format('Y'));	///Anzahl der Tage im Monat
	$names = array('Name 1', 
		'Name 2', 
		'Name 3', 
		'Name 4',
		'Name 5', 
		'Name 6', 
		'Name 7');	///Namen der Mitarbeitenden

///Titelleiste generieren
	ob_start();
		echo('<tr><th class="dateColumn">Datum</th>');
		for ($i=0; $i < count($names); $i++) { 
			echo('<th class="nameColumn">'.$names[$i].'</th>');
		}
		echo('</tr>');
	$titleBar = ob_get_contents();
	ob_end_clean();

///.ics-Datei generieren
if(isset($_POST['submitICS'])){
	header('Content-disposition: attachment; filename=BB-'.$startDate->format('Y-m').'.ics');	///Header für .ics-Datei
	header('Content-type: text/calendar');	///Header für .ics-Datei
	echo('BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
X-WR-CALNAME:Dienstplangenerator
PRODID:http://www.symptomatis.ch
X-APPLE-CALENDAR-COLOR:#CCCCCC
X-WR-TIMEZONE:Europe/Vienna
CALSCALE:GREGORIAN
BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+2
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+1
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
');


foreach ($_POST as $key => $value) {
	if($key != 'submitICS'){

		$date = DateTime::createFromFormat('Y-m-d', substr($key, 0, -2));

		$datePlusOne = clone($date);

		$eventFooter = 
'LOCATION:
URL:
END:VEVENT
';

		if (substr($key, -2)=='-P') {
			echo('BEGIN:VEVENT
DTSTAMP: '.gmdate("Ymd\THis\Z").'
UID:'.$date->format("Ymd\THis\Z").'-'.uniqid().'
DTSTART;TZID=Europe/Vienna:'.$date->format("Ymd")."T130000".'
DTEND;TZID=Europe/Vienna:'.$date->format("Ymd")."T160000".'
SUMMARY:'.$value.' (GYN)
DESCRIPTION: GYN Nachmittag Kurz
'.$eventFooter);
		}

		if (substr($key, -2)=='-Q') {
			echo('BEGIN:VEVENT
DTSTAMP: '.gmdate("Ymd\THis\Z").'
UID:'.$date->format("Ymd\THis\Z").'-'.uniqid().'
DTSTART;TZID=Europe/Vienna:'.$date->format("Ymd")."T130000".'
DTEND;TZID=Europe/Vienna:'.$date->format("Ymd")."T180000".'
SUMMARY:'.$value.' (GYN)
DESCRIPTION: GYN Nachmittag Lang
'.$eventFooter);
		}

		if(substr($key, -2)=='-V'){
			echo('BEGIN:VEVENT
DTSTAMP: '.gmdate("Ymd\THis\Z").'
UID:'.$date->format("Ymd\THis\Z").'-'.uniqid().'
DTSTART;TZID=Europe/Vienna:'.$date->format("Ymd")."T080000".'
DTEND;TZID=Europe/Vienna:'.$date->format("Ymd")."T123000".'
SUMMARY:'.$value.' (CHIR)
DESCRIPTION: CHIR Vormittag
'.$eventFooter);
		}

		if (substr($key, -2)=='-N') {
			echo('BEGIN:VEVENT
DTSTAMP: '.gmdate("Ymd\THis\Z").'
UID:'.$date->format("Ymd\THis\Z").'-'.uniqid().'
DTSTART;TZID=Europe/Vienna:'.$date->format("Ymd")."T133000".'
DTEND;TZID=Europe/Vienna:'.$date->format("Ymd")."T193000".'
SUMMARY:'.$value.' (CHIR)
DESCRIPTION: CHIR Nachmittag
'.$eventFooter);
		}

		if (substr($key, -2)=='-B') {
			echo('BEGIN:VEVENT
DTSTAMP:'.gmdate("Ymd")."T".date("His")."Z".'
UID:'.$date->format("Ymd")."T".$date->format("His")."Z".'-'.uniqid().'
DTSTART;TZID=Europe/Vienna:'.$date->format("Ymd")."T190000".'
DTEND;TZID=Europe/Vienna:'.$datePlusOne->modify("+1 day")->format("Ymd")."T080000".'
SUMMARY:'.$value.' (CHIR)
DESCRIPTION: CHIR Nacht
'.$eventFooter);

		}		

	}
}

	echo('END:VCALENDAR');
			exit;
	 }

///.csv-Datei generieren
if(isset($_POST['submitCSV'])){
	header('Content-disposition: attachment; filename=Dienstplan-'.$startDate->format('Y-m').'.csv');	///Header für .csv-Datei
	header('Content-type: text/csv');	///Header für .csv-Datei
}

?>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dienstplan-Export .ics</title>
  <link rel="stylesheet" href="style.css" type="text/css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
</head>

<body>
	<div id="wrapper">

		<h1>Dienstplan-Export .ics</h1>
		<h2>📝 <span>➤</span> 📅</h2>
		<h3>für 
			<select id="dateSelector">
				<?php
					$month = clone($startDate);
					for ($i=0; $i < 5; $i++) {
						echo('<option value="'.$month->format('Y-m').'">'.strftime("%B %Y",strtotime($month->format('Y-m-d'))).'</option>');
						$month->modify('+1 month');
					}
				?>
			</select>

		<?php // echo strftime("%B %Y",strtotime($startDate->format('Y-m-d')))?>
		</h3>
		<form method="post">
			<table>

			<?php
				echo($titleBar);

				$gyn='<span class="gyn">G</span>';
				$chir='<span class="chir">C</span>';

				for ($i=0; $i < $numberOfDaysInMonth; $i++) { 
					if (empty($date)) {
						$date = $startDate;
						if((1 <= $date->format('N')) && ($date->format('N') == 1)){ ////date('N') = 1 = Montag
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-P" name="'.$date->format('Y-m-d-\P').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-P" title="13:00-16:00">'.$gyn.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Abend</label>
									</td>');
							}
							echo('</tr>');
						}
						elseif($date->format('N')==2){
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-V" name="'.$date->format('Y-m-d-\V').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-V" title="08:00-12:30">'.$chir.'Vormittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-Q" name="'.$date->format('Y-m-d-\Q').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-Q" title="13:00-18:00">'.$gyn.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							echo('</tr>');
						}
						elseif($date->format('N')==3){
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							echo('</tr>');
						}
						elseif($date->format('N')==4){
							echo('<tr><td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { 
									echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
						}
						elseif($date->format('N')==5){
							echo('<tr><td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { 
									echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							//echo('</tr><tr><td class="spacerRow" colspan="'.(count($names)+1).'"></td></tr>');
							echo($titleBar);
						}
					}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
					else{
						if((1 <= $date->format('N')) && ($date->format('N') == 1)){
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-P" name="'.$date->format('Y-m-d-\P').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-P" title="13:00-16:00">'.$gyn.'Nachmittag</label>
										
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							echo('</tr>');
						}////'d.m. (D)'
						elseif($date->format('N')==2){
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-V" name="'.$date->format('Y-m-d-\V').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-V" title="08:00-12:30">'.$chir.'Vormittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-Q" name="'.$date->format('Y-m-d-\Q').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-Q" title="13:00-18:00">'.$gyn.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							echo('</tr>');
						}
						elseif($date->format('N')==3){
							echo('<tr>
									<td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { ///<input> Value ausbessern
								echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							echo('</tr>');
						}
						elseif($date->format('N')==4){
							echo('<tr><td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { 
									echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
						}
						elseif($date->format('N')==5){
							echo('<tr><td class="dateColumn">'.$date->format('d.m.').'<span class="weekday">'.strftime("%A",strtotime($date->format('Y-m-d'))).'</span></td>');
							for ($j=0; $j <=count($names)-1 ; $j++) { 
									echo('<td>
										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-N" name="'.$date->format('Y-m-d-\N').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-N" title="13:30-19:30">'.$chir.'Nachmittag</label>

										<input type="checkbox" id="'.$date->format('Y-m-d-').$j.'-B" name="'.$date->format('Y-m-d-\B').'" value="'.$names[$j].'">
										<label for="'.$date->format('Y-m-d-').$j.'-B" title="19:00-08:00">'.$chir.'Nacht</label>
									</td>');
							}
							//echo('</tr><tr><td class="spacerRow" colspan="'.(count($names)+1).'"></td></tr>');
							echo($titleBar);
						}
					}

					$date->modify('+1 day');
				 }
			?>
				<tr>
					<td class="dateColumn">Summe</td>
					<?php
						for ($i=0; $i < count($names); $i++) { 
									echo('<td class="nameColumn"><table>');
									echo('<tr class="sum"><td class="number" id="'.$i.'-P">0</td><td class="label">'.$gyn.'Nachmittag</td></tr>');
									echo('<tr class="sum"><td class="number" id="'.$i.'-Q">0</td><td class="label">'.$gyn.'Nachm.Lang</td></tr>');									
									echo('<tr class="sum"><td class="number" id="'.$i.'-V">0</td><td class="label">'.$chir.'Vormittag</td></tr>');
									echo('<tr class="sum"><td class="number" id="'.$i.'-N">0</td><td class="label">'.$chir.'Nachmittag</td></tr>');
									echo('<tr class="sum"><td class="number" id="'.$i.'-B">0</td><td class="label">'.$chir.'Nacht</td></tr>');
									echo('<tr class="sum"><td class="number" id="'.$i.'-G"><strong>0</strong></td><td class="label"><strong>Gesamt</strong></td></tr>');
									echo('</table></th>');
						}
					?>
				</tr>
			</table>
			<input type="submit" name="submitICS" value=".ics-Datei generieren" />
		</form>
	</div>
	<script type="text/javascript">
<?php ///Arrays Initialisieren: Aus PHP-Skript laden, Dienstkürzel einlesen, IDs initialisieren?>
		<?php
			echo "var Mitarbeiter = ". json_encode($names) . ";\n";
		?>
		var Dienstkürzel = ["V","N","B","P","Q"];
		var IDs = [];

<?php ///Sämtliche mögliche IDs generieren aus Ziffern der Mitarbeiter und Dienstkürzel?>
		for (var i = 0; i < Mitarbeiter.length; i++) {
			for (var j = 0; j < Dienstkürzel.length; j++) {
				IDs.push(i+"-"+Dienstkürzel[j]);
			};
		};

<?php ///Dienste nach Klick auf checkbox neu berechnen?>

		var diensteZählen = function() {
			for (var i = 0; i < IDs.length; i++) {
				var Anzahl = $( "input[id*='"+IDs[i]+"']:checked").length;
				$ ("#"+IDs[i]).text(Anzahl);
				console.log($("#0-V").text());
				$("#0-G").text(parseInt($("#0-V").text())+parseInt($("#0-N").text())+parseInt($("#0-B").text())+parseInt($("#0-P").text())+parseInt($("#0-Q").text()));
				$("#1-G").text(parseInt($("#1-V").text())+parseInt($("#1-N").text())+parseInt($("#1-B").text())+parseInt($("#1-P").text())+parseInt($("#1-Q").text()));
				$("#2-G").text(parseInt($("#2-V").text())+parseInt($("#2-N").text())+parseInt($("#2-B").text())+parseInt($("#2-P").text())+parseInt($("#2-Q").text()));
				$("#3-G").text(parseInt($("#3-V").text())+parseInt($("#3-N").text())+parseInt($("#3-B").text())+parseInt($("#3-P").text())+parseInt($("#3-Q").text()));
				$("#4-G").text(parseInt($("#4-V").text())+parseInt($("#4-N").text())+parseInt($("#4-B").text())+parseInt($("#4-P").text())+parseInt($("#4-Q").text()));
				$("#5-G").text(parseInt($("#5-V").text())+parseInt($("#5-N").text())+parseInt($("#5-B").text())+parseInt($("#5-P").text())+parseInt($("#5-Q").text()));
				$("#6-G").text(parseInt($("#6-V").text())+parseInt($("#6-N").text())+parseInt($("#6-B").text())+parseInt($("#6-P").text())+parseInt($("#6-Q").text()));
			};
		};
<?php ///Dienste nach Klick auf checkbox neu berechnen?>
		$( "input[type=checkbox]").change(function(){
			diensteZählen();
			$("#dateSelector option:selected").text();
		});


		$("#dateSelector").change(function(){

			$( "#dateSelector option:selected" ).each(function() {

				var url = window.location.href;
				
				if(url.indexOf('?d=')>-1){
					newURL = url.replace(/[0-9]{4}-[0-9]{2}/,$(this).val());
					
				}
				else{
					newURL = window.location.href+'?d='+$(this).val();
				}
				window.location = newURL;
				
			});
		});

	</script>
	
</body>
</html>