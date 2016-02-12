<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pagination Rel Links Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		
 * @ee_version	2.4.0 >
 */

class Pagination_rel_links_ext {
	
	public $settings 		= array();
	public $description		= 'Enables pagination rel links for SEO';
	public $docs_url		= '';
	public $name			= 'Pagination Rel Links';
	public $settings_exist	= 'y';
	public $version			= '1.4.2';
	
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;
		
		$this->enable_redirect = FALSE;
		if (isset($this->settings['enable_redirect']) && $this->settings['enable_redirect'] == 'y')
		{
			$this->enable_redirect = TRUE;
		}
		
	}
	// ----------------------------------------------------------------------
	
	
	/**
	 * Settings
	 * @return array
	 */
	function settings()
	{
		$settings = array();

		$settings['page_num_prefix'] = array('i', '', " - Page ");
		$settings['page_num_suffix'] = array('i', '', "");
		$settings['first_page_num'] = array('r', array('y' => "Yes", 'n' => "No"), 'n');
		$settings['enable_redirect'] = array('r', array('y' => "Yes", 'n' => "No"), 'n');
		$settings['use_caching'] = array('r', array('y' => "Yes", 'n' => "No"), 'n');

		return $settings;
	}
	// ----------------------------------------------------------------------
	
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
			'priority' => 99,
			'version'	=> $this->version,
			'enabled'	=> 'y',
		);

		ee()->db->insert('extensions', $data);
		
	}	
	// ----------------------------------------------------------------------

	
	/**
	 * pagination_create
	 *
	 * @param $data, $count
	 * @return void
	 */
	public function pagination_create(&$data, $count)
	{
		$params = ee()->TMPL->tagparams;

		$total_items = isset($data->total_items) ? $data->total_items : 0;
		$per_page = $data->per_page;
		$offset = $data->offset;
		$prefix = isset($data->prefix) ? $data->prefix : 'P';
		
		$segment_array = ee()->uri->segment_array();
		$segment_count = count($segment_array);
		$last_segment = end($segment_array);
		$current_url = implode('/', $segment_array);

		// Let's check to see if Better Pagination extension is installed and active and support this
		// Unable to use sql queries which conflicts with comments module query caching
		$extensions  = ee()->addons->get_installed('extensions');
		if (isset($extensions['better_pagination']))
		{
			// Use Better Pagination
			$config = ee()->config->item('better_pagination');
			$prefix = 'page';
			if (isset($config['page_name']))
			{
				$prefix = $config['page_name'];
			}
			$prefix_uri = '?'.$prefix.'=';
			
			// Redirect invalid paginated urls
			if ($this->enable_redirect == TRUE)
			{
				// Let's redirect to querystring pagination if default ee P# pagination is found
				if (preg_match('/'.$data->prefix.'\d+/', $last_segment))
				{
					$redirect_offset = (int) substr($last_segment,1);
					unset($segment_array[$segment_count]);
					$redirect = implode('/', $segment_array);
					if ($redirect_offset > 0)
					{
						$redirect .= $prefix_uri.$redirect_offset;
					}
					header("HTTP/1.1 301 Moved Permanently"); 
					header( "Location: /".$redirect );
					die();
				}
				// Redirect if pagination is 0
				if ($offset == 0 && ee()->input->get($prefix) === '0')
				{
					$redirect = implode('/', $segment_array);
					header("HTTP/1.1 301 Moved Permanently"); 
					header( "Location: /".$redirect );
					die();
				}	
			}			
		}
		else
		{
			if (preg_match('/'.$prefix.'\d+/', $last_segment))
			{
				// make sure we have an offset - not always available
				if ($offset == 0)
				{
					$offset = (int) substr($last_segment,1);
				}
				unset($segment_array[$segment_count]);
			}
			$prefix_uri = '/'.$prefix;
		}

		
		// Use library to store these pagination settings later (unable to store via session cache or global variables)
		ee()->load->library('rel_links');


		// Use pagination base url if set
		if (isset($params['paginate_base']) && !empty($params['paginate_base']))
		{
			$url = $params['paginate_base'];
		}
		else
		{
			$url = implode('/', $segment_array);
		}
	

		if ($offset > 0 && $total_items > 0 && $offset < $total_items)
		{
			$prev_uri = $url.($offset-$per_page>0 ? $prefix_uri.($offset-$per_page) : '');
			ee()->rel_links->prev = $prev_uri;
		}
		
		if ($total_items > 0 && $offset < $total_items-$per_page)
		{
			$next_uri = $url.$prefix_uri.($offset+$per_page);
			ee()->rel_links->next = $next_uri;
		}
		
		
		// Page number_format
		
		$page_number = $page_num = ($offset/$per_page)+1;
		if (isset($this->settings['page_num_prefix']) && !empty($this->settings['page_num_prefix']))
		{
			$page_num = $this->settings['page_num_prefix'].$page_num;
		}
		if (isset($this->settings['page_num_suffix']) && !empty($this->settings['page_num_suffix']))
		{
			$page_num .= $this->settings['page_num_suffix'];
		}

		if ($page_number == 1)
		{
			if (isset($this->settings['first_page_num']) && $this->settings['first_page_num'] == 'y')
			{
				$page_num_format = $page_num;
				ee()->rel_links->page_num = $page_num_format;
			}
		}
		else
		{
			$page_num_format = $page_num;
			ee()->rel_links->page_num = $page_num_format;
		}

		
		// Redirect to main page if pagination not found
		if ($this->enable_redirect == TRUE)
		{
			if($offset > 0 && $total_items > 0 && $offset >= $total_items && $url != $current_url)
			{
				header( "HTTP/1.1 301 Moved Permanently" );
				header( "Location: /".$url );
				die();
			}
		}
		
		// An attempt to store pagination info somewhere when the pagination has been cached.
		// When cached it is not normally possible to get pagination info - doesn't get called
		// So try storing the link urls as 'tag cache' within the pagination so we can grab later
		if (isset($this->settings['use_caching']) && $this->settings['use_caching'] == 'y')
		{
			// Store page links in case they are cached
			if (is_array($data->template_data))
			{
				$first_key = @key($data->template_data);
				$template_data = $data->template_data[$first_key];
			}
			else
			{
				$template_data = $data->template_data;
			}

			$tag_cache = '';
		
			$tag_cache .= isset($prev_uri) ? '{pagination_rel_links:set:prev_uri='.$prev_uri.'}' : '';
			$tag_cache .= isset($next_uri) ? '{pagination_rel_links:set:next_uri='.$next_uri.'}' : '';
			$tag_cache .= isset($page_num_format) ?  '{pagination_rel_links:set:page_num='.$page_num_format.'}' : '';
			
			// Escape tag if CE Cache is used, otherwise it will display the unparsed tag

			$query = ee()->db->select('module_version')->from('modules')->where('module_name', 'ce_cache')->limit(1)->get();
			if ($query->num_rows > 0)
			{
				$tag_cache = '{exp:ce_cache:escape:pre}'.$tag_cache.'{/exp:ce_cache:escape:pre}';
			}

			$template_data .= $tag_cache;
			
			// add back into to template data
			if (is_array($data->template_data))
			{
				$data->template_data = array($first_key => $template_data);
			}
			else
			{
				$data->template_data = $template_data;
			}
		}
		
	}
	// ----------------------------------------------------------------------

	
	/**
	 * pagination_create
	 *
	 * @param $final_template, $sub, $site_id
	 * @return string
	 */
	function template_post_parse($final_template, $is_partial, $site_id)
	{
		if (isset(ee()->extensions->last_call) && ee()->extensions->last_call)
		{
		    $final_template = ee()->extensions->last_call;
		}

		ee()->load->library('rel_links');
		
		$prev_uri = ee()->rel_links->prev;
		$next_uri = ee()->rel_links->next;
		$page_num = ee()->rel_links->page_num;

		$site_url = ee()->functions->fetch_site_index();
	
		// Get cached pagination values (where pagination has been cached)
		if (isset($this->settings['use_caching']) && $this->settings['use_caching'] == 'y')
		{
			if ( ! $prev_uri )
			{
				$pattern = '|'.LD.'pagination_rel_links:set:prev_uri=(.+?)'.RD.'|';
				preg_match($pattern, $final_template, $matches);
				if (!empty($matches))
				{
					$prev_uri = $matches[1];
				}
			}
			if ( ! $next_uri )
			{	
				$pattern = '|'.LD.'pagination_rel_links:set:next_uri=(.+?)'.RD.'|';
				preg_match($pattern, $final_template, $matches);
				if (!empty($matches))
				{
					$next_uri = $matches[1];
				}
			}
			if ( ! $page_num )
			{	
				$pattern = '|'.LD.'pagination_rel_links:set:page_num=(.+?)'.RD.'|';
				preg_match($pattern, $final_template, $matches);
				if (!empty($matches))
				{
					$page_num = $matches[1];
				}
			}
		
			// Remove any tags used for cached link urls
			if ($prev_uri)
			{
				if ( ! $is_partial) $final_template = str_replace('{pagination_rel_links:set:prev_uri='.$prev_uri.'}', '', $final_template);
			}
			if ($next_uri)
			{
				if ( ! $is_partial) $final_template = str_replace('{pagination_rel_links:set:next_uri='.$next_uri.'}', '', $final_template);
			}
			if ($page_num)
			{
				if ( ! $is_partial) $final_template = str_replace('{pagination_rel_links:set:page_num='.$page_num.'}', '', $final_template);
			}
		}

		// Update template tags (base template to limit the replaces)
		if ( ! $is_partial)
		{
			// uri and url
			$final_template = str_replace('{pagination_rel_links:prev_uri}', $prev_uri, $final_template);
			$final_template = str_replace('{pagination_rel_links:next_uri}', $next_uri, $final_template);
			$final_template = str_replace('{pagination_rel_links:prev_url}', $site_url.$prev_uri, $final_template);
			$final_template = str_replace('{pagination_rel_links:next_url}', $site_url.$next_uri, $final_template);

			// full link tag
			$final_template = str_replace('{pagination_rel_links:prev}', ($prev_uri ? '<link rel="prev" href="'.$site_url.$prev_uri.'" />' : ''), $final_template);
			$final_template = str_replace('{pagination_rel_links:next}', ($next_uri ? '<link rel="next" href="'.$site_url.$next_uri.'" />' : ''), $final_template);

			// Page number_format
			$final_template = str_replace('{pagination_rel_links:page_num}', $page_num, $final_template);
		}

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