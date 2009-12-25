/**
Override field definitions for core database tables.
If a table is included here, ALL fields must be defined.

First level array key is the table name (without any kind of prefix)
Second level array keys are always '_FIELD_TYPES', '_NOTNULL' and suchlike.

These definitions are just examples - there is no logic behind what they do!
*/
array('comments' => 
	array (
	  '_FIELD_TYPES' => 
	  array (
		'comment_id' => 'int',
		'comment_pid' => 'int',
		'comment_item_id' => 'int',
		'comment_subject' => 'todb',
		'comment_author_id' => 'int',
		'comment_author_name' => 'todb',
		'comment_author_email' => 'todb',
		'comment_datestamp' => 'int',
		'comment_comment' => 'string',
		'comment_blocked' => 'int',
		'comment_ip' => 'todb',
		'comment_type' => 'escape',
		'comment_lock' => 'int',
	  ),
	  '_NOTNULL' => 
	  array (
		'comment_id' => '',
		'comment_comment' => '',
	  ),
	),
	'links' => 
	array (
	  '_FIELD_TYPES' => 
	  array (
		'link_id' => 'int',
		'link_name' => 'todb',
		'link_url' => 'todb',
		'link_description' => 'string',
		'link_button' => 'todb',
		'link_category' => 'int',
		'link_order' => 'int',
		'link_parent' => 'int',
		'link_open' => 'int',
		'link_class' => 'todb',
		'link_function' => 'escape',
	  ),
	  '_NOTNULL' => 
	  array (
		'link_id' => '',
		'link_description' => '',
	  ),
	)
)