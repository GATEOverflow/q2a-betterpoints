<?php

class qa_html_theme_layer extends qa_html_theme_base {
	public function show_post_count_on_profile()
	{
		if ( $this->template == 'user' &&
				qa_opt('edit_history_active') &&
				!empty( $this->content["form_activity"]['fields']['questions'] )
		   ) {

			$handle = qa_request_part( 1 );
			$user_id = qa_handle_to_userid( $handle );
			$query = "select count(distinct postid) from ^edit_history where userid = #";
			$result = qa_db_query_sub($query, $user_id);
			$num_of_edits = qa_db_read_one_value($result);
			$edits_info = array(
					'type'  => 'static',
					'id'    => 'edits',
					'label' => qa_lang( 'bp_lang/edits' ),
					'value' => '<span class="qa-uf-user-q-posts">' . $num_of_edits . '</span>',
					);
			qa_array_insert( $this->content["form_activity"]['fields'], 'questions', array( 'edits' => $edits_info ) );
		}
		qa_html_theme_base::show_post_count_on_profile();
	}




}

