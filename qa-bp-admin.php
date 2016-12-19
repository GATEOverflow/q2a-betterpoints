<?php
class qa_bp_admin {

	function option_default($option) {

		switch($option) {
			case 'qa_bp_enable': 
				return 0;
			case 'qa_bp_commentpoints': 
				return 1;
			case 'qa_bp_commentuppoints': 
				return 2;
			case 'qa_bp_commentdownpoints': 
				return 1;
			case 'qa_bp_commentuplimitpoints': 
				return 10;
			case 'qa_bp_commentdownlimitpoints': 
				return 5;
			case 'qa_bp_commentupvotepoints': 
				return 1;
			case 'qa_bp_commentdownvotepoints': 
				return 0;
			case 'qa_bp_editpoints': 
				return 2;
			case 'qa_bp_exampoints': 
				return 4;
			case 'qa_bp_examtakenpoints': 
				return 2;
			case 'qa_bp_blogpoints': 
				return 2;
			default:
				return null;				
		}

	}

	function allow_template($template)
	{
		return ($template!='admin');
	}

	public function init_queries($tableslc)
	{
		//if(qa_opt('qa_bp_enable') && !qa_opt('qa_bp_initialized'))
		if(qa_opt('qa_bp_enable'))// && !qa_opt('qa_bp_initialized'))
		{

			$queries = array();
			$tablename = qa_db_add_table_prefix('userpoints');


			$sqlCols = 'SHOW COLUMNS FROM '.$tablename;
			$fields = qa_db_read_all_values(qa_db_query_sub($sqlCols));
			$newcolumns = array('cupvotes', 'cdownvotes', 'cvoteds');
			if(qa_opt('edit_history_active')){
				$newcolumns [] = 'edits';
			}
			if(qa_opt('cache_exams_count') > 0){
				$newcolumns [] = 'exams';
				$newcolumns [] = 'examstaken';
			}
			if(qa_opt('cache_blog_pcount') > 0){
				$newcolumns [] = 'blogs';

			}
			foreach ($newcolumns as $column){
				if(!in_array($column, $fields)) {
					$queries [] =  'ALTER TABLE '.$tablename.' ADD '.$column. ' MEDIUMINT(9)  NOT NULL DEFAULT 0;';
				}
			}
			qa_opt('qa_bp_initialized', '1');
			return $queries;
		}
		return null;
	}

	function admin_form(&$qa_content)
	{                       

		// Process form input

		$ok = null;

		if (qa_clicked('qa_bp_save')) {
			qa_opt('qa_bp_enable',(bool)qa_post_text('qa_bp_enable'));
			qa_opt('qa_bp_commentpoints',qa_post_text('qa_bp_commentpoints'));
			qa_opt('qa_bp_commentuppoints',qa_post_text('qa_bp_commentuppoints'));
			qa_opt('qa_bp_commentdownpoints',qa_post_text('qa_bp_commentdownpoints'));
			qa_opt('qa_bp_commentupvotepoints',qa_post_text('qa_bp_commentupvotepoints'));
			qa_opt('qa_bp_commentdownvotepoints',qa_post_text('qa_bp_commentdownvotepoints'));
			qa_opt('qa_bp_commentuplimitpoints',qa_post_text('qa_bp_commentuplimitpoints'));
			qa_opt('qa_bp_commentdownlimitpoints',qa_post_text('qa_bp_commentdownlimitpoints'));
			if(qa_opt('edit_history_active')){
				qa_opt('qa_bp_editpoints',qa_post_text('qa_bp_editpoints'));
			}	
			if(qa_opt('cache_exams_count') >= 0){
				qa_opt('qa_bp_exampoints',qa_post_text('qa_bp_exampoints'));
				qa_opt('qa_bp_examtakenpoints',qa_post_text('qa_bp_examtakenpoints'));
			} 
			if(qa_opt('cache_blog_pcount') > 0)		
				qa_opt('qa_bp_blogpoints',qa_post_text('qa_bp_blogpoints'));
			$ok = qa_lang('bp_lang/savemsg');
		}

		// Create the form for display

		$fields = array();
		$fields[] = array(
				'label' => 'Enable Better Points',
				'tags' => 'name="qa_bp_enable"',
				'value' => (bool)qa_opt('qa_bp_enable'),
				'type' => 'checkbox',
					);
				$fields[] = array(
					'label' => 'Points for a Comment Post',
					'tags' => 'name="qa_bp_commentpoints"',
					'value' => qa_opt('qa_bp_commentpoints'),
					'type' => 'number',
					);
				$fields[] = array(
					'label' => 'Points for Receiving a Comment Upvote',
					'tags' => 'name="qa_bp_commentuppoints"',
					'value' => qa_opt('qa_bp_commentuppoints'),
					'type' => 'number',
					);
				$fields[] = array(
					'label' => 'Limit in Points for a Comment Up Vote',
					'tags' => 'name="qa_bp_commentuplimitpoints"',
					'value' => qa_opt('qa_bp_commentuplimitpoints'),
					'type' => 'number',
					);
				$fields[] = array(
						'label' => 'Reduction in Points for Receiving a Comment DownVote',
						'tags' => 'name="qa_bp_commentdownpoints"',
						'value' => qa_opt('qa_bp_commentdownpoints'),
						'type' => 'number',
						);
				$fields[] = array(
						'label' => 'Limit in Points for a Comment Down Vote',
						'tags' => 'name="qa_bp_commentdownlimitpoints"',
						'value' => qa_opt('qa_bp_commentdownlimitpoints'),
						'type' => 'number',
						);
				$fields[] = array(
						'label' => 'Points for Giving a Comment Upvote',
						'tags' => 'name="qa_bp_commentupvotepoints"',
						'value' => qa_opt('qa_bp_commentupvotepoints'),
						'type' => 'number',
						);
				$fields[] = array(
						'label' => 'Points for Giving a Comment DownVote',
						'tags' => 'name="qa_bp_commentdownvotepoints"',
						'value' => qa_opt('qa_bp_commentdownvotepoints'),
						'type' => 'number',
						);
				if(qa_opt('edit_history_active')){
					$fields[] = array(
							'label' => 'Points for an Edit',
							'tags' => 'name="qa_bp_editpoints"',
							'value' => qa_opt('qa_bp_editpoints'),
							'type' => 'number',
							);
				}
				if(qa_opt('cache_exams_count') >= 0){
					$fields[] = array(
							'label' => 'Points for Creating an Exam',
							'tags' => 'name="qa_bp_exampoints"',
							'value' => qa_opt('qa_bp_exampoints'),
							'type' => 'number',
							);
					$fields[] = array(
							'label' => 'Points for Taking an Exam',
							'tags' => 'name="qa_bp_examtakenpoints"',
							'value' => qa_opt('qa_bp_examtakenpoints'),
							'type' => 'number',
							);
				}
				if(qa_opt('cache_blog_pcount') >= 0){
					$fields[] = array(
							'label' => 'Points for Creating a Blog Post',
							'tags' => 'name="qa_bp_blogpoints"',
							'value' => qa_opt('qa_bp_blogpoints'),
							'type' => 'number',
							);
				}

				return array(           
						'ok' => ($ok && !isset($error)) ? $ok : null,

						'fields' => $fields,

						'buttons' => array(
							array(
								'label' => qa_lang_html('main/save_button'),
								'tags' => 'NAME="qa_bp_save"',
							     ),
							),
					    );
	}
}

