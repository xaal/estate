<?php
class item extends CI_Controller
{

	// num of records per page
	private $limit = 5;
 
	// empty array for search terms
	var $terms     = array();	
	
	function __construct()
	{
		parent::__construct();
		
		// load model
		$this->load->model('item_model','',TRUE);
		$this->load->model('type_model','',TRUE);
		
	}
	
	function index()
	{
		
	}
	
	function add()
	{

		$data['title']="Estate: Add Item";
		$data['headline']="Add a New Item";
		
		$data['main_content']='item/item_add';
		
		
		$data['type']=$this->type_model->get_type_list();
		
		
		
		$this->load->view('includes/template2',$data);
	}
	

	
	function listing()
	{
		$item_qty;
		// offset
		$uri_segment = 3;
		
		// return third URI segment, if no third segment returns '' 
		$offset = $this->uri->segment($uri_segment,'');	
		
		// assign posted valued
		$data['eng_title'] = $this->input->post('eng_title');
		$data['mya_title'] = $this->input->post('mya_title');	
		$data['type_name'] = $this->input->post('type_name');
		$data['location'] = $this->input->post('location');
		$data['description'] = $this->input->post('description');
		
		// gets total URI segments
		$total_seg = $this->uri->total_segments();	
		
		// set search params
		// enters here only when 'Search' button is pressed or through 'Paging'
		if(isset($_POST['search']) || $total_seg>3)
		{			
 
			//$default = array('clientname', 'group', 'remarks');
			$default = array('eng_title', 'mya_title','type_name','location','description');
			if($total_seg > 3){
 			// navigation from paging									
 
				/**
				 *
				 * Convert URI segments into an associative array of key/value pairs
				 * But this array also contains the last redundant key value pair taking the page number as key.
				 * So, the last key value pair must be removed.				 
				 *
				*/
 
				$this->terms = $this->uri->uri_to_assoc(3,$default); 
 
				/**
				 * Replacing all the 'unset' values in the associative array (with keys as in $default array) to null value
				 * and create a new array '$this->terms_uri' taking only the database keys we need to query, 				
				**/
 
				for($i=0;$i<count($default);$i++){										
					if($this->terms[$default[$i]] == 'unset'){
						$this->terms[$default[$i]] = '';						
						$this->terms_uri[$default[$i]] = 'unset';
 
					}else{
						$this->terms_uri[$default[$i]] = $this->terms[$default[$i]];		
					}									
				}				
 
				// When the page is navigated through paging, it enters the condition below
				if(($total_seg % 2) > 0){					 		 
					// exclude the last array item (i.e. the array key for page number), prepare array for database query
					$this->terms = array_slice($this->terms, 0 , (floor($total_seg/2)-1));					
 
					$offset = $this->uri->segment($total_seg, '');		
					$uri_segment = $total_seg;
				}
 
				// Convert associative array $this->terms_uri to segments to append to base_url
				$keys = $this->uri->assoc_to_uri($this->terms_uri);		
 
				$parents = $this->item_model->get_search_pagedlist($this->terms,$this->limit, $offset)->result();
 
				// set total_rows config data for pagination			
				$config['total_rows'] = $this->item_model->count_all_search($this->terms);		
 
				$this->terms = array();								// resetting terms array
				$this->terms_uri = array();							// resetting terms_uri array
			}
			else
			{
			// navigation through POST search button
 
				$searchparams_uri = array();
 
				for($i=0;$i<count($default);$i++){
					if($this->input->post($default[$i]) != ''){						
						$searchparams_uri[$default[$i]] = $this->input->post($default[$i]);
						$data[$default[$i]] = $this->input->post($default[$i]);						
					}else{										
						$searchparams_uri[$default[$i]] = 'unset';
						$data[$default[$i]] = '';						
					}
				}			
 
				// Replace all the 'unset' values in an associative array to null value and create a new array '$searchparams' for database processing
				foreach($searchparams_uri as $k=>$v){
					if($v != 'unset'){
						$searchparams[$k] = $v;
					}else{
						$searchparams[$k] = '';
					}					
				}					
 
				$item_qty = $this->item_model->get_search_pagedlist($searchparams,$this->limit, $offset)->result();
 
				// turn associative array to segments to append to base_url
				$keys = $this->uri->assoc_to_uri($searchparams_uri);	
 
				// set total_rows config data for pagination			
				$config['total_rows'] = $this->item_model->count_all_search($searchparams);
			}
		}
		else
		{
		// load data
			//$parents = $this->parentmodel->get_paged_list($this->limit, $offset)->result();
			$item_qty=$this->item_model->get_paged_list($this->limit,$offset)->result();
			// set total_rows config data for pagination
			$config['total_rows'] = $this->item_model->count_all();
			$searchparams = "";
			$keys = "";
		}
		
		$config['base_url'] = site_url('item/listing/').'/'.$keys.'/';
  		$config['per_page'] = $this->limit;
		$config['uri_segment'] = $uri_segment;
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
 
		$this->table->set_empty("&nbsp;");
		
		//$heading=array('','Order No','Title in English','Title in Myanmar','Property Type'','Location','Description','Photo','Business/Residence','Sales/Rent','Remark','Status','Action');
		$heading=array('No.','Title in English','Title in Myanmar','Property Type','Location','Description','Photo','Business/Residence','Sales/Rent','Remark','Status','State','Action');
		
		$this->table->set_heading($heading);
		
		$i = 0 + $offset;
		
		$table_row=array();
		foreach($item_qty as $item)
		{
			$table_row=null;
			$table_row[]=++$i;
			//$table_row[]=$item->order_no;
			$table_row[]=$item->eng_title;
			$table_row[]=$item->mya_title;
			//$table_row[]=$item->type_id;
			$table_row[]=$item->name;
			$table_row[]=$item->location;
			$table_row[]=$item->description;
			$table_row[]=$item->photo;
			$temp=$item->business_or_residence;
			if($temp==0)  $table_row[]='Business';
			else if($temp==1) $table_row[]='Residence';
			else if($temp==2) $table_row[]='Any';
			
			$temp=$item->sale_or_rent;
			if($temp==0)  $table_row[]='Sales';
			else if($temp==1) $table_row[]='Rent';
			else if($temp==2) $table_row[]='Any';

			$table_row[]=$item->remark;
			if($item->is_active) $table_row[]='Inactive';
			else $table_row[]=' Active';
			if($item->is_deleted) $table_row[]='Deleted';
			else $table_row[]='';
			
			
			$table_row[]='<nobr>'.
			anchor('item/edit/'.$item->id,'edit').' | '.
			anchor('item/delete/'.$item->id,'delete',
			"onClick=\" return confirm('Are you sure you want to ' +
			'delete the record for $item->eng_title?')\"").
			'</nobr>';
			
			$this->table->add_row($table_row);
			//$this->table->add_row(++$i, $parent->eng_title, $parent->mya_title);
		}
		
		$items_table=$this->table->generate();
		$data['title']="Estate: Item Listing";
		$data['headline']="Item Listing";

		$data['data_table']=$items_table;
		$data['main_content']='item/item_listing';		
		
		$this->load->view('includes/template2',$data);	

	}
	

	function create()
	{
		
		$this->load->model('item_model','',TRUE);
		$this->item_model->addItem($_POST);
		redirect('item/listing','refresh');
	}
	
	function edit()
	{
	
		$id=$this->uri->segment(3);
		$this->load->model('item_model','',TRUE);
		$data['row']=$this->item_model->getItems($id)->result();
		
		$data['title']="Estate: Edit Item";
		$data['headline']="Edit Item Information";
		$data['main_content']='item/item_edit';
		
		$this->load->model('type_model','',TRUE);
		$data['type']=$this->type_model->get_type_list();
		
		$this->load->view('includes/template2',$data);
	}
	
	function update()
	{
		$this->load->model('item_model','',TRUE);
		$this->item_model->updateItem($_POST['id'],$_POST);
		
		//echo $_POST['type_id'];
		redirect('item/listing','refresh');
	}
	function delete()
	{
		$id=$this->uri->segment(3);
		
		$this->load->model('item_model','',TRUE);
		$this->item_model->deleteItem($id);
		redirect('item/listing','refresh');
	}
	
	
}
?>