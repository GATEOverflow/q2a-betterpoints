<?php
class qa_bp_admin {

	function option_default($option) {

		switch($option) {
			case 'qa_bp_enable': 
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

