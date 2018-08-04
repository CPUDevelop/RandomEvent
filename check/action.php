<?php
session_start();
include('../db.php');

	// POST 값
	$captcha = htmlspecialchars($_POST['captcha']);
	$email = htmlspecialchars($_POST['email']);
	
	// 비어있는지 확인
	if(empty($email)){
		echo "<script>alert('검색할 이메일을 입력해주세요.');</script>";
		return false;
	}
	
	// 이메일 유효성 확인
	$check_email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if($check_email == false){
		echo "<script>alert('이메일이 유효하지 않습니다.');</script>";
		return false;
	}
	
	// 이메일 DB 검색
	$ck1 = $connection->prepare('SELECT * FROM homepage');
	$ck1->execute();
	$check_ck1 = $ck1->fetch(PDO::FETCH_ASSOC);
	$vote_name = "voting_".$check_ck1['vote_num']."";
	
	$ck2 = $connection->prepare('SELECT * FROM '.$vote_name.' WHERE email = ?');
	$ck2->execute(array($email));
	$check_ck2 = $ck2->fetchAll(PDO::FETCH_ASSOC);
	$votecount = $ck2->rowCount();
	
	$ck3 = $connection->prepare('SELECT * FROM vote WHERE vote_name = ?');
	$ck3->execute(array($check_ck1['vote_num']));
	$check_ck3 = $ck3->fetch(PDO::FETCH_ASSOC);
	$date = date('Y-m-d H:i:s');
	
	if($date > $check_ck3['finish_date']){
		echo "<script>alert('응모가 마감된 이벤트입니다.');</script>";
		return false;
	}
	
	
	if($_SESSION["g_captcha"] == 1){
		if(!$captcha){
			echo "<script>alert('로봇이 아님을 증명해주세요.');</script>";
			return false;
		}
	
		// 캡차 값을 curl 형식으로 보내기
		$ch = curl_init();
 
		curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=KEY&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
		$captcha_success = curl_exec ($ch);
		$captcha_success = json_decode($captcha_success);
 
		curl_close ($ch);
	
		// 캡차가 실패 되었다면, 시작
		if($captcha_success->success == false) {
			echo "<script>alert('로봇 인증이 실패 되었습니다.');</script>";
			return false;
		}
	}else{
		$_SESSION["g_captcha"] = "1";
	}
	
	if($votecount == 0){
		echo "<script>alert('검색한 이메일은 응모 기록이 없습니다.');</script>";
		return false;
	}else{
		for($i=0;$i<$votecount;$i++){
		$date = $check_ck2[$i]['date']; ?>
		<br><table class="table table-bordered">
            <tbody>
				<tr>
					<th bgcolor="#f4f4f4">응모번호</th>
					<td>#<?php echo($check_ck2[$i]['id']); ?> 번</td>
                </tr>
                <tr>
					<th>유튜브 닉네임</th>
					<td style="color:blue;"><?php echo($check_ck2[$i]['name']); ?></td>
                </tr>
				<tr>
					<th bgcolor="#f4f4f4">당첨 확인 이메일</th>
					<td style="color:blue;"><?php echo($check_ck2[$i]['email']); ?></td>
                </tr>
				<tr>
					<th>전화번호</th>
					<td style="color:blue;"><?php echo substr(preg_replace("/(0(?:2|[0-9]{2}))([0-9]+)([0-9]{4}$)/", "\\1-\\2-\\3", $check_ck2[$i]['phone']), 0, -1) . "*"; ?></td>
                </tr>
				<tr>
					<th bgcolor="#f4f4f4">구독 여부</th>
					<td><?php if($check_ck2[$i]['subscribe'] == 1) echo("로이조TV 구독자"); else echo("로이조TV 구독자가 아닙니다");?></td>
                </tr>
				<tr>
					<th>응모하신 날짜</th>
					<td><?php echo substr($date, 0, 4); ?>년 <?php echo substr($date, 5, 2); ?>월 <?php echo substr($date, 8, 2); ?>일, <?php echo substr($date, 11, 2); ?>시 <?php echo substr($date, 14, 2); ?>분 <?php echo substr($date, 17, 2); ?>초</td>
                </tr>
			</tbody>
		</table>
	<?php }
	} ?>