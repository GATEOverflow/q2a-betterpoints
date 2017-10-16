<?php
        
                        
    if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                    header('Location: ../../');
                    exit;   
    }               

    qa_register_plugin_module('module', 'qa-bp-admin.php', 'qa_bp_admin', 'Better Points');
    qa_register_plugin_module('event', 'qa-bp-events.php', 'qa_bp_events', 'Better Points Events');
    qa_register_plugin_layer('qa-bp-layer.php', 'Better Points Layer');
    qa_register_plugin_overrides('qa-bp-overrides.php', 'Better Points Override');
	qa_register_plugin_phrases('qa-bp-lang-*.php', 'bp_lang');    
/*                              
    Omit PHP closing tag to help avoid accidental output
*/                              
                          

