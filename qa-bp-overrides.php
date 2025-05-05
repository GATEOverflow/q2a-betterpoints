<?php

function qa_db_points_calculations(){

	$orig =  qa_db_points_calculations_base();
	if(!qa_opt('qa_bp_enable'))
		return $orig;	
	$options=qa_get_options(qa_db_points_option_names());
	$orig['cposts']['multiple'] =  $options['points_multiple']*qa_opt('qa_bp_commentpoints');


	if(qa_opt('edit_history_active')){
		$orig['edits'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_editpoints'),
				'formula' => "count(distinct postid) as edits from ^userpoints AS userid_src JOIN ^edit_history on userid_src.userid=^edit_history.userid where ^edit_history.userid~",
				);
	}
	if(qa_opt('cache_exams_count') > 0){
		$orig['exams'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_exampoints'),
				'formula' => "count(*) as exams from ^userpoints AS userid_src JOIN ^exams on userid_src.userid=^exams.userid where ^exams.userid~",
				);
		$orig['examstaken'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_examtakenpoints'),
				'formula' => "count(distinct examid) as examstaken from  ^userpoints AS userid_src JOIN ^exam_results on userid_src.userid=^exam_results.userid  where ^exam_results.userid~",
				);
	}
	if(qa_opt('cache_blog_pcount') > 0){
		$orig['blogs'] = array(
				'multiple' => $options['points_multiple']*qa_opt('qa_bp_blogpoints'),
				'formula' => "count(*) as blogs from ^userpoints AS userid_src JOIN ^blogs on userid_src.userid=^blogs.userid where ^blogs.userid~",
				);
	}

	return $orig;
}

/*function intable($field)
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

}*/
function qa_db_points_update_ifuser_off($userid, $columns)
{
	if(!qa_opt('qa_bp_enable'))
		return qa_db_points_update_ifuser_base($userid, $columns);
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
		//	if(intable($field)){
				if (isset($keycolumns[$field])) {
					$insertfields.=$field.', ';
					$insertvalues.='@_'.$field.':=(SELECT '.$calculation['formula'].'), ';
					$updates.=$field.'=@_'.$field.', ';
					$insertpoints.='+('.(int)$multiple.'*@_'.$field.')';
							}	
							$updatepoints.='+('.$multiple.'*'.(isset($keycolumns[$field]) ? '@_' : '').$field.')';
								/*}
								else{
								$updatepoints.='+('.$multiple.'*'.(isset($keycolumns[$field]) ? '@_' : '').'(select '.$calculation['formula'].'))';
								}*/
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
