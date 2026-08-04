<?php
include('inc2.php');
$sessionyear = $cn = $secname = $exam = '';
// include ('../db.php');;
$sessionyear = $sy;
//$sccode=$_POST['sccode'];;
$bg = 'bg-01.jpg';
// $cn = $_GET['cls'];
// $secname = $_GET['sec'];
// $exam = $_GET['exam'];
$etdt = date('Y-m-d H:i:s');

$ordinal = array('1st', '2nd', '3rd', '4th', 'Pre');
$ordinal_sup = array('1<sup>st</sup>', '2<sup>nd</sup>', '3<sup>rd</sup>', '4<sup>th</sup>', 'Pre');


if (isset($_GET["slot"])) {
	$slot = $_GET["slot"];
} else {
	$slot = 'School';
}

if (isset($_GET['sy'])) {
	$sessionyear = $_GET['sy'];
}
if (isset($_GET['cls'])) {
	$cn = $_GET['cls'];
}
if (isset($_GET['sec'])) {
	$secname = $_GET['sec'];
}
if (isset($_GET['exam'])) {
	$exam = $_GET['exam'];
}
$color = $_GET['clr'] ?? 0;


$hmark = array();
$sql0x2 = "SELECT subject, max(markobt) as kkk from stmark where sccode='$sccode' and sessionyear = '$sessionyear' and exam='$exam' and classname='$cn'  group by subject";
// echo $sql0x2;
$result0x2g = $conn->query($sql0x2);
if ($result0x2g->num_rows > 0) {
	while ($row0x2 = $result0x2g->fetch_assoc()) {
		$hmark[] = $row0x2;
	}
}

$hmarkex = array();
$sql0x2 = "SELECT sub_code_1, sub_code_2, max(sub_1_total) as comb1, max(sub_2_total) as comb2 from tabulatingsheetex where sccode='$sccode' and sessionyear = '$sessionyear' and exam='$exam' and classname='$cn'  ";
// echo $sql0x2;
$result0x2g = $conn->query($sql0x2);
if ($result0x2g->num_rows > 0) {
	while ($row0x2 = $result0x2g->fetch_assoc()) {
		$hmarkex[] = $row0x2;
	}
}


$hmarktot = array();
$sql0x2 = "SELECT max(totalmarks) as max FROM tabulatingsheet where exam='$exam' and classname='$cn'   and sessionyear = '$sessionyear'  and sccode='$sccode' ";
// echo $sql0x2;
$result0x2ghh = $conn->query($sql0x2);
if ($result0x2ghh->num_rows > 0) {
	while ($row0x2 = $result0x2ghh->fetch_assoc()) {
		$hmarktot = $row0x2['max'];
	}
}


// echo $slot;
$sql0x2 = "SELECT * from slots where sccode='$sccode' and slotname = '$slot' ";
// echo $sql0x2;
$result0x2gdd = $conn->query($sql0x2);
if ($result0x2gdd->num_rows > 0) {
	while ($row0x2 = $result0x2gdd->fetch_assoc()) {
		$cus_report = $row0x2['cus_report'];
		$engname = $row0x2['trans_name_eng'];
		$benname = $row0x2['trans_name_ben'];
		$parents = $row0x2['parents'];
	}
}

// echo '...' . $cus_report . '///';
if ($exam == 'Grand') {
	$cus_report = 'grand-01';
}

if (strlen($cus_report) > 0 && $sessionyear != '' && $cn != '' && $secname != '' && $exam != '') {

	$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
	$current_url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$current_url = str_replace('progress-preport', 'custom-preport/progress-preport-' . $cus_report, $current_url);

	// echo 're-direct';
	header("Location: $current_url");
	exit();
}

// echo $sessionyear . '/' . $cn . '/' . $secname . '/' . $exam;

if ($sessionyear == '' || $cn == '' || $secname == '' || $exam == '') {
	echo "<script>window.location.href = 'result-repo-select.php';</script>";
} else {
	// $heading = $_POST['heading'];
	// $back = $_POST['back'];
	// $sign = $_POST['sign'];
	// $echo $heading;
	$heading = 'true';
	$back = 'true';
	$sign = 'true';

	?>

	<html>

	<body style="margin:0; font-family:Segoe UI;">

		<style>
			#trans tr,
			#trans td {
				border: 1px solid black;
				padding: 2px;
			}

			#gsys tr,
			#gsys td {
				border: 1px solid black;
			}
		</style>

		<button class="noprint"
			style=" margin:15px; margin-left:1000px; position:fixed; z-index:99; float:right; padding:5px 20px; border-radius:10px; border:1px solid seagreen; color: white; background:seagreen;"
			onclick="goo();">Back To Home</button>

		<?php
		$ctid = 0;
		$cteacher = '';
		$sql0x2 = "SELECT * from areas where areaname='$cn' and subarea = '$secname' and sessionyear  LIKE '%$sy%'  and user='$rootuser'";
		$result0x2 = $conn->query($sql0x2);
		if ($result0x2->num_rows > 0) {
			while ($row0x2 = $result0x2->fetch_assoc()) {
				$ctid = $row0x2["classteacher"];
			}
		}


		$sql0x2vr = "SELECT * from teacher where sccode='$sccode' and tid='$ctid'";
		$result0x2vr = $conn->query($sql0x2vr);
		if ($result0x2vr->num_rows > 0) {
			while ($row0x2vr = $result0x2vr->fetch_assoc()) {
				$cteacher = $row0x2vr["tname"];
			}
		}


		$sql0x2v = "SELECT * from teacher where sccode='$sccode' and (position='Head Teacher' or position='Principal')";
		$result0x2v = $conn->query($sql0x2v);
		if ($result0x2v->num_rows > 0) {
			while ($row0x2v = $result0x2v->fetch_assoc()) {
				$hname = $row0x2v["tname"];
				$hpos = $row0x2v["position"];
				$htid = $row0x2v["tid"];
			}
		}






		$sql00011a = "SELECT * FROM users where eiin='$sccode' and user_level='100' ";
		$result00011a = $conn->query($sql00011a);
		if ($result00011a->num_rows > 0) {
			while ($row00011a = $result00011a->fetch_assoc()) {
				//$einame=$row00011a["einame"]; $eiaddress=$row00011a["eiaddress"]; $eicontact=$row00011a["eicontact"]; 
	


				$fullname = $row00011a["fullname"];
				$user_group = $row00011a["user_group"];

			}
		}
		$einame = $scname;
		$eiaddress = $scadd1 . ', ' . $ps . ', ' . $dist;
		if ($sccode == 105671) {
			$eiaddress = 'Homna, Cumilla';
		}


		$eicontact = $mobile;


		$tsheet = array();
		$sql000111 = "SELECT * FROM tabulatingsheet where exam='$exam' and classname='$cn'  and sectionname = '$secname' and sessionyear = '$sessionyear' and slot='$slot' and sccode='$sccode' ";
		$result000111 = $conn->query($sql000111);
		if ($result000111->num_rows > 0) {
			while ($row000111 = $result000111->fetch_assoc()) {
				$tsheet[] = $row000111;
			}
		}
		// echo var_dump($tsheet[0]);
	


		$tsheetex = array();
		$sql000111 = "SELECT * FROM tabulatingsheetex where exam='$exam' and classname='$cn'  and sectionname = '$secname' and sessionyear = '$sessionyear'  and slot='$slot' and sccode='$sccode' ";
		$result000111ex = $conn->query($sql000111);
		if ($result000111ex->num_rows > 0) {
			while ($row000111 = $result000111ex->fetch_assoc()) {
				$tsheetex[] = $row000111;
			}
		}

		$subnamelist = array();
		$sql000 = "SELECT * FROM subjects where sccategory='$sctype' ";
		$result000 = $conn->query($sql000);
		if ($result000->num_rows > 0) {
			while ($row000 = $result000->fetch_assoc()) {
				$subnamelist[] = $row000;
			}
		}

		$subsetup = array();
		$sql000t = "SELECT * FROM subsetup where sccode='$sccode' and classname='$cn' and sectionname='$secname' and slot='$slot' and sessionyear='$sessionyear'  ";
		$result000t = $conn->query($sql000t);
		if ($result000t->num_rows > 0) {
			while ($row000t = $result000t->fetch_assoc()) {
				$subsetup[] = $row000t;
			}
		}


		$rpubdt = $td;
		$sql000111 = "SELECT * FROM examlist where examtitle='$exam' and sccode='$sccode'   and sessionyear = '$sessionyear' and slot='$slot' ";
		$result000111t = $conn->query($sql000111);
		if ($result000111t->num_rows > 0) {
			while ($row000111 = $result000111t->fetch_assoc()) {
				$rpubdt = $row000111['result_publish'];
			}
		}

		// ********************************************************************************************
		// ********************************************************************************************
		// ********************************************************************************************
		// ********************************************************************************************
		// ********************************************************************************************
		// ********************************************************************************************
	

		?>

		<style>
			.page-break {
				page-break-before: always;
			}

			.page-breaks:last-child {
				page-break-before: auto;
				/* or omit this line, since it's default */
			}
		</style>
		<?php




		$sql0001 = "SELECT * FROM sessioninfo where sccode='$sccode' and classname='$cn'  and sectionname='$secname' and sessionyear = '$sessionyear'  order by rollno";
		// echo $sql0001;
		$result0001 = $conn->query($sql0001);
		$num = mysqli_num_rows($result0001);
		$run = 0;

		if ($result0001->num_rows > 0) {
			while ($row0001 = $result0001->fetch_assoc()) {
				$run++;
				if ($num == $run) {
					$lastpad = '0mm';
				} else {
					$lastpad = '20mm';
				}
				$rollno = $row0001["rollno"];



				$stid = $row0001["stid"];
				$fourth = $row0001["fourth_subject"];


				$sql00011 = "SELECT * FROM students where stid='$stid' ";
				$result00011 = $conn->query($sql00011);
				if ($result00011->num_rows > 0) {
					while ($row00011 = $result00011->fetch_assoc()) {
						$stnameeng = $row00011["stnameeng"];
						$stnameben = $row00011["stnameben"];
						$fname = $row00011["fname"];
						$mname = $row00011["mname"];
						$gender = $row00011["gender"];
						$religion = $row00011["religion"];
						$tcno = $row00011["preins"];

						if ($parents == 'DOSO') {
							if ($gender == 'Boy') {
								$lingo = 'S/O';
							} else {
								$lingo = 'D/O';
							}
							$parent_line = $lingo . ' ' . $fname . ' & ' . $mname;
						} else {
							$parent_line = 'F : ' . $fname . ' & M : ' . $mname;
						}



					}
				}

				// echo '/' . $stid . '/';
				$stmark = array();
				$stmarkex = array();
				$ind = array_search($stid, array_column($tsheet, 'stid'));
				$exind = array_search($stid, array_column($tsheetex, 'stid'));

				// echo '~~~~' . $ind . '~~~~<br>';
	
				$stmark[] = $tsheet[$ind];
				$stmarkex[] = $tsheetex[$exind];
				// echo var_dump($stmark);
	

				// $sql000111 = "SELECT * FROM tabulatingsheet where exam='$exam' and classname='$cn'  and sectionname = '$secname' and sessionyear = '$sessionyear' and stid='$stid' ";
				// $result000111 = $conn->query($sql000111);
				// if ($result000111->num_rows > 0) {
				// 	while ($row000111 = $result000111->fetch_assoc()) {
				$sub_1 = $stmark[0]["sub_1"];
				$sub_1_sub = $stmark[0]["sub_1_sub"];
				$sub_1_obj = $stmark[0]["sub_1_obj"];
				$sub_1_pra = $stmark[0]["sub_1_pra"];
				$sub_1_ca = $stmark[0]["sub_1_ca"];
				$sub_1_total = $stmark[0]["sub_1_total"];
				$sub_1_gp = $stmark[0]["sub_1_gp"];
				$sub_1_gl = $stmark[0]["sub_1_gl"];
				$sub_2 = $stmark[0]["sub_2"];
				$sub_2_sub = $stmark[0]["sub_2_sub"];
				$sub_2_obj = $stmark[0]["sub_2_obj"];
				$sub_2_pra = $stmark[0]["sub_2_pra"];
				$sub_2_ca = $stmark[0]["sub_2_ca"];
				$sub_2_total = $stmark[0]["sub_2_total"];
				$sub_2_gp = $stmark[0]["sub_2_gp"];
				$sub_2_gl = $stmark[0]["sub_2_gl"];
				$sub_3 = $stmark[0]["sub_3"];
				$sub_3_sub = $stmark[0]["sub_3_sub"];
				$sub_3_obj = $stmark[0]["sub_3_obj"];
				$sub_3_pra = $stmark[0]["sub_3_pra"];
				$sub_3_ca = $stmark[0]["sub_3_ca"];
				$sub_3_total = $stmark[0]["sub_3_total"];
				$sub_3_gp = $stmark[0]["sub_3_gp"];
				$sub_3_gl = $stmark[0]["sub_3_gl"];
				$sub_4 = $stmark[0]["sub_4"];
				$sub_4_sub = $stmark[0]["sub_4_sub"];
				$sub_4_obj = $stmark[0]["sub_4_obj"];
				$sub_4_pra = $stmark[0]["sub_4_pra"];
				$sub_4_ca = $stmark[0]["sub_4_ca"];
				$sub_4_total = $stmark[0]["sub_4_total"];
				$sub_4_gp = $stmark[0]["sub_4_gp"];
				$sub_4_gl = $stmark[0]["sub_4_gl"];
				$sub_5 = $stmark[0]["sub_5"];
				$sub_5_sub = $stmark[0]["sub_5_sub"];
				$sub_5_obj = $stmark[0]["sub_5_obj"];
				$sub_5_pra = $stmark[0]["sub_5_pra"];
				$sub_5_ca = $stmark[0]["sub_5_ca"];
				$sub_5_total = $stmark[0]["sub_5_total"];
				$sub_5_gp = $stmark[0]["sub_5_gp"];
				$sub_5_gl = $stmark[0]["sub_5_gl"];
				$sub_6 = $stmark[0]["sub_6"];
				$sub_6_sub = $stmark[0]["sub_6_sub"];
				$sub_6_obj = $stmark[0]["sub_6_obj"];
				$sub_6_pra = $stmark[0]["sub_6_pra"];
				$sub_6_ca = $stmark[0]["sub_6_ca"];
				$sub_6_total = $stmark[0]["sub_6_total"];
				$sub_6_gp = $stmark[0]["sub_6_gp"];
				$sub_6_gl = $stmark[0]["sub_6_gl"];
				$sub_7 = $stmark[0]["sub_7"];
				$sub_7_sub = $stmark[0]["sub_7_sub"];
				$sub_7_obj = $stmark[0]["sub_7_obj"];
				$sub_7_pra = $stmark[0]["sub_7_pra"];
				$sub_7_ca = $stmark[0]["sub_7_ca"];
				$sub_7_total = $stmark[0]["sub_7_total"];
				$sub_7_gp = $stmark[0]["sub_7_gp"];
				$sub_7_gl = $stmark[0]["sub_7_gl"];
				$sub_8 = $stmark[0]["sub_8"];
				$sub_8_sub = $stmark[0]["sub_8_sub"];
				$sub_8_obj = $stmark[0]["sub_8_obj"];
				$sub_8_pra = $stmark[0]["sub_8_pra"];
				$sub_8_ca = $stmark[0]["sub_8_ca"];
				$sub_8_total = $stmark[0]["sub_8_total"];
				$sub_8_gp = $stmark[0]["sub_8_gp"];
				$sub_8_gl = $stmark[0]["sub_8_gl"];
				$sub_9 = $stmark[0]["sub_9"];
				$sub_9_sub = $stmark[0]["sub_9_sub"];
				$sub_9_obj = $stmark[0]["sub_9_obj"];
				$sub_9_pra = $stmark[0]["sub_9_pra"];
				$sub_9_ca = $stmark[0]["sub_9_ca"];
				$sub_9_total = $stmark[0]["sub_9_total"];
				$sub_9_gp = $stmark[0]["sub_9_gp"];
				$sub_9_gl = $stmark[0]["sub_9_gl"];

				$sub_10 = $stmark[0]["sub_10"];
				$sub_10_sub = $stmark[0]["sub_10_sub"];
				$sub_10_obj = $stmark[0]["sub_10_obj"];
				$sub_10_pra = $stmark[0]["sub_10_pra"];
				$sub_10_ca = $stmark[0]["sub_10_ca"];
				$sub_10_total = $stmark[0]["sub_10_total"];
				$sub_10_gp = $stmark[0]["sub_10_gp"];
				$sub_10_gl = $stmark[0]["sub_10_gl"];

				$sub_11 = $stmark[0]["sub_11"];
				$sub_11_sub = $stmark[0]["sub_11_sub"];
				$sub_11_obj = $stmark[0]["sub_11_obj"];
				$sub_11_pra = $stmark[0]["sub_11_pra"];
				$sub_11_ca = $stmark[0]["sub_11_ca"];
				$sub_11_total = $stmark[0]["sub_11_total"];
				$sub_11_gp = $stmark[0]["sub_11_gp"];
				$sub_11_gl = $stmark[0]["sub_11_gl"];

				$sub_12 = $stmark[0]["sub_12"];
				$sub_12_sub = $stmark[0]["sub_12_sub"];
				$sub_12_obj = $stmark[0]["sub_12_obj"];
				$sub_12_pra = $stmark[0]["sub_12_pra"];
				$sub_12_ca = $stmark[0]["sub_12_ca"];
				$sub_12_total = $stmark[0]["sub_12_total"];
				$sub_12_gp = $stmark[0]["sub_12_gp"];
				$sub_12_gl = $stmark[0]["sub_12_gl"];


				$sub_13 = $stmark[0]["sub_13"];
				$sub_13_sub = $stmark[0]["sub_13_sub"];
				$sub_13_obj = $stmark[0]["sub_13_obj"];
				$sub_13_pra = $stmark[0]["sub_13_pra"];
				$sub_13_ca = $stmark[0]["sub_13_ca"];
				$sub_13_total = $stmark[0]["sub_13_total"];
				$sub_13_gp = $stmark[0]["sub_13_gp"];
				$sub_13_gl = $stmark[0]["sub_13_gl"];

				$sub_14 = $stmark[0]["sub_14"];
				$sub_14_sub = $stmark[0]["sub_14_sub"];
				$sub_14_obj = $stmark[0]["sub_14_obj"];
				$sub_14_pra = $stmark[0]["sub_14_pra"];
				$sub_14_ca = $stmark[0]["sub_14_ca"];
				$sub_14_total = $stmark[0]["sub_14_total"];
				$sub_14_gp = $stmark[0]["sub_14_gp"];
				$sub_14_gl = $stmark[0]["sub_14_gl"];

				$sub_15 = $stmark[0]["sub_15"];
				$sub_15_sub = $stmark[0]["sub_15_sub"];
				$sub_15_obj = $stmark[0]["sub_15_obj"];
				$sub_15_pra = $stmark[0]["sub_15_pra"];
				$sub_15_ca = $stmark[0]["sub_15_ca"];
				$sub_15_total = $stmark[0]["sub_15_total"];
				$sub_15_gp = $stmark[0]["sub_15_gp"];
				$sub_15_gl = $stmark[0]["sub_15_gl"];

				$ben_sub = $stmark[0]["ben_sub"];
				$ben_obj = $stmark[0]["ben_obj"];
				$ben_pra = $stmark[0]["ben_pra"];
				$ben_ca = $stmark[0]["ben_ca"];
				$ben_total = $stmark[0]["ben_total"];
				$ben_gp = $stmark[0]["ben_gp"];
				$ben_gl = $stmark[0]["ben_gl"];



				$eng_sub = $stmark[0]["eng_sub"];
				$eng_obj = $stmark[0]["eng_obj"];
				$eng_pra = $stmark[0]["eng_pra"];
				$eng_ca = $stmark[0]["eng_ca"];
				$eng_total = $stmark[0]["eng_total"];
				$eng_gp = $stmark[0]["eng_gp"];
				$eng_gl = $stmark[0]["eng_gl"];

				$totalmarks = $stmark[0]["totalmarks"];
				$fullmarks = $stmark[0]["full_marks"];
				$avgrate = $stmark[0]["avgrate"];
				$gpa = $stmark[0]["gpa"];
				$gla = $stmark[0]["gla"];
				$meritplace = $stmark[0]["meritplace"];
				$mcomb = $stmark[0]["meritplacecomb"];
				$totalfail = $stmark[0]["totalfail"];
				$fail_sub_list = $stmark[0]["failsub"];

				$prevexam = $stmark[0]["prevexam"];
				$thisexam = $stmark[0]["thisexam"];
				$thisexam = $stmark[0]["thisexam"];
				$aicomm = $stmark[0]["ai_comm"];



				// 	}
				// }
				//echo $sql000111;
				?>
				<?php if ($run > 1) {
					echo '<div style=" height:0px;;"></div>';
					$pg_brk = 'page-break';
				} else {
					$pg_brk = ' ';
				}
				?>


				<div class="<?php echo $pg_brk; ?>" style="border:0; background-image: url('../assets/transcript/<?php echo $bg; ?>'); height:290mm;background-size:220mm; <?php if ($totalmarks <= 0) {
						  echo ' display:none;';
					  } ?>">
					<div style="padding:8mm 8mm;">

						<table valign="top" style="border:0; border-collapse:collapse; width:193mm; height:270mm; ">
							<tr>

								<td valign="top">

									<table style="width:100%;">


										<tr>

											<td>
												<?php
												//************************************************************************************************* HEADING..........................................
												if ($heading == 'true') { ?>
													<div style="height:32mm; text-align:center;">

														<img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" height="65px"
															style="background-image: url('/images/no-image.png') ;  padding:2px 0 3px 0; margin-bottom:3px;"
															onerror="this.onerror=null; this.src='http://www.eimbox.com/images/no-image.png';" />
														<br>
														<span
															style="font-family:segoe ui; font-size:24px; font-weight:bold;"><?php echo $einame; ?></span><br>
														<?php echo $eiaddress . "<br>Contact : " . $eicontact; ?>

													</div>
													<?php
												} else {
													?>
													<div style="height:45mm">

													</div>
												<?php }
												$examt = $exam;
												?>

											</td>

										</tr>
									</table>




									<div style="text-align:center; margin:30px 0 5px;">
										<img src="assets/transcript/progress_report_text_03.png" style="width:250px;" />
										<b>
											<div
												style="color: #159159 !important; line-height:20px; font-size:20px; font-weight:700;">
												<?php echo str_replace($ordinal, $ordinal_sup, $examt) . ' Examination - ' . $sessionyear; ?>
											</div>
										</b>
									</div>


									<table>
										<tr>
											<td width="600px">
												<span style="font-size:10px;">Student's Info</span><br>
												<b>
													<?php if ($engname == 1) {
														?>
														<span
															style="font-size:20px; color: #9900cc !important"><?php echo $stnameeng ?></span>
														<?php
													}
													if ($benname == 1 && $engname == 1) {
														echo '<br>';
													}
													if ($benname == 1) {
														?>
														<span
															style="color:seagreen; font-family:sutonnyOMJ; font-size:24px; padding-top:5px;">
															<?php echo $stnameben; ?></span>
														<?php
													} ?>



												</b><br>
												<span style="font-size:12px; "><?php echo $parent_line; ?></span><br>

												<span style="font-size:12px; font-weight:bold;">ID # <?php echo $stid; ?></span>
												<br><br>


												Class : <b><?php echo $cn; ?></b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												Group : <b><?php echo $secname; ?></b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												Roll No. : <b>
													<?php

													//echo $sccode . $cn.$secname;
										
													echo $rollno;
													//echo $tcno;
													?>
												</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

											</td>

											<td width="120px" align="right">
												<br>
												<img src="https://eimbox.com/students/<?php echo $stid; ?>.jpg" width="100px"
													style="background-image: url('/images/no-image.png') ; border:1px solid black ; max-height:115px;  padding:3px; margin-bottom:10px;"
													onerror="this.onerror=null; this.src='http://www.eimbox.com/images/no-image.png';" />
											</td>
											<td width="10px"></td>
											<td width="120px">
												<center><span style="font-size:10px">Grading System</span></center>
												<table id="gsys" border="1" width="100%"
													style="color:#1c702c; border-collapse:collapse;">
													<tr>
														<td colspan="2" align="center" style="font-size:9px; font-weight:bold;">
															Grade</td>
														<td align="center" style="font-size:9px; font-weight:bold;">Marks Range</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">A+</td>
														<td style="font-size:9px; text-align:center;">5.00</td>
														<td style="font-size:9px; text-align:center;">80+</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">A</td>
														<td style="font-size:9px; text-align:center;">4.00</td>
														<td style="font-size:9px; text-align:center;">70 - 79</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">A-</td>
														<td style="font-size:9px; text-align:center;">3.50</td>
														<td style="font-size:9px; text-align:center;">60 - 69</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">B</td>
														<td style="font-size:9px; text-align:center;">3.00</td>
														<td style="font-size:9px; text-align:center;">50 - 59</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">C</td>
														<td style="font-size:9px; text-align:center;">2.00</td>
														<td style="font-size:9px; text-align:center;">40 - 49</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">D</td>
														<td style="font-size:9px; text-align:center;">1.00</td>
														<td style="font-size:9px; text-align:center;">33 - 39</td>
													</tr>
													<tr>
														<td style="font-size:9px; text-align:center;">F</td>
														<td style="font-size:9px; text-align:center;">0.00</td>
														<td style="font-size:9px; text-align:center;">0 - 32</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>

									<style>
										.mag {
											text-align: center;
											font-weight: bold;
											padding: 7px 0px;
										}
									</style>

									<table id="trans" border="1" width="100%"
										style="margin-top:2px; border-collapse:collapse; print-color-adjust: exact; border:1px black solid !important; font-family:segoe UI;">
										<tr height="10px">
											<td width="300px" class="mag" rowspan="2">
												<center>Subjects

												</center>
											</td>
											<td width="300px" class="mag" rowspan="2" colspan="2">
												<center> <small>Full & Highest Mark</small> </center>
											</td>
											<td width="50px" class="mag" colspan="3">Marks Obtained</td>
											<td width="50px" class="mag" colspan="3">Calculation</td>
											<td width="200px" class="mag" rowspan="2">
												<center>Result</center>
											</td>
										</tr>
										<tr height="12px">




											<td width="50px" class="mag">SUB</td>
											<td width="50px" class="mag">OBJ</td>
											<td width="50px" class="mag">PRA</td>

											<td width="50px" class="mag">Total</td>
											<td width="50px" class="mag">GP</td>
											<td width="50px" class="mag">GL</td>





										</tr>


										<!-- Subject LOOP............... -->

										<?php
										$sobbisoy = $stmark[0]['allsubject'];
										$sobbisoy = str_replace('/', '.', $sobbisoy);
										// echo $sobbisoy;
										$sob = explode('.', $sobbisoy);
										$bulb = 0;
										$char = 0;
										$ekex = 0;
										foreach ($sob as $ek) {
											if ($ekex == $ek) {
												continue;
											}
											$ekex = $ek;

											$flag = 0;
											// echo $ek; 
											$jgl = 'F';
											if ($ek > 100) {
												// echo $ek;
												$subnameind = array_search($ek, array_column($subnamelist, 'subcode'));
												$subs = $subnamelist[$subnameind]['subject'];
												$col2 = array_search($ek, array_column($subsetup, 'subject'));
												// $col = array_search($ek, $stmark[0]);
							
												$keys = array_keys($stmark[0], $ek, true);

												$key = null;

												if (!empty($keys)) {
													if ($keys[0] === 'rollno') {
														$key = $keys[1] ?? null;
													} else {
														$key = $keys[0];
													}
												}
												$col = $key;

												if ($ek < 1000) {
													$js = $stmark[0][$col . '_sub'];
													$jo = $stmark[0][$col . '_obj'];
													$jp = $stmark[0][$col . '_pra'];
													$jc = $stmark[0][$col . '_ca'];
													$jt = $stmark[0][$col . '_total'];
													$jgp = $stmark[0][$col . '_gp'];
													$jgl = $stmark[0][$col . '_gl'];
												} else if ($ek > 1000) {
													$col = array_search($ek, $stmarkex[0]);
													$col = str_replace('_code', '', $col);
													$colf = str_replace('_code', 'fm', $col);
													$colf = str_replace('sub_', 'sub_fm_', $colf);
													// echo $colf;
													$subsfm = $stmarkex[0][$colf];

													$js = $stmarkex[0][$col . '_sub'];
													$jo = $stmarkex[0][$col . '_obj'];
													$jp = $stmarkex[0][$col . '_pra'];
													$jc = $stmarkex[0][$col . '_ca'];
													$jt = $stmarkex[0][$col . '_total'];
													$jgp = $stmarkex[0][$col . '_gp'];
													$jgl = $stmarkex[0][$col . '_gl'];
												} else {
													$js = '';
													$jo = '';
													$jp = '';
													$jc = '';
													$jt = '';
													$jgp = '';
													$jgl = '';
													$flag = 1;
												}

												if ($ek == 906 && $jt == 0) {
													continue;
												} else if ($ek == 907 && $jt == 0) {
													continue;
												}


												// echo $ek . $col2 . '////';
												// echo var_dump($stmarkex[0]);
												// echo '<br><br><br>';
							

												$hmind = array_search($ek, array_column($hmark, 'subject'));
												$hmmark = $hmark[$hmind]['kkk'] ?? 0;


												if ($ek == $hmarkex[0]['sub_code_1'])
													$hmmark = $hmarkex[0]['comb1'];
												if ($ek == $hmarkex[0]['sub_code_2'])
													$hmmark = $hmarkex[0]['comb2'];


												if ($ek < 1000) {
													$addi = $stmarkex[0]['fourth_total'];
													$addgp = $stmarkex[0]['add_gp'];
													$subsfm = $subsetup[$col2]['fullmarks'];

													$setup_sub = $subsetup[$col2]['subj'];
													$setup_obj = $subsetup[$col2]['obj'];
													$setup_pra = $subsetup[$col2]['pra'];

												}

												if ($jgp == 5) {
													$clr = 'seagreen';
												} else if ($jgp == 0) {
													$clr = 'red';
												} else {
													$clr = 'black';
												}
												$sub_dis = 1;
												if ($religion == 'Islam' && $ek == 112)
													$sub_dis = 0;
												if ($religion == 'Hindu' && $ek == 111)
													$sub_dis = 0;

												if ($jt == 0)
													$sub_dis = 0;


												if ($sub_dis == 1) {
													$cspan = 9;
													?>
													<tr style="color: <?php echo $clr; ?>; font-size:15px; <?php if ($ek > 1000) {
														   echo 'font-weight:700;';
													   } ?>;">

														<td style="padding:1px 4px 1px 10px; <?php if ($flag == 1) {
															echo ' font-weight:bold;';
														} ?>" <?php if ($flag == 1) {
															 echo ' colspan="' . $cspan . '"';
														 } ?>>


															<?php
															// echo $subs . $ek . ' <b>[' . $subsfm . ']</b>';
									
															if ($stid == '1056761407' || $stid == '1056761403') {
																if ($ek == 126) {
																	$subs = 'Home Science';
																}
															}





															echo $subs;
															?>
														</td>

														<?php if ($flag == 0) { ?>
															<td style="text-align:center; padding: 3px 5px;"><span
																	style="color:<?php echo $cl; ?> !important;"><?php echo number_format($subsfm, 0); ?></span>
															</td>

															<td style="text-align:center;  padding: 3px 5px; border-right:2px solid black;"><span
																	style="color:<?php echo $cl; ?> !important;"><?php echo number_format($hmmark, 0); ?></span>
															</td>


															<td style="text-align:center"><?php if ($setup_sub > 0) {
																echo $js;
															} else {
																echo '-';
															} ?>
															</td>
															<td style="text-align:center"><?php if ($setup_obj > 0) {
																echo $jo;
															} else {
																echo '-';
															} ?>
															</td>
															<td style="text-align:center"><?php if ($setup_pra > 0) {
																echo $jp;
															} else {
																echo '-';
															} ?>
															</td>


															<!-- <td style="text-align:center">
														<?php
														// echo $jc; 
														?></td> -->


															<td style="text-align:center; "><span
																	style="color:<?php echo $cl; ?> !important;"><?php echo $jt; ?></span>
															</td>
															<td style="text-align:center; "><span style="color:<?php echo $cl; ?> !important;"><?php echo $jgp;
																if ($char == 1) {
																	if($jgp > 2) {
																		$addgp = $jgp - 2;
																	} else {
																		$addgp = 0;
																	}
																	echo '<br><b>' .number_format($addgp, 2) . '</b>';
																} ?></span>
															</td>
															<td style="text-align:center; "><span style="color:<?php echo $cl; ?> !important;"><?php if ($jgl == 0)
																	$jgl = 'F';
																echo $jgl; ?></span>
															</td>

															<?php

														}
														$sql00011ax = "SELECT count(*) as tsub FROM subsetup where sccode='$sccode' and classname='$cn' and sectionname='$secname'  and sessionyear='$sessionyear' ";
														$result00011axd = $conn->query($sql00011ax);
														if ($result00011axd->num_rows > 0) {
															while ($row00011ax = $result00011axd->fetch_assoc()) {
																$rowspan = $row00011ax["tsub"];
															}
														}


														$sql00011ax = "SELECT * FROM gpa where gl='$gla'  ";
														$result00011ax = $conn->query($sql00011ax);
														if ($result00011ax->num_rows > 0) {
															while ($row00011ax = $result00011ax->fetch_assoc()) {
																$remark = $row00011ax["remark"];
																$colorcode = $row00011ax["colorcode"];
															}
														}
														$clc = '#' . $colorcode;
														//echo $clc;
														//if($gpa == 0){$clc = 'red';} else if ($gpa == 5){$clc = 'green';} else {$clc = 'black';}
								
														if ($ek == 1000) {
															$char = 1;
														}
														?>

														<style>
															.ttb tr td {
																border: 1px solid gray;
															}

															.desc {
																font-size: 11px;
																padding: 2px;
															}

															.mark {
																font-size: 15px;
																padding: 2px;
															}

															.mark b,
															.desc b {
																color: #990000;
															}
														</style>

														<?php
														if ($bulb == 0) {
															?>
															<td rowspan="20" style="text-align:center;">

																<small>Total Marks<br>Obtained</small><br>
																<span style="font-size:22px; font-weight:bold; color:#cc4400 !important;">
																	<?php
																	// $avgrate = number_format(($totalmarks + $addi) * 100 / $fullmarks, 2);
																	// echo $totalmarks + $addi;
																	echo $totalmarks;
																	?>
																</span>
																<br><span style="font-size:10px; ">Out Of
																	<?php echo $fullmarks; ?></span>

																<br>Average Achievement<br>
																<b><?php echo number_format($avgrate, 2) . '%'; ?></b>
																<hr width="80%" style="border:1px solid black">



																Obtained Highest Marks <br><b><?php echo $hmarktot; ?></b>
																<hr width="80%" style="border:1px solid black">
																<!--
														Total Working Days<br>
														........... / 100 days.</b><hr width="80%" style="border:1px solid black">
														-->
																<?php if ($sccode == 105673) {
																	?>
																	<span style="font-size:11px;"><?= $stmark[0]['ai_comm'] ?></span>
																	<br>
																	<span style="font-size:15px; font-weight:700;"><?= $stmark[0]['prevexam'] ?></span>
																	<br>
																	<span style="font-size:12px; font-weight:500;">Grand Result</span>
																	<?php
																}
																?>
																<br>

																Result<br>
																<b><span
																		style="color:<?php echo $clc; ?> !important;"><?php echo $gpa . ' : ' . $gla; ?></span></b><br>
																<strong><span
																		style="color:<?php echo $clc; ?> !important;"><?php echo $remark; ?></span></strong>
																<?php
																if ($gpa == 0) {

																	echo "<br>Failed in " . $totalfail . " subject(s) <br>";
																	echo "<span style='color:red; font-weight:bold; font-size:9px;'>" . $fail_sub_list . "</span>";
																}
																?>


																<hr width="80%" style="border:1px solid black">

																<?php
																if ($gpa == 0) {
																	$ppp = 'Place';
																} else {
																	$ppp = 'Merit Place';
																}
																echo $ppp;
																?>
																<br>
																<span
																	style="color:blue !important; font-size:20px; font-weight:bold;"><?php echo $meritplace; ?></span>

																<br>

																<span
																	class="font-size:14px; padding:4px 10px; border:1px solid gray; border-radius:5px;"><small>Combined
																		Merit Place</small><br><b><?php echo $mcomb; ?></b></span>

															</td>
															<?php
															$bulb++;
														}
														?>



													</tr>

													<?php
												}
											}
										}
										?>
									</table>

									<h5 style="padding:5px ; margin:0; text-align:left;">Annonate</h5>
									<div style="font-size:11px; text-align:left;">
										<ul style="margin:0;">
											<li>
												<span style="font-style:italic;">
													SUB = Subjective, OBJ = Objective <b> (Viva-Voce for Agriculture)</b>, PRA =
													Practical,
													GP = Grade Point, GL = Grade Letter
												</span>
											</li>
											<li>
												<span style="font-style:italic;">
													The highest marks are calculated based on the whole class, not just the section.
												</span>
											</li>


										</ul>
									</div>

									<div style="margin : 15px 0 15px 25px; font-size:80%; text-align:left;">
										Result Published On :
										<b><?php
										echo date('l, j F, Y', strtotime($rpubdt)); ?></b>
										<?php
										if ($aicomm) {
											// echo '<br><span style="color:blue; font-weight:bold;">Comment : </span>' . $aicomm;
										}
										?>

									</div>

									<table width="100%" style="margin:5px 0 0 0; border:0;">

										<tr>
											<td valign="bottom">
												<?php
												if ($exam == 'Half Yearly') {
													$exx = 1;
												} else {
													$exx = 0;
												}
												$lnk = 'http://www.students.eimbox.com/transcript.php?qr=' . $sessionyear . $stid . $exx;
												//echo $lnk;
												?>

												<img style=" margin:auto;"
													src="https://quickchart.io/qr?text=<?php echo $lnk; ?>&size=70" />
												<br>

											</td>
											<?php if ($progressguar == 1) { ?>

												<td valign="bottom" style="font-size:11px;">

													............................................
													<br>
													Guardian's Signature<br><br><br>

												</td>
											<?php } ?>

											<td valign="bottom" style="text-align:center;">

												<?php
												$tna = 'KKK';
												// $sql000 = "SELECT * FROM classroutine where sccode='$sccode' and classname='$cn' and sectionname = '$secname' and period='First' ";
												// $result000 = $conn->query($sql000);
												// if ($result000->num_rows > 0) {
												// 	while ($row000 = $result000->fetch_assoc()) {
												// 		$tid = $row000["tid"];
												// 	}
												// }
									
												// $sql000 = "SELECT * FROM teacher where tid='$tid' ";
												// $result000 = $conn->query($sql000);
												// if ($result000->num_rows > 0) {
												// 	while ($row000 = $result000->fetch_assoc()) {
												// 		$tname = $row000["tname"];
												// 	}
												// }
												$tna = $tname = $cteacher;
												$tna = '';
												?>

												<span style="font-size:11px; text-align:center;">
													<!--<img src="sign/<?php echo $tid; ?>.png" width="120px" /><br>-->
													<img src="https://eimbox.com/sign/<?php echo $ctid; ?>.png" class="tsign" alt=""
														style="height:12mm;"
														onerror="this.onerror=null;this.src='https://eimbox.com/sign/nosign.png';" /><br>

													<b><small><?php echo '( ' . $cteacher . ' )'; ?></small></b><br>
													Class Teacher (<?php echo $cn . ' : ' . $secname;
													; ?>)<br>
													<?php echo $einame; ?><br>
													<?php echo $eiaddress; ?>
													<!--
												<br>
												<?php echo '<b>t' . $tna . '</b>'; ?><br><?php echo $mno; ?>
												-->
												</span>
											</td>

											<td valign="botttom" style="text-align:center;">

												<?php
												if ($sign == 'true') {
													$htid = $sccode;
													?>

													<div style="font-size:12px;">
														<img src="https://eimbox.com/sign/<?php echo $htid; ?>.png" class="tsign" alt=""
															style="height:12mm;"
															onerror="this.onerror=null;this.src='https://eimbox.com/sign/nosign.png';" /><br>

													</div>



													<?php
												} else {
													echo '<span style="height:100px;"></span>';
												}

												?>


												<span style="font-size:11px; text-align:center;">
													<?php echo '<b>( ' . $headname . ' )</b>'; ?><br>
													<small><?php echo $headtitle; ?></small><br>
													<?php echo $scname; ?><br>
													<?php echo $eiaddress; ?>
												</span>
											</td>


										</tr>
										<tr>
											<td colspan="4" style="height:1mm;">&nbsp;</td>
										</tr>

									</table>


								</td>

							</tr>


						</table>

					</div>
				</div>

				<div style="height:<?php $lastpad = '0mm';
				echo $lastpad; ?>; "></div>
				<?php


			}
		} else {
			echo 'No Student Found.';
		}
		?>

	</body>

	</html>



	<script>
		function goo() {
			window.location.href = 'index.php';
		}
	</script>
	<?php
}