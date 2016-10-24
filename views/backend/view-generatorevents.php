<?php
/**
 * List Tables View
 *
 * @package TablePress
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * List Tables View class
 * @package TablePress
 * @subpackage Views
 * @author Tobias Bäthge
 * @since 1.0.0
 */
class GeneratorEventList extends WP_List_Table {

	private $db;

    public function __construct(){

    	$this->load_dependencies();

    	$this->db = GeneratorEvents::get_instance();
    	
    	global $status, $page;

		parent::__construct( array(
			'singular'  => 'id_event',
			'plural'    => 'events',
			'ajax'      => false,
			'screen'    => $_REQUEST['page']
		) );
    }

    function load_dependencies(){
    	require_once( ALPAGE_ABSPATH . 'classes/class-generator-events.php' );
    }

	function get_columns(){
		$columns = array(
			'cb'	=> '<input type="checkbox" />',
			'id'	=> __('ID', 'wptg-plugin'),
			'name_site'	=> __('Site', 'wptg-plugin'),
			'name'	=> __('Name', 'wptg-plugin'),
			'date'	=> __('Date', 'wptg-plugin')
		);
		return $columns;
	}

    function column_default($item, $column_name){
        return stripslashes($item[$column_name]);
    }

	function column_name($item){
		//Build row actions
		$actions = array(
			'edit' => sprintf('<a href="?page=%s&action=%s&id_event=%s">%s</a>', $_REQUEST['page'],'edit',$item['id'], __('Edit', 'wptg-plugin') )
		);

		//Return the title contents
		return sprintf('%1$s %2$s',
			/*$1%s*/ stripslashes($item['name']),
			/*$2%s*/ $this->row_actions($actions)
		);
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
		);
	}

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('Delete', 'wptg-plugin')
        );
        return $actions;
    }

	function prepare_items() {
		$per_page               = 25;
		$hidden                 = array();
		$columns                = $this->get_columns();
		$sortable               = array();
		$curr_page              = $this->get_pagenum();

		$total_items            = $this->db->getCountEvents();
		$data                   = $this->db->get_page_itemsEvent($curr_page, $per_page);

		$this->items            = $data;
		$this->_column_headers  = array($columns, $hidden, $sortable);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items/$per_page)
		) );
	}

	function show(){
		echo sprintf('<div class="wrap">');
    	echo sprintf( '<h2>%s <a class="add-new-h2" href="%s">%s</a></h2>', __('Site', 'wptg-plugin'), admin_url('admin.php?page=GeneratorSites&action=add'), __('Add New', 'wptg-plugin') );
        echo sprintf('<form method="GET"><input type="hidden" name="page" value="'.$_GET['page'].'">');
	    $this->prepare_items();
		$this->display();
	    echo sprintf('</form>');
    	echo sprintf('</div>');
	}

	
	function showForm($id=null){
		wp_enqueue_style('alpage_admin_style');		 
		wp_enqueue_style('datetimepicker');

		if (!empty($id)) {
			$data=$this->db->getSite($id);
			$savedata = "update";
		}else {
			$data=array();
			$savedata = "insert";
		}

		$name 			= isset($data[0]['name'])?$data[0]['name']:''; 
		$addres 		= isset($data[0]['addres'])?$data[0]['addres']:''; 
		$latitude 		= isset($data[0]['latitude'])?$data[0]['latitude']:''; 
		$longitude 		= isset($data[0]['longitude'])?$data[0]['longitude']:''; 
		$environment	= isset($data[0]['environment'])?$data[0]['environment']:''; 
		$opening_hour	= isset($data[0]['opening_hour'])?$data[0]['opening_hour']:''; 
		$closed_hour 	= isset($data[0]['closed_hour'])?$data[0]['closed_hour']:'';

		?>
		
		<script>
		jQuery(document).ready(function($) {
		   jQuery('#table-opening_hour,#table-closed_hour').datetimepicker({
			  datepicker:false,
			  format:'H:i'
			});
		   /*jQuery('#table-closed_hour').datetimepicker({
			  datepicker:false,
			  format:'H:i',
			  value:'03:00'
			});*/
		});
		</script>
		<div class="wrap">
		<h1>Add Site Event</h1>
			<form method="post" action="?page=GeneratorSites&action=list" >
				<input type="hidden" name="id_site" value="<?php echo $id; ?>" >
				<input type="hidden" name="savedata" value="<?php echo $savedata; ?>" >
				<div class="postbox ">
				<div class="form-wrap inside">
					<div class="form-field">
						<label for="table-name"><?php _e( 'Site Name', 'GeneratorEvents' ); ?>:</label>
						<input required type="text" name="GeForm[name]" value="<?php echo $name; ?>" id="table-name" class="placeholder placeholder-active"  placeholder="<?php esc_attr_e( 'Enter Site Name here', 'GeneratorEvents' ); ?>" />
					</div>
					<div class="form-field">
						<label for="site-addres"><?php _e( 'Addres', 'GeneratorEvents' ); ?>:</label>
						<textarea name="GeForm[addres]" id="site-addres" rows="2"><?php echo $addres; ?></textarea>
						<p><?php _e( 'Enter the address of the site.', 'GeneratorEvents' ); ?></p>
					</div>


					<div class="form-field field-coord">
						<label for="table-latitude"><?php _e( 'Latitude', 'GeneratorEvents' ); ?>:</label>
						<input required type="text" name="GeForm[latitude]" value="<?php echo $latitude; ?>" id="table-latitude" />
					</div>
					<div class="form-field field-coord">
						<label for="table-longitude"><?php _e( 'Longitude', 'GeneratorEvents' ); ?>:</label>
						<input required type="text" name="GeForm[longitude]" value="<?php echo $longitude; ?>" id="table-longitude" />
					</div>

					<div class="form-field">
						<label for="table-environment"><?php _e( 'Environment', 'GeneratorEvents' ); ?>:</label>
						<input type="text" name="GeForm[environment]" value="<?php echo $environment; ?>" id="table-environment" />
						<p><?php _e( 'Enter the type of environment in this', 'GeneratorEvents' ); ?></p>
					</div>

					<div class="form-field form-field-small">
						<label for="table-opening_hour"><?php _e( 'Opening hour', 'GeneratorEvents' ); ?>:</label>
						<input type="time" name="GeForm[opening_hour]" value="<?php echo $opening_hour; ?>" id="table-opening_hour" title="<?php esc_attr_e( 'Opening hour', 'GeneratorEvents' ); ?>"/>
						<p><?php _e( 'Time to open the site.', 'GeneratorEvents' ); ?></p>
					</div>
					<div class="form-field form-field-small">
						<label for="table-closed_hour"><?php _e( 'Closed hour', 'GeneratorEvents' ); ?>:</label>
						<input type="time" name="GeForm[closed_hour]" value="<?php echo $closed_hour; ?>" id="table-closed_hour" title="<?php esc_attr_e( 'CLosed hour.', 'GeneratorEvents' ); ?>" />
						<p><?php _e( 'Time to close the site.', 'GeneratorEvents' ); ?></p>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php submit_button(); ?>
			</form>
		</div>

		<?php
	}

	function delete(){
		return $this->db->deleteSite($_GET['id_event']);
	}

	function processData(){

		if (isset($_POST['savedata']) && !empty($_POST['savedata'])){
			$savedata=$_POST['savedata'];
			switch ($savedata) {
	 			case 'insert':
	 				return $this->db->addSite();
	 			break;
	 			case 'update':
	 				return $this->db->editSite();
	 			break; 			
	 		}
		}
		return false;
	}

	function doAction($action){
		$this->processData();
 		switch ($action) {
 			case 'list':
 				$this->show();
 			break;
 			case 'add':
 				$this->showForm();
 			break;
 			case 'edit':
 				$id=$_GET['id_event'];
 				$this->showForm($id);
 			break;
 			case 'delete':
 				$this->delete();
 				$this->show();
 			break;
 		}
	}


}