<?php
class qa_bp_events {


	function process_event($event, $userid, $handle, $cookieid, $params) {
		require_once QA_INCLUDE_DIR.'db/points.php';
		$edit = array('q_edit', 'a_edit', 'c_edit');
		if(in_array($event, $edit))
		{
			qa_db_points_update_ifuser($userid, 'edits');
		}

		else if($event === 'qas_blog_b_post')
		{
			qa_db_points_update_ifuser($userid, 'edits');

		}

		else if($event === 'qa_exam_post')
		{
			qa_db_points_update_ifuser($userid, 'exams');
		}

		else if($event === 'qa_exam_taken')
		{
			qa_db_points_update_ifuser($userid, 'examstaken');

		}


	}

}

