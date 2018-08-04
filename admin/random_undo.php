<?php
session_start();
include('../db.php');

// 세션값 변수 지정
$member_id = $_SESSION['member_id'];

if(isset($member_id)){
	$ck1 = $connection->prepare('SELECT * FROM homepage');
	$ck1->execute();
	$check_ck1 = $ck1->fetch(PDO::FETCH_ASSOC);

	// 당첨자 불러오기
	$ck3 = $connection->prepare('SELECT * FROM vote WHERE vote_name = ?');
	$ck3->execute(array($check_ck1['vote_num']));
	$check_ck3 = $ck3->fetch(PDO::FETCH_ASSOC);
	$now_id = $check_ck3['now_id'];
	$now_id = $now_id - 1;
	
	// 이전 당첨자 취소
	$upd = $connection->prepare('UPDATE vote SET now_id = now_id - 1, user'.$now_id.' = ? WHERE vote_name = ?');
	$upd->execute(array("", $check_ck1['vote_num']));
	
	// 완료
	echo "<script>alert('이전 당첨자를 취소 하였습니다.');</script>";
	echo("<script>location.reload();</script>");
	
}else{
	echo("<script>location.href='./login.php';</script>");
}
?>