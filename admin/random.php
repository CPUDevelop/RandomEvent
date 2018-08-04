<?php
session_start();
include('../db.php');

// 세션값 변수 지정
$member_id = $_SESSION['member_id'];

if(isset($member_id)){
	$ck1 = $connection->prepare('SELECT * FROM homepage');
	$ck1->execute();
	$check_ck1 = $ck1->fetch(PDO::FETCH_ASSOC);
	
	$vote_name = "voting_".$check_ck1['vote_num']."";

	// 랜덤 DB 검색
	$ck2 = $connection->prepare('SELECT * FROM '.$vote_name.' ORDER BY RAND() LIMIT 1');
	$ck2->execute(array($id_post));
	$check_ck2 = $ck2->fetchAll(PDO::FETCH_ASSOC);
	$date = $check_ck2[0]['date'];

	// 당첨자 출력
	$ck3 = $connection->prepare('SELECT * FROM vote WHERE vote_name = ?');
	$ck3->execute(array($check_ck1['vote_num']));
	$check_ck3 = $ck3->fetch(PDO::FETCH_ASSOC);
	$vote_list = $check_ck3['vote_list'];
	$now_id = $check_ck3['now_id'];
	
	$email = substr($check_ck2[0]['email'], 0, 1) . "**" . substr($check_ck2[0]['email'], 3);
	sleep(1);
?>
<script>
$('#show_name1,#show_name2').html("<?php echo($check_ck2[0]['name']); ?>");
$('#show_email').html("<?php echo($email); ?>");
$('#show_date').html("<?php echo substr($date, 0, 4); ?>년 <?php echo substr($date, 5, 2); ?>월 <?php echo substr($date, 8, 2); ?>일, <?php echo substr($date, 11, 2); ?>시 <?php echo substr($date, 14, 2); ?>분 <?php echo substr($date, 17, 2); ?>초");
$('#name<?php echo($now_id); ?>').html('<span style="color:blue;"><?php echo($check_ck2[0]['name']); ?></span>');
$('#email<?php echo($now_id); ?>').html('<span style="color:blue;"><?php echo($email); ?></span>');
<?php if($vote_list == $now_id + 1){ ?>
$('#done').val("1");
<?php } ?>
</script>
<?php
	// 다음 당첨자 Load
	$upd = $connection->prepare('UPDATE vote SET now_id = now_id + 1, user'.$now_id.' = ? WHERE vote_name = ?');
	$upd->execute(array("".$check_ck2[0]['name']." / ".$check_ck2[0]['email']."", $check_ck1['vote_num']));
}else{
	echo("<script>location.href='./login.php';</script>");
}
?>