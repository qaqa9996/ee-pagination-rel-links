<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pagination Rel Links Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		
 */

class Pagination_rel_links_ext {
	
	public $settings 		= array();
	public $description		= 'Enables pagination rel links';
	public $docs_url		= '';
	public $name			= 'Pagination Rel Links';
	public $settings_exist	= 'n';
	public $version			= '1.0';
	
	//private $prev_link;
	//private $next_link;
	
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;
	}// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'pagination_create',
			'hook'		=> 'pagination_create',
			'settings'	=> serialize($this->settings),
			'priority' => 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);
		
		if (version_compare(APP_VER, '2.7', '<'))
		{
			$data['hook'] = 'channel_module_create_pagination';
		}

		ee()->db->insert('extensions', $data);			
				
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'template_post_parse',
			'hook'		=> 'template_post_parse',
			'settings'	=> serialize($this->settings),
			'priority' => 10,
			'version'	=> $this->version,
			'enabled'	=> 'y',
		);

		ee()->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * pagination_create
	 *
	 * @param 
	 * @return 
	 */
	public function pagination_create($data, $count)
	{
		ee()->load->library('rel_links');
	
		$total_items = $data->total_items;
		$per_page = $data->per_page;
		$offset = $data->offset;
		$prefix = $data->prefix;
	
		$segment_array = ee()->uri->segment_array();
		$segment_count = count($segment_array);
		$last_segment = end($segment_array);

		if (preg_match('/'.$prefix.'\d+/', $last_segment))
		{
			// make sure we have an offset
			if ($offset == 0)
			{
				$offset = (int) substr($last_segment,1);
			}
			unset($segment_array[$segment_count]);
		}
		
		$url = implode('/', $segment_array);

		if ($offset > 0 && $offset < $total_items)
		{
			$prev_link = $url.($offset-$per_page>0 ? '/'.$prefix.($offset-$per_page) : '');
			ee()->rel_links->prev = $prev_link;
		}
		
		if ($offset < $total_items-$per_page)
		{
			$next_link = $url.'/'.$prefix.($offset+$per_page);
			ee()->rel_links->next = $next_link;
		}
	}
	
	
	function template_post_parse($final_template, $sub, $site_id)
	{
		if (isset(ee()->extensions->last_call) && ee()->extensions->last_call)
		{
		    $final_template = ee()->extensions->last_call;
		}
		
		ee()->load->library('rel_links');
		
		$prev_uri = ee()->rel_links->prev;
		$next_uri = ee()->rel_links->next;

		// uri and url
		$final_template = str_replace('{pagination_rel_links:prev_uri}', $prev_uri, $final_template);
		$final_template = str_replace('{pagination_rel_links:next_uri}', $next_uri, $final_template);
		$final_template = str_replace('{pagination_rel_links:prev_url}', site_url().$prev_uri, $final_template);
		$final_template = str_replace('{pagination_rel_links:next_url}', site_url().$next_uri, $final_template);

		// full link tag
		$final_template = str_replace('{pagination_rel_links:prev}', ($prev_uri ? '<link rel="prev" href="'.site_url().$prev_uri.'" />' : ''), $final_template);
		$final_template = str_replace('{pagination_rel_links:next}', ($next_uri ? '<link rel="next" href="'.site_url().$next_uri.'" />' : ''), $final_template);


		return $final_template;
	}
	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.pagination_rel_links.php */
/* Location: /system/expressionengine/third_party/pagination_rel_links/ext.pagination_rel_links.php */