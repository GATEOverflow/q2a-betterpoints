<?php

function qa_db_points_calculations(){
	$orig =  qa_db_points_calculations_base();
	$options=qa_get_options(qa_db_points_option_names());
	$orig['cposts']['multiple'] =  $options['points_multiple']*qa_opt('qa_bp_commentpoints');

	$orig [	'cupvotes'] = array(
			'multiple' => $options['points_multiple']*qa_opt('qa_bp_commentupvotepoints'),
			'formula' => "COUNT(*) AS cupvotes FROM ^uservotes AS userid_src JOIN ^posts ON userid_src.postid=^posts.postid WHERE userid_src.userid~ AND LEFT(^posts.type, 1)='C' AND userid_src.vote>0",
			);

	$orig['cdownvotes'] = array(
			'multiple' => $options['points_multiple']*qa_opt('qa_bp_commentdownvotepoints'),
			'formula' => "COUNT(*) AS cdownvotes FROM ^uservotes AS userid_src JOIN ^posts ON userid_src.postid=^posts.postid WHERE userid_src.userid~ AND LEFT(^posts.type, 1)='C' AND userid_src.vote<0",
			);
	$orig['cvoteds'] = array(
			'multiple' => $options['points_multiple'],
			'formula' => "COALESCE(SUM(".
			"LEAST(". qa_opt('qa_bp_commentuppoints')."*upvotes,". qa_opt('qa_bp_commentuplimitpoints').")".
			"-".
			"LEAST(".qa_opt('qa_bp_commentdownpoints')."*downvotes,".qa_opt('qa_bp_commentdownlimitpoints').")".
			"), 0) AS cvoteds FROM ^posts AS userid_src WHERE LEFT(type, 1)='C' AND userid~",
			);

	if(qa_opt('edit_history_active')){
		$orig['edits'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_editpoints'),
				'formula' => "count(distinct postid) as cnt from ^edit_history where ^edit_history.userid~",
				);
	}
	if(qa_opt('cache_exams_count') > 0){
		$orig['exams'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_exampoints'),
				'formula' => "count(*) as cnt from ^exams where ^exams.userid~",
				);
		$orig['examstaken'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_examtakenpoints'),
				'formula' => "count(distinct examid) as cnt from ^exam_results where ^exam_results.userid~",
				);
	}
	if(qa_opt('cache_blog_pcount') > 0){
		$orig['blogs'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_blogpoints'),
				'formula' => "count * as cnt from ^blogs where ^blogs.userid~",
				);
	}

	return $orig;
}

function intable($field)
{
	$query="desc ^userpoints";
	$result = qa_db_query_sub($query);
	$columns = qa_db_read_all_assoc($result);
	for($i = 0; $i < count($columns); $i++)
		if($field === $columns[$i]['Field'])
		{
			return true;
		}
	return false;

}
function qa_db_points_update_ifuser($userid, $columns)
{
	if (qa_should_update_counts() && isset($userid)) {
		require_once QA_INCLUDE_DIR.'app/options.php';
		require_once QA_INCLUDE_DIR.'app/cookies.php';
		$calculations=qa_db_points_calculations();

		if ($columns===true)
			$keycolumns=$calculations;
		elseif (empty($columns))
			$keycolumns=array();
		elseif (is_array($columns))
			$keycolumns=array_flip($columns);
		else
			$keycolumns=array($columns => true);

		$insertfields='userid, ';
		$insertvalues='$, ';
		$insertpoints=(int)qa_opt('points_base');

		$updates='';
		$updatepoints=$insertpoints;

		foreach ($calculations as $field => $calculation) {
			$multiple=(int)$calculation['multiple'];
			if(intable($field)){
				if (isset($keycolumns[$field])) {
					$insertfields.=$field.', ';
					$insertvalues.='@_'.$field.':=(SELECT '.$calculation['formula'].'), ';
					$updates.=$field.'=@_'.$field.', ';
					$insertpoints.='+('.(int)$multiple.'*@_'.$field.')';
							}
							$updatepoints.='+('.$multiple.'*'.(isset($keycolumns[$field]) ? '@_' : '').$field.')';
								}
								else{
								$updatepoints.='+('.$multiple.'*'.(isset($keycolumns[$field]) ? '@_' : '').'(select '.$calculation['formula'].'))';
								}
								}

								$query='INSERT INTO ^userpoints ('.$insertfields.'points) VALUES ('.$insertvalues.$insertpoints.') '.
								'ON DUPLICATE KEY UPDATE '.$updates.'points='.$updatepoints.'+bonus';

								qa_db_query_raw(str_replace('~', "='".qa_db_escape_string($userid)."'", qa_db_apply_sub($query, array($userid))));
								// build like this so that a #, $ or ^ character in the $userid (if external integration) isn't substituted

								if (qa_db_insert_on_duplicate_inserted())
								qa_db_userpointscount_update();
								}


								}

								?>
