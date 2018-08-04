<?php
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);
	session_start();
	include('../db.php');
	
	$clientId = "KEY";
	$clientSecret = "KEY";
	$g_youtubeDataAPIKey = "KEY";
	$redirectURL = "http://사이트주소.com/youtube/oauthresponse.php";

	if ($_GET["code"] != null && $_GET["code"] != "") {

		$f = fopen("data/response.txt", "w");
		if ($f) {
			fwrite($f, serialize($resp));
			fclose($f);

			$arrResp = json_decode($resp, true);
			$accessToken = $arrResp["access_token"];
			$_SESSION["access_token"] = $accessToken;
			
			// accessToken 검사
			if ($accessToken == null || $accessToken == "") {
				$_SESSION["access_token"] = "";
				header("Location: ./login.php");
				exit;
			}
    
			// 구독 여부 확인
			$url = "https://www.googleapis.com/youtube/v3/subscriptions?key=" . $g_youtubeDataAPIKey .
   			 "&access_token=" . $accessToken. "&part=snippet&mine=true&maxResults=50";

			$curl = curl_init();
				 curl_setopt_array($curl, array(
   				 CURLOPT_RETURNTRANSFER => 1,
   				 CURLOPT_URL => $url,
   				 CURLOPT_USERAGENT => 'YouTube API Tester',
   				 CURLOPT_SSL_VERIFYPEER => 1,
   				 CURLOPT_SSL_VERIFYHOST=> 0,
   				 CURLOPT_CAINFO => "cert/cacert.pem",
   				 CURLOPT_CAPATH => "cert/cacert.pem",
   				 CURLOPT_FOLLOWLOCATION => TRUE
   				 ));
			$resp = curl_exec($curl);

			curl_close($curl);
   	 
			if ($resp) {
				$json = json_decode($resp);
		 
				if ($json) {
					//$total = $json->pageInfo->totalResults;
					$items = $json->items;

					foreach($items as $item) {
						$title = $item->snippet->title;
						if($title == "로이조 TV"){
							$subscribe = 1;
						}
					}
					
					// 변수 지정
					$h1 = $connection->prepare('SELECT * FROM homepage');
					$h1->execute();
					$check_h1 = $h1->fetch(PDO::FETCH_ASSOC);
					$vote_name = "voting_".$check_h1['vote_num']."";
					
					$name = htmlspecialchars($_SESSION["name"]);
					$email = htmlspecialchars($_SESSION["email"]);
					$phone = htmlspecialchars($_SESSION["phone"]);
					$date = date('Y-m-d H:i:s');
					$ip = $_SERVER['REMOTE_ADDR'];
					
					// 비어있는지 값 확인
					if(empty($name) || empty($email) || empty($phone)){
						echo "<script>alert('값 전달이 잘못 되었습니다. 다시 시도하세요');</script>";
						echo("<script>location.href='/';</script>");
						return false;
					}
					
					// 응모 마감 확인
					$h2 = $connection->prepare('SELECT * FROM vote WHERE vote_name = ?');
					$h2->execute(array($check_h1['vote_num']));
					$check_h2 = $h2->fetch(PDO::FETCH_ASSOC);
					if($date > $check_h2['finish_date']){
						echo "<script>alert('응모가 마감된 이벤트입니다. ".$check_h2['finish_date']."');</script>";
						echo("<script>location.href='/';</script>");
						return false;
					}
					
					// 중복 응모 제거 - 구글 아이디
					$ck1 = $connection->prepare('SELECT * FROM '.$vote_name.' WHERE google_id = ?');
					$ck1->execute(array($google_id));
					$check_ck1 = $ck1->fetch(PDO::FETCH_ASSOC);
					
					// 중복 응모 제거 - 아이피
					$ck2 = $connection->prepare('SELECT * FROM '.$vote_name.' WHERE user_ip = ?');
					$ck2->execute(array($ip));
					$check_ck2 = $ck2->fetch(PDO::FETCH_ASSOC);
					
					if(!empty($check_ck1['id']) || !empty($check_ck2['id'])){
						$st2 = $connection->prepare('UPDATE vote SET overlap = overlap + 1 WHERE vote_name = ?');
						$st2->execute(array($check_h1['vote_num']));
						
						echo "<script>alert('중복 응모는 불가합니다. (구글아이디/아이피 1회 제한)');</script>";
						echo("<script>location.href='/';</script>");
						return false;
					}
			
					// 구독 확인후 DB 저장
					if($subscribe == 1){
						$st2 = $connection->prepare('INSERT INTO '.$vote_name.' (name, email, phone, subscribe, google_id, date, user_ip) values (?, ?, ?, ?, ?, ?, ?)');
						$st2->execute(array($name, $email, $phone, "1", $google_id, $date, $ip));
						
						$st2 = $connection->prepare('UPDATE vote SET total = total + 1 WHERE vote_name = ?');
						$st2->execute(array($check_h1['vote_num']));
						
						echo "<script>alert('응모가 완료 되었습니다!');</script>";
						echo("<script>location.href='/success.php?vote_google_id=".$google_id."';</script>");
					}else{
						echo "<script>alert('로이조 TV 구독자가 아닙니다. 구독을 눌러주세요.');</script>";
						echo("<script>location.href='https://www.youtube.com/channel/UCLJs55bZWMsCBPekt3vssoQ?sub_confirmation=1';</script>");
					}
				} else
					exit("JSON 오류가 발생 하였습니다." . json_last_error_msg());
			}
			
			// 세션 초기화
			session_destroy();

		} else
			exit("response.txt를 쓰는데 오류가 발생 하였습니다.");
	} else {
		@unlink("data/response.txt");
	}
	
?>
